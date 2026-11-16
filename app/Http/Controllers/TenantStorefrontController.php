<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantStorefrontController extends Controller
{
    private const PAYMENT_LABELS = [
        'cash' => 'Tunai saat ambil',
        'bank_transfer' => 'Transfer bank',
        'qris' => 'QRIS manual',
        'e_wallet' => 'E-wallet',
    ];

    private const FULFILLMENT_LABELS = [
        'pickup' => 'Ambil di bengkel',
        'delivery' => 'Diantar bengkel',
        'shipping' => 'Dikirim paket',
    ];

    public function catalog(Request $request, string $id): View
    {
        $tenant = $this->activeTenant($id);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $parts = $tenant->run(function () use ($search) {
            $reservedParts = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->where('sales.status', 'pending')
                ->whereNotNull('sales.fulfillment_method')
                ->whereNotNull('customers.central_user_id')
                ->select('sale_items.spare_part_id')
                ->selectRaw('SUM(sale_items.quantity) as reserved_quantity')
                ->groupBy('sale_items.spare_part_id');

            $parts = DB::table('spare_parts')
                ->leftJoinSub($reservedParts, 'reserved_parts', function (Builder $join): void {
                    $join->on('reserved_parts.spare_part_id', '=', 'spare_parts.id');
                })
                ->where('spare_parts.is_active', true)
                ->whereRaw('spare_parts.stock > COALESCE(reserved_parts.reserved_quantity, 0)')
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('spare_parts.name', 'like', "%{$search}%")
                            ->orWhere('spare_parts.sku', 'like', "%{$search}%")
                            ->orWhere('spare_parts.category', 'like', "%{$search}%");
                    });
                })
                ->select([
                    'spare_parts.id',
                    'spare_parts.sku',
                    'spare_parts.name',
                    'spare_parts.stock',
                    'spare_parts.price',
                    'spare_parts.category',
                    'spare_parts.image_path',
                ])
                ->selectRaw('(spare_parts.stock - COALESCE(reserved_parts.reserved_quantity, 0)) as available_stock')
                ->orderBy('spare_parts.name')
                ->paginate(12)
                ->withQueryString();

            return $parts->through(function (object $part): object {
                $part->imageUrl = $part->image_path
                    ? Storage::disk('public')->url($part->image_path)
                    : null;

                return $part;
            });
        });

        return view('bengkel.katalog', [
            'tenant' => $tenant,
            'parts' => $parts,
            'search' => $search,
            'paymentMethods' => $tenant->getAttribute('payment_methods') ?? [],
            'fulfillmentModes' => $tenant->getAttribute('fulfillment_modes') ?? [],
        ]);
    }

    public function storeOrder(Request $request, string $id): RedirectResponse
    {
        $tenant = $this->activeTenant($id);
        $paymentMethods = $tenant->getAttribute('payment_methods') ?? [];
        $fulfillmentModes = $tenant->getAttribute('fulfillment_modes') ?? [];

        if ($paymentMethods === [] || $fulfillmentModes === [] || ! $tenant->getAttribute('address')) {
            throw ValidationException::withMessages([
                'order' => 'Bengkel belum melengkapi alamat, metode pembayaran, dan opsi pemenuhan pesanan.',
            ]);
        }

        $paymentKeys = array_column($paymentMethods, 'key');
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.part_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'between:1,1000'],
            'payment_method' => ['required', Rule::in($paymentKeys)],
            'fulfillment_method' => ['required', Rule::in($fulfillmentModes)],
            'delivery_address' => [
                Rule::requiredIf(in_array($request->input('fulfillment_method'), ['delivery', 'shipping'], true)),
                'nullable',
                'string',
                'max:2000',
            ],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Pilih setidaknya satu sparepart.',
            'payment_method.in' => 'Metode pembayaran yang dipilih tidak tersedia.',
            'fulfillment_method.in' => 'Cara menerima pesanan yang dipilih tidak tersedia.',
        ]);

        $paymentMethod = collect($paymentMethods)->firstWhere('key', $validated['payment_method']);
        $fulfillmentFee = (float) (($tenant->getAttribute('fulfillment_fees') ?? [])[$validated['fulfillment_method']] ?? 0);
        $deliveryAddress = in_array($validated['fulfillment_method'], ['delivery', 'shipping'], true)
            ? ($validated['delivery_address'] ?? null)
            : null;

        $invoiceNumber = $tenant->run(function () use ($validated, $request, $paymentMethod, $fulfillmentFee, $deliveryAddress): string {
            return DB::transaction(function () use ($validated, $request, $paymentMethod, $fulfillmentFee, $deliveryAddress): string {
                $items = collect($validated['items'])
                    ->keyBy(fn (array $item): int => (int) $item['part_id']);
                $partIds = $items->keys()->all();
                $parts = DB::table('spare_parts')
                    ->whereIn('id', $partIds)
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get(['id', 'sku', 'name', 'stock', 'price']);

                if ($parts->count() !== count($partIds)) {
                    throw ValidationException::withMessages([
                        'items' => 'Satu atau lebih sparepart sudah tidak tersedia.',
                    ]);
                }

                $lineItems = [];
                foreach ($parts as $part) {
                    $quantity = (int) $items->get($part->id)['quantity'];
                    $reservedQuantity = (int) DB::table('sale_items')
                        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                        ->join('customers', 'customers.id', '=', 'sales.customer_id')
                        ->where('sale_items.spare_part_id', $part->id)
                        ->where('sales.status', 'pending')
                        ->whereNotNull('sales.fulfillment_method')
                        ->whereNotNull('customers.central_user_id')
                        ->sum('sale_items.quantity');

                    if ((int) $part->stock - $reservedQuantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$part->name} tidak mencukupi untuk pesanan baru.",
                        ]);
                    }

                    $lineItems[] = [
                        'part_id' => $part->id,
                        'sku' => $part->sku,
                        'name' => $part->name,
                        'quantity' => $quantity,
                        'unit_price' => (float) $part->price,
                    ];
                }

                $subtotal = round(array_sum(array_map(
                    fn (array $item): float => $item['unit_price'] * $item['quantity'],
                    $lineItems,
                )), 2);
                $total = round($subtotal + $fulfillmentFee, 2);
                $user = $request->user();
                $customer = DB::table('customers')
                    ->where('central_user_id', $user->id)
                    ->first();
                $now = now();
                $customerData = [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => str_ends_with((string) $user->email, '@accounts.amahay.invalid')
                        ? null
                        : $user->email,
                    'updated_at' => $now,
                ];

                if ($customer === null) {
                    $customerId = DB::table('customers')->insertGetId([
                        ...$customerData,
                        'central_user_id' => $user->id,
                        'created_at' => $now,
                    ]);
                } else {
                    $customerId = $customer->id;
                    DB::table('customers')->where('id', $customerId)->update($customerData);
                }

                $invoiceNumber = 'ORD-'.Str::upper((string) Str::ulid());
                $saleId = DB::table('sales')->insertGetId([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customerId,
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $total,
                    'amount_paid' => 0,
                    'change_due' => 0,
                    'fulfillment_method' => $validated['fulfillment_method'],
                    'delivery_address' => $deliveryAddress,
                    'fulfillment_fee' => $fulfillmentFee,
                    'notes' => $validated['customer_note'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($lineItems as $item) {
                    DB::table('sale_items')->insert([
                        'sale_id' => $saleId,
                        'spare_part_id' => $item['part_id'],
                        'item_name' => $item['name'],
                        'sku' => $item['sku'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => 0,
                        'subtotal' => round($item['unit_price'] * $item['quantity'], 2),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('payments')->insert([
                    'sale_id' => $saleId,
                    'payment_method' => $paymentMethod['key'],
                    'amount' => $total,
                    'status' => 'pending',
                    'notes' => json_encode([
                        'label' => $paymentMethod['label'],
                        'instructions' => $paymentMethod['instructions'],
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                return $invoiceNumber;
            });
        });

        return redirect()->route('user.bengkel.pembayaran', [
            'id' => $tenant->getTenantKey(),
            'invoice' => $invoiceNumber,
        ])->with('success', 'Pesanan berhasil dibuat. Stok fisik akan dikurangi setelah pembayaran dikonfirmasi.');
    }

    public function payment(Request $request, string $id, string $invoice): View
    {
        $tenant = $this->activeTenant($id);
        $order = $tenant->run(function () use ($request, $invoice): ?array {
            $sale = DB::table('sales')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->where('sales.invoice_number', $invoice)
                ->where('customers.central_user_id', $request->user()->id)
                ->first([
                    'sales.id',
                    'sales.invoice_number',
                    'sales.status',
                    'sales.subtotal',
                    'sales.total',
                    'sales.fulfillment_method',
                    'sales.delivery_address',
                    'sales.fulfillment_fee',
                    'sales.notes as customer_note',
                    'sales.created_at',
                ]);

            if ($sale === null) {
                return null;
            }

            $payment = DB::table('payments')
                ->where('sale_id', $sale->id)
                ->latest('id')
                ->first(['payment_method', 'amount', 'status', 'notes', 'paid_at']);
            if ($payment === null) {
                return null;
            }
            $items = DB::table('sale_items')
                ->where('sale_id', $sale->id)
                ->get(['item_name', 'sku', 'quantity', 'unit_price', 'subtotal']);

            return [
                'sale' => $sale,
                'payment' => $payment,
                'items' => $items,
            ];
        });
        abort_if($order === null, 404);

        $paymentDetails = $order['payment']?->notes
            ? json_decode($order['payment']->notes, true, flags: JSON_THROW_ON_ERROR)
            : [];

        return view('bengkel.pembayaran', [
            'tenant' => $tenant,
            'order' => $order,
            'paymentDetails' => $paymentDetails,
            'paymentLabels' => self::PAYMENT_LABELS,
            'fulfillmentLabels' => self::FULFILLMENT_LABELS,
        ]);
    }

    private function activeTenant(string $id): Tenant
    {
        $tenant = Tenant::query()->with('domains')->findOrFail($id);
        abort_unless($tenant->isActive(), 404);

        return $tenant;
    }
}
