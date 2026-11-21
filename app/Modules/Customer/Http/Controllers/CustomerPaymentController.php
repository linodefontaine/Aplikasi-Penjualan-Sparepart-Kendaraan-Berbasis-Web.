<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Workshop\Payments\DemoPaymentMethods;
use App\Modules\Workshop\Payments\Exceptions\GatewayUnavailableException;
use App\Modules\Workshop\Payments\PaymentService;
use App\Modules\Workshop\Payments\QrisSvgRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    private const CUSTOMER_PAYMENT_METHODS = [
        'qris' => 'QRIS',
        'va_bca' => 'Virtual Account BCA',
        'va_bri' => 'Virtual Account BRI',
        'va_bni' => 'Virtual Account BNI',
        'va_mandiri' => 'Virtual Account Mandiri',
        'cash' => 'Bayar di Tempat / Ambil di Toko',
    ];

    public function show(Request $request, string $id, string $invoice, QrisSvgRenderer $renderer): View
    {
        $tenant = $this->activeTenant($id);
        $order = $tenant->run(function () use ($request, $invoice): ?array {
            $sale = DB::table('sales')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->where('sales.invoice_number', $invoice)
                ->where('customers.central_user_id', $request->user()->id)
                ->first([
                    'sales.*',
                    'customers.name as customer_name',
                ]);
            if ($sale === null) {
                return null;
            }

            $payment = DB::table('payments')
                ->where('sale_id', $sale->id)
                ->latest('id')
                ->first();
            if ($payment === null) {
                return null;
            }

            $attempt = DB::table('payment_attempts')
                ->where('payment_id', $payment->id)
                ->orderByDesc('attempt_number')
                ->first();
            $expiresAt = $attempt?->expires_at !== null
                ? CarbonImmutable::parse($attempt->expires_at)
                : null;
            $expired = $attempt !== null
                && ($attempt->status === 'expired'
                    || ($attempt->status === 'pending' && ($expiresAt === null || ! $expiresAt->isFuture())));
            $active = $attempt !== null
                && $attempt->status === 'pending'
                && $attempt->qris_payload !== null
                && ! $expired;

            return [
                'sale' => $sale,
                'payment' => $payment,
                'items' => DB::table('sale_items')->where('sale_id', $sale->id)->get(),
                'attempt' => $attempt,
                'expiresAtIso' => $expiresAt?->toIso8601String(),
                'expired' => $expired,
                'active' => $active,
            ];
        });
        abort_if($order === null, 404);

        $selectedMethod = $order['payment']->payment_method === 'bank_transfer'
            ? 'va_bca'
            : $order['payment']->payment_method;
        $methodReady = $order['payment']->payment_method === $selectedMethod;
        $paymentDetails = $order['payment']->notes
            ? json_decode($order['payment']->notes, true)
            : [];
        $bankCode = str_starts_with($selectedMethod, 'va_') ? $selectedMethod : null;

        return view('bengkel.pembayaran', [
            'tenant' => $tenant,
            'order' => $order,
            'selectedMethod' => $selectedMethod,
            'methodReady' => $methodReady,
            'paymentDetails' => is_array($paymentDetails) ? $paymentDetails : [],
            'paymentMethods' => self::CUSTOMER_PAYMENT_METHODS,
            'virtualAccountNumber' => $bankCode
                ? DemoPaymentMethods::virtualAccountNumber((string) $tenant->getTenantKey(), $invoice, $bankCode)
                : null,
            'sandboxEnabled' => (bool) config('services.qris.sandbox_enabled'),
            'qrisSvg' => $order['active'] && config('services.qris.sandbox_enabled')
                ? $renderer->svg((string) $order['attempt']->qris_payload)
                : null,
        ]);
    }

    public function selectMethod(Request $request, string $id, string $invoice, PaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(self::CUSTOMER_PAYMENT_METHODS))],
        ]);
        $method = $validated['payment_method'];
        if ($method === 'qris' && ! config('services.qris.sandbox_enabled')) {
            throw ValidationException::withMessages([
                'payment_method' => 'Pembayaran QRIS belum dikonfigurasi untuk lingkungan ini.',
            ]);
        }

        $tenant = $this->activeTenant($id);
        $paymentId = $tenant->run(function () use ($request, $invoice, $method): int {
            return DB::transaction(function () use ($request, $invoice, $method): int {
                $payment = $this->ownedPayment($request, $invoice, lockForUpdate: true);
                if ($payment->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'payment' => 'Metode pembayaran tidak dapat diubah setelah transaksi selesai.',
                    ]);
                }

                DB::table('payments')->where('id', $payment->id)->update([
                    'payment_method' => $method,
                    'updated_at' => now(),
                ]);

                return (int) $payment->id;
            }, attempts: 3);
        });

        if ($method === 'qris') {
            try {
                $tenant->run(fn () => $paymentService->createQrisAttempt($paymentId));
            } catch (GatewayUnavailableException $exception) {
                report($exception);

                return $this->paymentRedirect($tenant, $invoice)
                    ->with('error', 'QRIS sandbox sedang mengalami gangguan. Coba buat tagihan kembali.');
            }
        }

        return $this->paymentRedirect($tenant, $invoice)
            ->with('success', 'Metode pembayaran berhasil dipilih.');
    }

    public function simulate(Request $request, string $id, string $invoice, PaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(self::CUSTOMER_PAYMENT_METHODS))],
        ]);
        $method = $validated['payment_method'];
        $tenant = $this->activeTenant($id);

        $tenant->run(function () use ($request, $invoice, $method, $paymentService): void {
            $payment = $this->ownedPayment($request, $invoice);
            if ($payment->payment_method !== $method) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Pilih metode pembayaran yang sedang ditampilkan.',
                ]);
            }

            if ($method === 'qris') {
                $paymentService->simulateSandboxPayment((int) $payment->id, (int) $request->user()->id);

                return;
            }

            $paymentService->simulateDemoPayment(
                (int) $payment->id,
                $method,
                (int) $request->user()->id,
                (int) $request->user()->id,
            );
        });

        return $this->paymentRedirect($tenant, $invoice)
            ->with('success', 'Pembayaran demo berhasil. Bukti lunas tersedia di bawah.');
    }

    private function ownedPayment(Request $request, string $invoice, bool $lockForUpdate = false): object
    {
        $saleQuery = DB::table('sales')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.invoice_number', $invoice)
            ->where('customers.central_user_id', $request->user()->id)
            ->select('sales.id');
        if ($lockForUpdate) {
            $saleQuery->lockForUpdate();
        }
        $sale = $saleQuery->first();
        abort_if($sale === null, 404);

        $paymentQuery = DB::table('payments')
            ->where('sale_id', $sale->id)
            ->latest('id');
        if ($lockForUpdate) {
            $paymentQuery->lockForUpdate();
        }
        $payment = $paymentQuery->first();
        abort_if($payment === null, 404);

        return $payment;
    }

    private function activeTenant(string $id): Tenant
    {
        $tenant = Tenant::query()->with('domains')->findOrFail($id);
        abort_unless($tenant->isActive(), 404);

        return $tenant;
    }

    private function paymentRedirect(Tenant $tenant, string $invoice): RedirectResponse
    {
        return redirect()->route('user.bengkel.pembayaran', [
            'id' => $tenant->getTenantKey(),
            'invoice' => $invoice,
        ]);
    }
}
