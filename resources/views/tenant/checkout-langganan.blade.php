<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Langganan - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-[#0b0f19] px-4 py-10 font-sans text-gray-200">
    <main class="mx-auto w-full max-w-3xl space-y-6">
        <header class="space-y-2 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">Checkout Langganan</p>
            <h1 class="text-2xl font-bold text-white">Pembayaran Paket Bengkel</h1>
            <p class="text-sm text-gray-400">Selesaikan simulasi pembayaran untuk mengaktifkan akses dashboard Owner.</p>
        </header>

        <section class="rounded-3xl border border-gray-700 bg-[#111827] p-6 shadow-2xl sm:p-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400">Ringkasan Tagihan</h2>
            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-700 bg-[#1f2937] p-4">
                    <dt class="text-xs text-gray-400">Nama bengkel</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $tenant->getAttribute('tenant_name') }}</dd>
                </div>
                <div class="rounded-xl border border-gray-700 bg-[#1f2937] p-4">
                    <dt class="text-xs text-gray-400">Paket</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $invoice->package_name }}</dd>
                </div>
                <div class="rounded-xl border border-gray-700 bg-[#1f2937] p-4">
                    <dt class="text-xs text-gray-400">Kode invoice</dt>
                    <dd class="mt-1 break-all font-mono text-sm font-semibold text-amber-200">{{ $invoice->invoice_number }}</dd>
                </div>
                <div class="rounded-xl border border-amber-400/40 bg-amber-500/5 p-4">
                    <dt class="text-xs text-gray-400">Total tagihan / bulan</dt>
                    <dd class="mt-1 text-xl font-bold text-white">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-800 bg-red-950/40 px-4 py-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.checkout.simulate', ['invoice' => $invoice->invoice_number]) }}" class="mt-7 space-y-5">
                @csrf
                <fieldset class="space-y-3">
                    <legend class="text-sm font-semibold text-white">Pilih metode pembayaran</legend>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-700 bg-[#1f2937] p-4 transition hover:border-amber-400">
                        <input type="radio" name="payment_method" value="qris" required @checked(old('payment_method', $invoice->payment_method ?? 'qris') === 'qris') class="accent-amber-400">
                        <span>
                            <span class="block text-sm font-semibold text-white">QRIS</span>
                            <span class="mt-1 block text-xs text-gray-400">Pembayaran QRIS simulasi untuk pengujian presentasi.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-700 bg-[#1f2937] p-4 transition hover:border-amber-400">
                        <input type="radio" name="payment_method" value="virtual_account" required @checked(old('payment_method', $invoice->payment_method ?? 'qris') === 'virtual_account') class="accent-amber-400">
                        <span>
                            <span class="block text-sm font-semibold text-white">Virtual Account</span>
                            <span class="mt-1 block text-xs text-gray-400">Virtual Account simulasi untuk pengujian presentasi.</span>
                        </span>
                    </label>
                </fieldset>

                <button type="submit" class="w-full rounded-xl bg-amber-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-900/25 transition hover:bg-amber-800">
                    Konfirmasi / Simulasi Pembayaran Berhasil
                </button>
                <p class="text-center text-xs text-gray-500">Tombol ini hanya simulasi presentasi, bukan pemrosesan pembayaran sungguhan.</p>
            </form>
        </section>
    </main>
</body>

</html>
