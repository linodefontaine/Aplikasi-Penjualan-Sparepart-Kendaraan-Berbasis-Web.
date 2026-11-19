<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

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
            ->paginate(12)
            ->withQueryString();
        $accountTenant = $request->user()->tenant_id === null
            ? null
            : Tenant::query()->find($request->user()->tenant_id);

        return view('bengkel.user-menu', compact('tenants', 'search', 'accountTenant'));
    }
}
