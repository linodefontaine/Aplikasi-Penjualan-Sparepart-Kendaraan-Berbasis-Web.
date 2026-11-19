<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
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
    public function create(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->existingAccountDestination($request)) {
            return $redirect;
        }

        return view('auth.register', [
            'tenantBaseDomain' => $this->tenantBaseDomain($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->existingAccountDestination($request)) {
            return $redirect;
        }

        $user = $request->user();
        abort_unless($user->role === 'Pelanggan' && $user->tenant_id === null, 403);
        $baseDomain = $this->tenantBaseDomain($request);

        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
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
            'package' => ['required', Rule::in(['pro', 'starter', 'enterprise'])],
        ], [
            'subdomain.regex' => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung.',
        ]);

        $tenant = Tenant::create([
            'tenant_name' => $validated['tenant_name'],
            'address' => trim($validated['address'] ?? ''),
            'phone' => $user->phone,
            'package' => $validated['package'],
        ]);

        DB::transaction(function () use ($validated, $tenant, $user, $baseDomain): void {
            $tenant->createDomain(Str::lower($validated['subdomain']).'.'.$baseDomain);

            $user->update([
                'role' => 'Owner',
                'tenant_id' => $tenant->getTenantKey(),
            ]);
        });

        $request->session()->regenerate();

        return redirect()->route('owner.home');
    }

    private function existingAccountDestination(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        return match ($user->role) {
            'Owner' => redirect()->route('owner.home'),
            'Kasir' => redirect()->route('owner.kasir'),
            'Mekanik' => redirect()->route('owner.mekanik'),
            'Super Admin' => redirect()->route('superadmin.home'),
            default => $user->tenant_id === null ? null : abort(403),
        };
    }

    private function tenantBaseDomain(Request $request): string
    {
        $baseDomain = trim((string) config('tenancy.base_domain'));

        return Str::lower($baseDomain !== '' ? $baseDomain : $request->getHost());
    }
}
