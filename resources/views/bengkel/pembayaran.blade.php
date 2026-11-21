<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran {{ $order['sale']->invoice_number }} - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
            .receipt { border: 0 !important; box-shadow: none !important; color: black !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#0b0f19] font-sans text-gray-200">
    @php
        $isPaid = $order['sale']->status === 'completed' || $order['payment']->status === 'completed';
        $isCancelled = $order['sale']->status === 'cancelled' || $order['payment']->status === 'cancelled';
        $canPay = ! $isPaid && ! $isCancelled && $order['payment']->status === 'pending';
        $paymentStatusLabel = $isPaid ? 'Lunas' : ($isCancelled ? 'Dibatalkan' : 'Belum dibayar');
    @endphp

    <header class="no-print border-b border-gray-800 bg-[#111827] px-4 py-4 sm:px-6">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 font-bold text-white">A</div>
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-blue-400">Pembayaran aman · mode demo</p>
                    <h1 class="text-sm font-bold text-white">Checkout Pembayaran</h1>
                </div>
            </div>
            <a href="{{ route('user.bengkel.katalog', ['id' => $tenant->getTenantKey()]) }}" class="rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 transition hover:border-blue-500 hover:text-white">Kembali ke Katalog</a>
        </div>
    </header>

    <main class="mx-auto grid max-w-5xl gap-6 px-4 py-6 sm:px-6 sm:py-8 lg:grid-cols-[1.2fr_.8fr]">
        <div class="space-y-5">
            @if (session('success'))
                <p role="status" class="no-print rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-xs text-green-300">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p role="alert" class="no-print rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-xs text-red-300">{{ session('error') }}</p>
            @endif
            @if ($errors->any())
                <p role="alert" class="no-print rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-xs text-red-300">{{ $errors->first() }}</p>
            @endif

            <section class="rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-800 pb-4">
                    <div>
                        <p class="text-xs font-semibold text-white">{{ $tenant->getAttribute('tenant_name') }}</p>
                        <p class="mt-1 text-[10px] text-gray-400">Penerima pembayaran</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-[10px] font-semibold {{ $isPaid ? 'bg-green-500/10 text-green-300' : ($isCancelled ? 'bg-gray-700 text-gray-300' : 'bg-amber-500/10 text-amber-300') }}">
                        {{ $paymentStatusLabel }}
                    </span>
                </div>
                <div class="mt-4 flex flex-wrap justify-between gap-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-500">Kode invoice</p>
                        <p class="mt-1 break-all font-mono text-sm font-bold text-blue-400">{{ $order['sale']->invoice_number }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-[10px] text-gray-500">Pelanggan</p>
                        <p class="mt-1 text-xs font-medium text-white">{{ $order['sale']->customer_name }}</p>
                    </div>
                </div>
                @if ($order['sale']->delivery_address)
                    <p class="mt-4 border-t border-gray-800 pt-3 text-[11px] text-gray-400">Alamat penerima: <span class="text-gray-200">{{ $order['sale']->delivery_address }}</span></p>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
                <h2 class="text-sm font-bold text-white">Rincian transaksi</h2>
                <div class="mt-3 divide-y divide-gray-800">
                    @foreach ($order['items'] as $item)
                        <div class="flex items-start justify-between gap-4 py-3">
                            <div>
                                <p class="text-xs font-medium text-white">{{ $item->item_name }}</p>
                                <p class="mt-1 text-[10px] text-gray-400">{{ $item->sku ?: 'Item transaksi' }} · {{ number_format($item->quantity, 0, ',', '.') }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <p class="shrink-0 text-xs font-semibold text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 space-y-2 border-t border-gray-800 pt-4 text-xs">
                    <div class="flex justify-between gap-4 text-gray-400">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($order['sale']->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ((float) $order['sale']->fulfillment_fee > 0)
                        <div class="flex justify-between gap-4 text-gray-400">
                            <span>Biaya pemenuhan</span>
                            <span>Rp {{ number_format($order['sale']->fulfillment_fee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 border-t border-gray-800 pt-3 text-sm font-bold text-white">
                        <span>Total tagihan</span>
                        <span>Rp {{ number_format($order['sale']->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            @if ($isPaid)
                <section id="receipt" class="receipt rounded-2xl border border-green-500/30 bg-white p-5 text-gray-900 shadow-lg sm:p-6">
                    <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-green-700">Bukti pembayaran</p>
                            <h2 class="mt-1 text-lg font-bold">Struk Lunas</h2>
                            <p class="mt-1 text-xs">{{ $tenant->getAttribute('tenant_name') }}</p>
                        </div>
                        <p class="font-mono text-xs font-semibold">{{ $order['sale']->invoice_number }}</p>
                    </div>
                    <div class="mt-4 space-y-2 text-xs">
                        @foreach ($order['items'] as $item)
                            <div class="flex justify-between gap-3">
                                <span>{{ $item->item_name }} × {{ number_format($item->quantity, 0, ',', '.') }}</span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-between border-t border-gray-200 pt-3 text-sm font-bold">
                        <span>Total dibayar</span>
                        <span>Rp {{ number_format($order['payment']->amount, 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-600">Metode: {{ $paymentMethods[$order['payment']->payment_method] ?? $paymentDetails['label'] ?? $order['payment']->payment_method }} · {{ $order['payment']->paid_at ? \Illuminate\Support\Carbon::parse($order['payment']->paid_at)->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : now('Asia/Jakarta')->format('d M Y H:i').' WIB' }}</p>
                    <button type="button" onclick="window.print()" class="no-print mt-5 rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-blue-700">Cetak struk</button>
                </section>
            @elseif ($isCancelled)
                <p class="rounded-xl border border-gray-700 bg-[#111827] p-4 text-xs text-gray-300">Transaksi ini telah dibatalkan.</p>
            @endif
        </div>

        @if ($canPay)
            <section class="no-print h-fit rounded-2xl border border-blue-500/20 bg-[#111827] p-5 shadow-lg sm:p-6">
                <h2 class="text-sm font-bold text-white">Pilih metode pembayaran</h2>
                <p class="mt-1 text-[11px] text-gray-400">Simulasi presentasi—tidak ada uang sungguhan yang diproses.</p>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ($paymentMethods as $method => $label)
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.metode', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="{{ $method }}">
                            <button type="submit" class="w-full rounded-xl border px-3 py-3 text-left text-[11px] font-semibold transition {{ $selectedMethod === $method ? 'border-blue-500 bg-blue-600/15 text-blue-300' : 'border-gray-700 bg-[#161e2e] text-gray-300 hover:border-blue-500' }}">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>

                @if ($selectedMethod === 'qris')
                    @if (! $sandboxEnabled)
                        <p class="mt-4 rounded-xl bg-yellow-500/10 p-3 text-xs text-yellow-200">QRIS sandbox belum dikonfigurasi untuk lingkungan ini.</p>
                    @elseif ($order['active'] && $qrisSvg)
                        <div class="mt-5 grid gap-4 sm:grid-cols-[160px_1fr] sm:items-center">
                            <div class="mx-auto rounded-xl bg-white p-2" aria-label="Kode QRIS demo">{!! $qrisSvg !!}</div>
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-white">Pindai QRIS demo</p>
                                <p class="text-[10px] leading-relaxed text-gray-400">QR ini hanya simulasi lokal dan tidak menerima pembayaran sungguhan.</p>
                                <p class="text-[10px] text-gray-400">Referensi: <span class="font-mono text-gray-200">{{ $order['attempt']->payment_reference }}</span></p>
                                <p class="text-[10px] text-gray-400">Berlaku sampai {{ \Illuminate\Support\Carbon::parse($order['attempt']->expires_at)->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.simulasi', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="payment_method" value="qris">
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white transition hover:bg-blue-700">Simulasi Pembayaran Berhasil</button>
                        </form>
                    @else
                        <p class="mt-4 rounded-xl bg-blue-500/10 p-3 text-xs text-blue-200">{{ $order['expired'] ? 'QRIS sebelumnya kedaluwarsa. Buat tagihan baru.' : 'Buat QRIS demo untuk menampilkan kode pembayaran.' }}</p>
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.metode', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="payment_method" value="qris">
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white hover:bg-blue-700">{{ $order['expired'] ? 'Buat QRIS baru' : 'Tampilkan kode QRIS' }}</button>
                        </form>
                    @endif
                @elseif ($selectedMethod && str_starts_with($selectedMethod, 'va_') && $virtualAccountNumber)
                    <div class="mt-5 rounded-xl border border-gray-700 bg-[#161e2e] p-4">
                        <p class="text-[10px] uppercase tracking-wider text-gray-400">{{ $paymentMethods[$selectedMethod] }}</p>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p id="virtual-account-number" class="font-mono text-lg font-bold tracking-wider text-white">{{ $virtualAccountNumber }}</p>
                            <button type="button" onclick="copyVirtualAccount()" class="rounded-lg border border-gray-600 px-3 py-2 text-[10px] font-semibold text-blue-300 hover:border-blue-500">Salin</button>
                        </div>
                        <p class="mt-3 text-[10px] leading-relaxed text-gray-400">Nomor VA ini dibuat untuk simulasi. Gunakan menu Virtual Account di aplikasi bank hanya pada presentasi demo.</p>
                    </div>
                    @if ($methodReady)
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.simulasi', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="payment_method" value="{{ $selectedMethod }}">
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white hover:bg-blue-700">Simulasi Pembayaran Berhasil</button>
                        </form>
                    @endif
                @elseif ($selectedMethod === 'ewallet')
                    <div class="mt-5 rounded-xl border border-gray-700 bg-[#161e2e] p-4">
                        <p class="text-xs font-semibold text-white">E-wallet demo</p>
                        <p class="mt-2 text-[11px] leading-relaxed text-gray-400">Lanjutkan simulasi pembayaran menggunakan e-wallet. Tidak ada akun atau saldo sungguhan yang diakses.</p>
                    </div>
                    @if ($methodReady)
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.simulasi', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="payment_method" value="ewallet">
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white hover:bg-blue-700">Simulasi Pembayaran Berhasil</button>
                        </form>
                    @endif
                @elseif ($selectedMethod === 'cash')
                    <div class="mt-5 rounded-xl border border-gray-700 bg-[#161e2e] p-4">
                        <p class="text-xs font-semibold text-white">Bayar di Tempat / Ambil di Toko</p>
                        <p class="mt-2 text-[11px] leading-relaxed text-gray-400">Tunjukkan invoice saat mengambil sparepart di bengkel. Untuk demo presentasi, Anda dapat menandai pembayaran sebagai berhasil.</p>
                    </div>
                    @if ($methodReady)
                        <form method="POST" action="{{ route('user.bengkel.pembayaran.simulasi', ['id' => $tenant->getTenantKey(), 'invoice' => $order['sale']->invoice_number]) }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="payment_method" value="cash">
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white hover:bg-blue-700">Simulasi Pembayaran Berhasil</button>
                        </form>
                    @endif
                @else
                    <p class="mt-4 rounded-xl bg-gray-800 p-3 text-xs text-gray-300">{{ $paymentDetails['instructions'] ?? 'Pilih salah satu metode pembayaran untuk melanjutkan.' }}</p>
                @endif

                @if (! $methodReady && $selectedMethod !== 'qris')
                    <p class="mt-4 text-[10px] text-amber-200">Pilih metode pembayaran di atas untuk menyimpan pilihan sebelum simulasi.</p>
                @endif
            </section>
        @endif
    </main>

    <script>
        async function copyVirtualAccount() {
            const number = document.getElementById('virtual-account-number').textContent.trim();
            await navigator.clipboard.writeText(number);
        }
    </script>
</body>
</html>
