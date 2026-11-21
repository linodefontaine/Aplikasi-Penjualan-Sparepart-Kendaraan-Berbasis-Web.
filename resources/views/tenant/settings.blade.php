<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Pesanan - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0b0f19] font-sans text-gray-200">
    <header class="border-b border-gray-800 bg-[#111827] px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-sm font-bold text-white">Pengaturan Katalog & Pesanan</h1>
                <p class="mt-1 text-xs text-gray-400">{{ $tenant->getAttribute('tenant_name') }}</p>
            </div>
            <a href="{{ route('owner.home') }}" class="inline-flex w-fit rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 transition hover:border-amber-500 hover:text-white">Kembali ke Dashboard</a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 sm:py-8">
        @if (session('success'))
            <p role="status" class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-xs text-green-300">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p role="alert" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-xs text-red-300">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('owner.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="space-y-4 rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
                <div>
                    <h2 class="text-sm font-bold text-white">Alamat Bengkel</h2>
                    <p class="mt-1 text-xs text-gray-400">Alamat ditampilkan pada direktori bengkel dan halaman katalog konsumen.</p>
                </div>
                <label for="address" class="block space-y-2 text-xs text-gray-300">
                    <span>Alamat lengkap</span>
                    <textarea id="address" name="address" rows="3" maxlength="2000" required class="w-full rounded-xl border border-gray-700 bg-[#161e2e] px-3 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none">{{ old('address', $tenant->getAttribute('address')) }}</textarea>
                </label>
            </section>

            <section class="space-y-4 rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
                <div>
                    <h2 class="text-sm font-bold text-white">Metode Pembayaran</h2>
                    <p class="mt-1 text-xs text-gray-400">Pembayaran dilakukan manual. Pesanan baru menunggu konfirmasi Owner/Kasir sebelum stok berkurang.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($paymentOptions as $key => $label)
                        @php
                            $savedMethod = collect($paymentMethods)->firstWhere('key', $key);
                            $isSelected = in_array($key, old('payment_methods', array_column($paymentMethods, 'key')), true);
                        @endphp
                        <div class="space-y-3 rounded-xl border border-gray-800 bg-[#161e2e] p-4">
                            <label class="flex items-center gap-2 text-xs font-medium text-white">
                                <input type="checkbox" name="payment_methods[]" value="{{ $key }}" @checked($isSelected) class="rounded border-gray-600 bg-gray-900 text-amber-600 focus:ring-amber-500">
                                {{ $label }}
                            </label>
                            <label class="block space-y-1.5 text-[11px] text-gray-400">
                                <span>Instruksi untuk konsumen</span>
                                <textarea name="payment_instructions[{{ $key }}]" rows="2" maxlength="500" placeholder="Contoh: transfer ke rekening atas nama bengkel"
                                    class="w-full rounded-lg border border-gray-700 bg-[#1f2937] px-3 py-2 text-xs text-white focus:border-amber-500 focus:outline-none">{{ old("payment_instructions.{$key}", $savedMethod['instructions'] ?? '') }}</textarea>
                            </label>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg sm:p-6">
                <div>
                    <h2 class="text-sm font-bold text-white">Cara Pemenuhan Pesanan</h2>
                    <p class="mt-1 text-xs text-gray-400">Pilih opsi yang benar-benar tersedia di bengkel Anda.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($fulfillmentOptions as $key => $label)
                        <label class="flex items-center gap-2 rounded-xl border border-gray-800 bg-[#161e2e] p-4 text-xs text-white">
                            <input type="checkbox" name="fulfillment_modes[]" value="{{ $key }}" @checked(in_array($key, old('fulfillment_modes', $fulfillmentModes), true)) class="rounded border-gray-600 bg-gray-900 text-amber-600 focus:ring-amber-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-1.5 text-xs text-gray-400">
                        <span>Biaya antar bengkel (Rp)</span>
                        <input type="number" name="fulfillment_fees[delivery]" min="0" max="100000000" step="1" value="{{ old('fulfillment_fees.delivery', $fulfillmentFees['delivery'] ?? 0) }}" class="w-full rounded-lg border border-gray-700 bg-[#161e2e] px-3 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none">
                    </label>
                    <label class="block space-y-1.5 text-xs text-gray-400">
                        <span>Biaya kirim paket (Rp)</span>
                        <input type="number" name="fulfillment_fees[shipping]" min="0" max="100000000" step="1" value="{{ old('fulfillment_fees.shipping', $fulfillmentFees['shipping'] ?? 0) }}" class="w-full rounded-lg border border-gray-700 bg-[#161e2e] px-3 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none">
                    </label>
                </div>
            </section>

            <button type="submit" class="w-full rounded-xl bg-amber-600 px-4 py-3 text-xs font-semibold text-white transition hover:bg-amber-600 sm:w-auto">Simpan Pengaturan</button>
        </form>
    </main>
</body>
</html>
