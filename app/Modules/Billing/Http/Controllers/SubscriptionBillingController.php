<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Platform\Services\TenantPackageCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionBillingController extends Controller
{
    public function checkout(Request $request, string $invoice): View|RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail($request->user()->tenant_id);
        $subscriptionInvoice = DB::table('subscription_invoices')
            ->where('tenant_id', $tenant->getTenantKey())
            ->where('invoice_number', $invoice)
            ->first();

        abort_if($subscriptionInvoice === null, 404);

        if ($subscriptionInvoice->status === 'paid') {
            return redirect()->route('owner.home');
        }

        return view('tenant.checkout-langganan', [
            'tenant' => $tenant,
            'invoice' => $subscriptionInvoice,
        ]);
    }

    public function simulate(Request $request, string $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['qris', 'virtual_account'])],
        ]);
        $tenantId = $request->user()->tenant_id;

        DB::transaction(function () use ($invoice, $tenantId, $validated): void {
            $subscriptionInvoice = DB::table('subscription_invoices')
                ->where('tenant_id', $tenantId)
                ->where('invoice_number', $invoice)
                ->lockForUpdate()
                ->first();

            abort_if($subscriptionInvoice === null, 404);

            if ($subscriptionInvoice->status === 'paid') {
                return;
            }

            $periodStartsAt = CarbonImmutable::now();
            $periodEndsAt = $periodStartsAt->addMonthNoOverflow();

            DB::table('subscription_invoices')
                ->where('id', $subscriptionInvoice->id)
                ->update([
                    'payment_method' => $validated['payment_method'],
                    'status' => 'paid',
                    'paid_at' => $periodStartsAt,
                    'subscription_starts_at' => $periodStartsAt,
                    'subscription_ends_at' => $periodEndsAt,
                    'updated_at' => $periodStartsAt,
                ]);

            $tenant = Tenant::query()->findOrFail($tenantId);
            $tenant->update([
                'status' => 'active',
                'subscription_started_at' => $periodStartsAt,
                'subscription_ends_at' => $periodEndsAt,
            ]);
        });

        return redirect()->route('owner.home')->with('success', 'Pembayaran langganan berhasil disimulasikan.');
    }

    public function index(Request $request, TenantPackageCatalog $packageCatalog): View
    {
        $tenant = Tenant::query()->findOrFail($request->user()->tenant_id);
        $invoices = DB::table('subscription_invoices')
            ->where('tenant_id', $tenant->getTenantKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $package = $packageCatalog->all()[$tenant->getAttribute('package')] ?? null;

        return view('tenant.langganan', [
            'tenant' => $tenant,
            'invoices' => $invoices,
            'activePackageName' => $package['name'] ?? (string) $tenant->getAttribute('package'),
            'subscriptionStartsAt' => $tenant->getAttribute('subscription_started_at'),
            'subscriptionEndsAt' => $tenant->getAttribute('subscription_ends_at'),
        ]);
    }
}
