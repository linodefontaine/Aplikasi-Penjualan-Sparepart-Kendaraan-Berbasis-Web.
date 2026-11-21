<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Modules\Customer\Services\CustomerDirectoryService;
use App\Services\TenantMetricsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantDirectoryController extends Controller
{
    public function consumerIndex(Request $request, CustomerDirectoryService $directory): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', Rule::in(array_keys(CustomerDirectoryService::FILTER_LABELS))],
        ]);
        $search = trim($validated['search'] ?? '');
        $filter = $validated['filter'] ?? '';

        $tenants = $directory->search($request, $search, $filter);
        $accountTenant = $request->user()?->tenant_id === null
            ? null
            : Tenant::query()->find($request->user()->tenant_id);
        $filterLabels = CustomerDirectoryService::FILTER_LABELS;

        return view('bengkel.user-menu', compact('tenants', 'search', 'filter', 'filterLabels', 'accountTenant'));
    }

    public function adminIndex(): View
    {
        $tenants = Tenant::query()
            ->with(['domains', 'owner'])
            ->latest()
            ->paginate(15);

        return view('superadmin.dashboardsuperadmin', compact('tenants'));
    }

    public function adminDashboard(TenantMetricsService $metrics): View
    {
        $summary = $metrics->monthlySummary();

        return view('superadmin.dashboard-global', $summary);
    }

    public function adminReport(TenantMetricsService $metrics): View
    {
        return view('superadmin.laporan', $metrics->monthlySummary());
    }

    public function adminPackages(TenantMetricsService $metrics): View
    {
        return view('superadmin.paket', [
            'packageDistribution' => $metrics->packageDistribution(),
        ]);
    }
}
