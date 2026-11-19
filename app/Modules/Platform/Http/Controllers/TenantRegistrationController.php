<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Platform\Services\TenantPackageCatalog;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Stancl\Tenancy\Database\Models\Domain;

class TenantRegistrationController extends Controller
{
    public function create(Request $request, TenantPackageCatalog $packageCatalog): View|RedirectResponse
    {
        if ($redirect = $this->existingAccountDestination($request)) {
            return $redirect;
        }

        return view('auth.register', [
            'packages' => $packageCatalog->all(),
            'tenantBaseDomain' => $this->tenantBaseDomain($request),
        ]);
    }

    public function store(Request $request, TenantPackageCatalog $packageCatalog): RedirectResponse
    {
        if ($redirect = $this->existingAccountDestination($request)) {
            return $redirect;
        }

        $user = $request->user();
        abort_unless($user->role === 'Pelanggan' && $user->tenant_id === null, 403);
        $baseDomain = $this->tenantBaseDomain($request);

        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/\A[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\z/i',
                function (string $attribute, mixed $value, Closure $fail) use ($baseDomain): void {
                    $domain = Str::lower((string) $value).'.'.$baseDomain;

                    if (Domain::where('domain', $domain)->exists()) {
                        $fail('Domain tersebut sudah digunakan.');
                    }
                },
            ],
            'package' => ['required', Rule::in(array_keys($packageCatalog->all()))],
        ], [
            'subdomain.regex' => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung.',
        ]);

        $package = $packageCatalog->all()[$validated['package']];
        $tenant = Tenant::create([
            'tenant_name' => $validated['tenant_name'],
            'phone' => $user->phone,
            'package' => $validated['package'],
            'status' => 'pending_payment',
        ]);
        $invoiceNumber = 'SUB-'.Str::upper((string) Str::ulid());

        DB::transaction(function () use ($validated, $tenant, $user, $baseDomain, $package, $invoiceNumber): void {
            $tenant->createDomain(Str::lower($validated['subdomain']).'.'.$baseDomain);

            DB::table('subscription_invoices')->insert([
                'tenant_id' => $tenant->getTenantKey(),
                'invoice_number' => $invoiceNumber,
                'package' => $validated['package'],
                'package_name' => $package['name'],
                'amount' => $package['amount'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->update([
                'role' => 'Owner',
                'tenant_id' => $tenant->getTenantKey(),
            ]);
        });

        $request->session()->regenerate();

        return redirect()->route('tenant.checkout.show', ['invoice' => $invoiceNumber]);
    }

    private function existingAccountDestination(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        return match ($user->role) {
            'Owner' => $this->ownerDestination($user),
            'Kasir' => redirect()->route('owner.kasir'),
            'Mekanik' => redirect()->route('owner.mekanik'),
            'Super Admin' => redirect()->route('superadmin.home'),
            default => $user->tenant_id === null ? null : abort(403),
        };
    }

    private function ownerDestination(User $user): RedirectResponse
    {
        $invoice = $user->tenant_id === null
            ? null
            : DB::table('subscription_invoices')
                ->where('tenant_id', $user->tenant_id)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->value('invoice_number');

        return is_string($invoice)
            ? redirect()->route('tenant.checkout.show', ['invoice' => $invoice])
            : redirect()->route('owner.home');
    }

    private function tenantBaseDomain(Request $request): string
    {
        $baseDomain = trim((string) config('tenancy.base_domain'));

        return Str::lower($baseDomain !== '' ? $baseDomain : $request->getHost());
    }
}
