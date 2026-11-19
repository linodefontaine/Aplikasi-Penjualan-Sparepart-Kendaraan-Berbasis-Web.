<?php

namespace App\Modules\Platform\Services;

use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDOException;

class TenantMetricsService
{
    private const PACKAGE_PRICES = [
        'starter' => 99000,
        'pro' => 249000,
        'enterprise' => 799000,
    ];

    public function monthlySummary(): array
    {
        $periodStart = CarbonImmutable::now()->startOfMonth();
        $nextPeriodStart = $periodStart->addMonth();
        $tenants = Tenant::query()->with(['domains', 'owner'])->latest()->get();
        $activeTenants = $tenants->filter(fn (Tenant $tenant): bool => $tenant->isActive());
        $rows = [];
        $unavailableTenantNames = [];

        foreach ($activeTenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $metrics = [
                    'revenue' => (float) DB::table('sales')
                        ->where('status', 'completed')
                        ->where('sold_at', '>=', $periodStart)
                        ->where('sold_at', '<', $nextPeriodStart)
                        ->sum('total'),
                    'invoice_count' => DB::table('sales')
                        ->where('status', 'completed')
                        ->where('sold_at', '>=', $periodStart)
                        ->where('sold_at', '<', $nextPeriodStart)
                        ->count(),
                    'work_order_count' => DB::table('work_orders')
                        ->where('created_at', '>=', $periodStart)
                        ->where('created_at', '<', $nextPeriodStart)
                        ->count(),
                    'recent_work_orders' => DB::table('work_orders')
                        ->where('created_at', '>=', $periodStart)
                        ->where('created_at', '<', $nextPeriodStart)
                        ->latest('created_at')
                        ->limit(3)
                        ->pluck('work_order_number'),
                ];
            } catch (QueryException|PDOException $exception) {
                report($exception);
                $unavailableTenantNames[] = (string) $tenant->getAttribute('tenant_name');

                continue;
            } finally {
                if (tenancy()->initialized) {
                    tenancy()->end();
                }
            }

            $rows[] = [
                'tenant' => $tenant,
                ...$metrics,
            ];
        }

        return [
            'tenantCount' => $tenants->count(),
            'activeTenantCount' => $activeTenants->count(),
            'monthlyRevenue' => collect($rows)->sum('revenue'),
            'monthlyInvoiceCount' => collect($rows)->sum('invoice_count'),
            'monthlyWorkOrderCount' => collect($rows)->sum('work_order_count'),
            'estimatedMonthlySubscriptions' => $activeTenants->sum(
                fn (Tenant $tenant): int => self::PACKAGE_PRICES[(string) $tenant->getAttribute('package')] ?? 0,
            ),
            'monthLabel' => $periodStart->locale('id')->translatedFormat('F Y'),
            'unavailableTenantNames' => $unavailableTenantNames,
            'availableDatabaseCount' => count($rows),
            'recentTenants' => $tenants->take(3),
            'tenantReportRows' => collect($rows)->sortByDesc('revenue')->values(),
        ];
    }

    /**
     * @return array{
     *     totalVehicles: int,
     *     completedTransactions: int,
     *     sparePartsSold: float,
     *     revenue: float,
     *     tenantCount: int,
     *     availableTenantCount: int,
     *     unavailableTenantCount: int,
     *     description: string
     * }
     */
    public function landingPageStatistics(): array
    {
        $tenants = Tenant::query()->get();
        $totals = [
            'totalVehicles' => 0,
            'completedTransactions' => 0,
            'sparePartsSold' => 0.0,
            'revenue' => 0.0,
        ];
        $availableTenantCount = 0;
        $unavailableTenantCount = 0;

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $statistics = [
                    'totalVehicles' => DB::table('vehicles')->count(),
                    'completedTransactions' => DB::table('sales')
                        ->where('status', 'completed')
                        ->count(),
                    'sparePartsSold' => (float) DB::table('sale_items')
                        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                        ->where('sales.status', 'completed')
                        ->whereNotNull('sale_items.spare_part_id')
                        ->sum('sale_items.quantity'),
                    'revenue' => (float) DB::table('sales')
                        ->where('status', 'completed')
                        ->sum('total'),
                ];
            } catch (QueryException|PDOException $exception) {
                report($exception);
                $unavailableTenantCount++;

                continue;
            } finally {
                if (tenancy()->initialized) {
                    tenancy()->end();
                }
            }

            $availableTenantCount++;
            $totals['totalVehicles'] += (int) $statistics['totalVehicles'];
            $totals['completedTransactions'] += (int) $statistics['completedTransactions'];
            $totals['sparePartsSold'] += $statistics['sparePartsSold'];
            $totals['revenue'] += $statistics['revenue'];
        }

        $tenantCount = $tenants->count();
        $description = match (true) {
            $tenantCount === 0 => 'Belum ada tenant terdaftar; statistik dimulai dari 0.',
            $unavailableTenantCount > 0 => sprintf(
                'Data aktual mencakup %d dari %d tenant terdaftar; database %d tenant belum dapat dibaca.',
                $availableTenantCount,
                $tenantCount,
                $unavailableTenantCount,
            ),
            default => sprintf('Akumulasi dari %d tenant terdaftar.', $tenantCount),
        };

        return [
            ...$totals,
            'tenantCount' => $tenantCount,
            'availableTenantCount' => $availableTenantCount,
            'unavailableTenantCount' => $unavailableTenantCount,
            'description' => $description,
        ];
    }

    public function packageDistribution(): array
    {
        $tenants = Tenant::query()->get();
        $distribution = [];

        foreach ([
            'starter' => 'Starter',
            'pro' => 'Pro Tenant',
            'enterprise' => 'Enterprise',
        ] as $package => $label) {
            $packageTenants = $tenants->filter(
                fn (Tenant $tenant): bool => $tenant->getAttribute('package') === $package,
            );
            $activeTenants = $packageTenants->filter(fn (Tenant $tenant): bool => $tenant->isActive());
            $activeTenantIds = $activeTenants->map(fn (Tenant $tenant): string => (string) $tenant->getTenantKey());

            $distribution[$package] = [
                'package' => $package,
                'label' => $label,
                'active_tenant_count' => $activeTenants->count(),
                'inactive_tenant_count' => $packageTenants->count() - $activeTenants->count(),
                'active_user_count' => User::query()
                    ->whereIn('tenant_id', $activeTenantIds)
                    ->whereIn('role', ['Owner', 'Kasir', 'Mekanik'])
                    ->count(),
                'unit_price' => self::PACKAGE_PRICES[$package],
                'estimated_monthly_value' => $activeTenants->count() * self::PACKAGE_PRICES[$package],
            ];
        }

        return $distribution;
    }
}
