<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Services\TenantMetricsService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(TenantMetricsService $metrics): View
    {
        return view('bengkel.landing-bengkel', [
            'statistics' => $metrics->landingPageStatistics(),
        ]);
    }
}
