<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Customer\Services\CustomerDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerDirectoryController extends Controller
{
    public function index(Request $request, CustomerDirectoryService $directory): View
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
}
