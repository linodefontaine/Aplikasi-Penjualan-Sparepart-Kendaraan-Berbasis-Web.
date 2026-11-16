<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran {{ $order['sale']->invoice_number }} - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0b0f19] font-sans text-gray-200">
    <header class="border-b border-gray-800 bg-[#111827] px-4 py-4 sm:px-6">
        <div class="mx-auto flex max-w-3xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-sm font-bold text-white">Informasi Pesanan</h1>
                <p class="mt-1 text-xs text-gray-400">{{ $tenant->getAttribute('tenant_name') }}</p>
            </div>
            <a href="{{ route('user.bengkel.katalog', ['id' => $tenant->getTenantKey()]) }}" class="inline-flex w-fit rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 transition hover:border-blue-500 hover:text-white">Kembali ke Katalog</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 sm:py-8">
        @if (session('success'))
            <p role="status" class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-xs text-green-300">{{ session('success') }}</p>
        @endif

        <section class="rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Nomor Pesanan</p>
                    <h2 class="mt-1 break-all font-mono text-sm font-bold text-blue-400">{{ $order['sale']->invoice_number }}</h2>
                </div>
                <span class="w-fit rounded-full px-3 py-1 text-[10px] font-semibold {{ $order['sale']->status === 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-300' }}">
                    {{ $order['sale']->status === 'completed' ? 'Pembayaran dikonfirmasi' : 'Menunggu konfirmasi pembayaran' }}
                </span>
            </div>
            <div class="mt-5 grid gap-4 border-t border-gray-800 pt-4 sm:grid-cols-2">
                <div>
                    <p class="text-[10px] text-gray-400">Cara pemenuhan</p>
                    <p class="mt-1 text-xs font-medium text-white">{{ $fulfillmentLabels[$order['sale']->fulfillment_method] ?? $order['sale']->fulfillment_method }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400">Alamat bengkel</p>
                    <p class="mt-1 text-xs text-white">{{ $tenant->getAttribute('address') }}</p>
                </div>
                @if ($order['sale']->delivery_address)
                    <div class="sm:col-span-2">
                        <p class="text-[10px] text-gray-400">Alamat penerima</p>
                        <p class="mt-1 whitespace-pre-line text-xs text-white">{{ $order['sale']->delivery_address }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
            <h2 class="text-sm font-bold text-white">Rincian Sparepart</h2>
            <div class="mt-4 divide-y divide-gray-800">
                @foreach ($order['items'] as $item)
                    <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-medium text-white">{{ $item->item_name }}</p>
                            <p class="mt-1 text-[10px] text-gray-400">{{ $item->sku }} · {{ number_format($item->quantity, 0, ',', '.') }} unit × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <p class="text-xs font-semibold text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 space-y-2 border-t border-gray-800 pt-4 text-xs">
                <div class="flex justify-between gap-4 text-gray-400">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order['sale']->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4 text-gray-400">
                    <span>Biaya pemenuhan</span>
                    <span>Rp {{ number_format($order['sale']->fulfillment_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t border-gray-800 pt-3 text-sm font-bold text-white">
                    <span>Total</span>
                    <span>Rp {{ number_format($order['sale']->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-blue-500/20 bg-[#111827] p-5 shadow-lg sm:p-6">
            <h2 class="text-sm font-bold text-white">Pembayaran Manual</h2>
            <p class="mt-1 text-xs text-gray-400">Metode: {{ $paymentLabels[$order['payment']->payment_method] ?? $order['payment']->payment_method }}</p>
            <div class="mt-4 rounded-xl border border-gray-800 bg-[#161e2e] p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Instruksi bengkel</p>
                <p class="mt-2 whitespace-pre-line text-xs text-white">{{ $paymentDetails['instructions'] ?? 'Silakan hubungi bengkel untuk menyelesaikan pembayaran.' }}</p>
            </div>
            @if ($order['payment']->status === 'pending')
                <p class="mt-4 text-xs leading-relaxed text-yellow-200">Setelah melakukan pembayaran, hubungi atau tunjukkan nomor pesanan ini kepada bengkel. Status dan stok akan diperbarui setelah Owner/Kasir memverifikasi pembayaran. Website ini belum menggunakan payment gateway.</p>
            @else
                <p class="mt-4 text-xs text-green-300">Pembayaran telah dikonfirmasi oleh bengkel. Terima kasih telah berbelanja.</p>
            @endif
        </section>
    </main>
</body>
</html>
