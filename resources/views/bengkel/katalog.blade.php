<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog {{ $tenant->getAttribute('tenant_name') }} - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0b0f19] font-sans text-gray-200">
    <header class="sticky top-0 z-10 border-b border-gray-800 bg-[#0b0f19]/95 px-4 py-4 backdrop-blur sm:px-6">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <a href="{{ route('user.menu') }}" class="text-[11px] text-blue-400 hover:text-blue-300">← Kembali ke daftar bengkel</a>
                <h1 class="mt-1 truncate text-base font-bold text-white">{{ $tenant->getAttribute('tenant_name') }}</h1>
                <p class="mt-1 text-xs text-gray-400">{{ $tenant->getAttribute('address') ?: 'Alamat belum dicantumkan' }}</p>
            </div>
            <a href="{{ route('user.bengkel.detail', ['id' => $tenant->getTenantKey()]) }}" class="inline-flex w-fit rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 transition hover:border-blue-500 hover:text-white">Detail & Booking Servis</a>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Katalog Sparepart</h2>
                <p class="mt-1 text-xs text-gray-400">Pilih barang yang tersedia dari bengkel ini.</p>
            </div>
            <form method="GET" action="{{ route('user.bengkel.katalog', ['id' => $tenant->getTenantKey()]) }}" class="flex w-full gap-2 sm:max-w-sm">
                <input type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Cari nama, SKU, atau kategori"
                    class="min-w-0 flex-1 rounded-xl border border-gray-700 bg-[#161e2e] px-3 py-2.5 text-xs text-white placeholder-gray-500 focus:border-blue-500 focus:outline-none">
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white hover:bg-blue-700">Cari</button>
            </form>
        </section>

        @if (session('success'))
            <p role="status" class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-xs text-green-300">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p role="alert" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-xs text-red-300">{{ $errors->first() }}</p>
        @endif

        @if ($parts->isNotEmpty())
            @auth
                <form method="POST" action="{{ route('user.bengkel.pesanan.store', ['id' => $tenant->getTenantKey()]) }}" class="space-y-6">
                    @csrf
            @endauth
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($parts as $index => $part)
                        <article class="flex flex-col justify-between gap-4 rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg">
                            <div>
                                @if ($part->imageUrl)
                                    <img src="{{ $part->imageUrl }}" alt="Foto {{ $part->name }}" loading="lazy" class="mb-4 aspect-[4/3] w-full rounded-xl border border-gray-800 bg-[#0b0f19] object-cover">
                                @else
                                    <div class="mb-4 flex aspect-[4/3] w-full items-center justify-center rounded-xl border border-dashed border-gray-700 bg-[#161e2e] text-xs text-gray-500">Foto belum tersedia</div>
                                @endif
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="break-words text-sm font-bold text-white">{{ $part->name }}</h3>
                                        <p class="mt-1 text-[10px] font-mono text-gray-400">{{ $part->sku }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-green-500/10 px-2 py-1 text-[10px] font-medium text-green-400">Tersedia {{ number_format($part->available_stock, 0, ',', '.') }}</span>
                                </div>
                                @if ($part->category)
                                    <p class="mt-3 text-[11px] text-gray-400">{{ $part->category }}</p>
                                @endif
                                <p class="mt-4 text-base font-bold text-blue-400">Rp {{ number_format($part->price, 0, ',', '.') }}</p>
                            </div>
                            @auth
                            <div class="flex items-center justify-between gap-3 border-t border-gray-800 pt-4">
                                <label for="part-{{ $part->id }}" class="flex items-center gap-2 text-xs text-gray-300">
                                    <input id="part-{{ $part->id }}" type="checkbox" name="items[{{ $index }}][part_id]" value="{{ $part->id }}" class="catalog-part rounded border-gray-600 bg-gray-900 text-blue-600 focus:ring-blue-500">
                                    Pilih
                                </label>
                                <label class="flex items-center gap-2 text-[11px] text-gray-400">
                                    <span>Jumlah</span>
                                    <input type="number" name="items[{{ $index }}][quantity]" value="1" min="1" max="{{ min(1000, (int) $part->available_stock) }}" disabled aria-label="Jumlah {{ $part->name }}"
                                        class="catalog-quantity w-20 rounded-lg border border-gray-700 bg-[#1f2937] px-2 py-1.5 text-xs text-white disabled:opacity-50">
                                </label>
                            </div>
                            @endauth
                        </article>
                    @endforeach
                </section>

            @auth
                    <section class="grid grid-cols-1 gap-4 rounded-2xl border border-gray-800 bg-[#111827] p-5 shadow-lg md:grid-cols-2 sm:p-6">
                        <input type="hidden" name="payment_method" value="cash">
                        <input type="hidden" name="fulfillment_method" value="pickup">
                        <label class="space-y-2 text-xs text-gray-300 md:col-span-2">
                            <span>Catatan untuk bengkel (opsional)</span>
                            <textarea name="customer_note" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-700 bg-[#1f2937] px-3 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">{{ old('customer_note') }}</textarea>
                        </label>
                        <p class="text-[11px] text-gray-400 md:col-span-2">Pembayaran dilakukan saat pengambilan di toko atau melalui metode online di halaman checkout.</p>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-3 text-xs font-semibold text-white transition hover:bg-blue-700 md:col-span-2">Checkout Sparepart</button>
                    </section>
                </form>
            @else
                <p class="rounded-xl border border-blue-500/20 bg-[#111827] px-4 py-4 text-center text-xs text-gray-300">
                    <a href="{{ route('login') }}" class="font-semibold text-blue-400 hover:text-blue-300">Masuk atau daftar</a>
                    untuk memilih sparepart dan checkout.
                </p>
            @endauth
        @else
            <div class="rounded-2xl border border-dashed border-gray-700 bg-[#111827] px-5 py-12 text-center">
                <p class="text-sm font-semibold text-white">{{ $search ? 'Sparepart tidak ditemukan.' : 'Belum ada sparepart tersedia.' }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ $search ? 'Coba kata kunci lain.' : 'Bengkel belum mempublikasikan sparepart dengan stok tersedia.' }}</p>
            </div>
        @endif

        @if ($parts->hasPages())
            <div>{{ $parts->links() }}</div>
        @endif
    </main>

    <script>
        document.querySelectorAll('.catalog-part').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                checkbox.closest('article').querySelector('.catalog-quantity').disabled = !checkbox.checked;
            });
        });

    </script>
</body>
</html>
