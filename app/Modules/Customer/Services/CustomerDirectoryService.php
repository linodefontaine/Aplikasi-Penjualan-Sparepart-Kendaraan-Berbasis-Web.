<?php

namespace App\Modules\Customer\Services;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CustomerDirectoryService
{
    public const FILTER_LABELS = [
        'terdekat' => 'Terdekat',
        'ganti-oli' => 'Ganti Oli',
        'servis-besar' => 'Servis Besar',
        'booking' => 'Booking & Antrean',
        'stok-sparepart' => 'Stok Suku Cadang',
    ];

    private const SERVICE_KEYWORDS = [
        'ganti-oli' => ['oli', 'oil'],
        'servis-besar' => ['servis besar', 'service besar', 'overhaul', 'turun mesin', 'full service'],
    ];

    public function search(Request $request, string $search, string $filter): LengthAwarePaginator
    {
        $tenants = Tenant::query()
            ->where(function (Builder $query): void {
                $query->whereNull('data->status')
                    ->orWhere('data->status', 'active');
            })
            ->with(['domains', 'owner'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('data->tenant_name', 'like', "%{$search}%")
                        ->orWhereHas('domains', function (Builder $domainQuery) use ($search): void {
                            $domainQuery->where('domain', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $tenants = match ($filter) {
            'terdekat' => $tenants->sortByDesc(fn (Tenant $tenant): int => $this->readinessScore($tenant))->values(),
            'ganti-oli', 'servis-besar' => $this->filterByService($tenants, $filter),
            'stok-sparepart' => $tenants
                ->filter(fn (Tenant $tenant): bool => $this->hasAvailableSpareparts($tenant))
                ->values(),
            default => $tenants,
        };

        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $tenants->forPage($page, $perPage)->values(),
            $tenants->count(),
            $perPage,
            $page,
            ['path' => $request->url()],
        );
        $query = $request->query();
        unset($query['page']);

        return $paginator->appends($query);
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return Collection<int, Tenant>
     */
    private function filterByService(Collection $tenants, string $filter): Collection
    {
        $servicesByTenant = $tenants->mapWithKeys(
            fn (Tenant $tenant): array => [$tenant->getTenantKey() => $this->activeServiceNames($tenant)],
        );
        $keywords = self::SERVICE_KEYWORDS[$filter];
        $matchingTenants = $tenants->filter(function (Tenant $tenant) use ($servicesByTenant, $keywords): bool {
            return collect($servicesByTenant->get($tenant->getTenantKey(), []))
                ->contains(fn (string $service): bool => collect($keywords)
                    ->contains(fn (string $keyword): bool => str_contains($service, $keyword)));
        })->values();

        if ($matchingTenants->isNotEmpty()) {
            return $matchingTenants;
        }

        $tenantsWithServices = $tenants->filter(
            fn (Tenant $tenant): bool => $servicesByTenant->get($tenant->getTenantKey(), []) !== [],
        )->values();

        return $tenantsWithServices->isNotEmpty() ? $tenantsWithServices : $tenants;
    }

    /**
     * @return list<string>
     */
    private function activeServiceNames(Tenant $tenant): array
    {
        return $tenant->run(function (): array {
            $schema = DB::connection('tenant')->getSchemaBuilder();
            if (! $schema->hasTable('service_catalog') || ! $schema->hasColumn('service_catalog', 'name')) {
                return [];
            }

            $columns = ['name'];
            if ($schema->hasColumn('service_catalog', 'description')) {
                $columns[] = 'description';
            }

            $query = DB::table('service_catalog');
            if ($schema->hasColumn('service_catalog', 'is_active')) {
                $query->where('is_active', true);
            }

            return $query->get($columns)
                ->map(fn (object $service): string => mb_strtolower(
                    $service->name.' '.($service->description ?? ''),
                ))
                ->all();
        });
    }

    private function hasAvailableSpareparts(Tenant $tenant): bool
    {
        return $tenant->run(function (): bool {
            $schema = DB::connection('tenant')->getSchemaBuilder();
            if (! $schema->hasTable('spare_parts')
                || ! $schema->hasColumn('spare_parts', 'id')
                || ! $schema->hasColumn('spare_parts', 'stock')) {
                return false;
            }

            $query = DB::table('spare_parts');
            if ($schema->hasColumn('spare_parts', 'is_active')) {
                $query->where('spare_parts.is_active', true);
            }

            $canCheckReservations = $schema->hasTable('sale_items')
                && $schema->hasColumn('sale_items', 'sale_id')
                && $schema->hasColumn('sale_items', 'spare_part_id')
                && $schema->hasColumn('sale_items', 'quantity')
                && $schema->hasTable('sales')
                && $schema->hasColumn('sales', 'id')
                && $schema->hasColumn('sales', 'customer_id')
                && $schema->hasColumn('sales', 'status')
                && $schema->hasColumn('sales', 'fulfillment_method')
                && $schema->hasTable('customers')
                && $schema->hasColumn('customers', 'id')
                && $schema->hasColumn('customers', 'central_user_id');

            if (! $canCheckReservations) {
                return $query->where('spare_parts.stock', '>', 0)->exists();
            }

            $reservedParts = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->where('sales.status', 'pending')
                ->whereNotNull('sales.fulfillment_method')
                ->whereNotNull('customers.central_user_id')
                ->select('sale_items.spare_part_id')
                ->selectRaw('SUM(sale_items.quantity) as reserved_quantity')
                ->groupBy('sale_items.spare_part_id');

            return $query
                ->leftJoinSub($reservedParts, 'reserved_parts', function (JoinClause $join): void {
                    $join->on('reserved_parts.spare_part_id', '=', 'spare_parts.id');
                })
                ->whereRaw('spare_parts.stock > COALESCE(reserved_parts.reserved_quantity, 0)')
                ->exists();
        });
    }

    private function readinessScore(Tenant $tenant): int
    {
        $score = (filled($tenant->getAttribute('address')) ? 100 : 0)
            + (filled($tenant->getAttribute('phone')) ? 100 : 0);

        return $score + match (mb_strtolower((string) $tenant->getAttribute('package'))) {
            'business', 'enterprise' => 3,
            'professional', 'pro' => 2,
            default => 1,
        };
    }
}
