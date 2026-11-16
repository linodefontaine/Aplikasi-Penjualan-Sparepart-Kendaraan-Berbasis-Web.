<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Platform\Services\TenantMetricsService;
use Illuminate\View\View;

class TenantDirectoryController extends Controller
{
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
