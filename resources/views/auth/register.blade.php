<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftarkan Tenant Bengkel - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#0b0f19] text-gray-200 min-h-screen flex items-center justify-center font-sans py-10">

    <div class="bg-[#111827] border border-gray-700/80 p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-lg mx-4">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-amber-700 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-amber-900/30 mb-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.9 6.9a2.12 2.12 0 1 1-3-3l6.9-6.9a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <h1 class="text-lg font-bold text-white tracking-wide">Daftarkan Tenant Bengkel</h1>
            <p class="text-xs text-gray-400 mt-1">Akun utama Anda akan menjadi akun pemilik bengkel.</p>
        </div>

        <div class="mb-5 rounded-xl border border-gray-700 bg-[#1f2937] px-4 py-3 text-xs">
            <p class="text-gray-400">Akun utama</p>
            <p class="mt-1 font-medium text-white">{{ auth()->user()->name }}</p>
            <p class="mt-0.5 text-gray-400">{{ auth()->user()->phone ?: auth()->user()->email }}</p>
        </div>

        <form action="{{ route('tenant.store') }}" method="POST" class="space-y-4">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-red-800 bg-red-950/40 px-4 py-3 text-xs text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-y-1.5">
                <label for="tenant_name" class="block text-xs font-medium text-gray-300">Nama Bengkel Anda</label>
                <input id="tenant_name" type="text" name="tenant_name" value="{{ old('tenant_name') }}" placeholder="Contoh: Bengkel Amahay" required
                    class="w-full bg-[#1f2937] border border-gray-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
            </div>

            <div class="space-y-1.5">
                <label for="subdomain" class="block text-xs font-medium text-gray-300">Domain Akses Bengkel</label>
                <div class="flex rounded-xl overflow-hidden border border-gray-700/80 bg-[#1f2937]">
                    <input id="subdomain" type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="majujaya" required
                        class="w-full min-w-0 bg-transparent px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none">
                    <span class="bg-[#1f2937] text-gray-400 px-3 sm:px-4 py-3 text-xs flex items-center border-l border-gray-700/80 font-medium whitespace-nowrap">.{{ $tenantBaseDomain }}</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="package" class="block text-xs font-medium text-gray-300">Pilih Paket Awal</label>
                <select id="package" name="package" required
                    class="w-full bg-[#1f2937] border border-gray-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-400 transition">
                    @foreach ($packages as $key => $package)
                        <option value="{{ $key }}" data-description="{{ $package['description'] }}" @selected(old('package', 'pro') === $key)>
                            {{ $package['name'] }} — {{ $package['price'] }}
                        </option>
                    @endforeach
                </select>
                <p id="package-description" aria-live="polite" class="rounded-lg border border-amber-400/30 bg-amber-500/5 px-3 py-2 text-xs text-amber-200">
                    {{ $packages[old('package', 'pro')]['description'] ?? $packages['pro']['description'] }}
                </p>
                <p class="text-xs text-gray-400">Paket dibayar per bulan dan aktif setelah pembayaran dikonfirmasi.</p>
            </div>

            <button type="submit"
                class="w-full bg-amber-700 hover:bg-amber-800 text-white font-medium py-3 rounded-xl shadow-lg shadow-amber-900/30 transition duration-200 text-sm mt-2">
                Lanjut ke Pembayaran
            </button>
        </form>

        <div class="text-center mt-5 text-xs text-gray-400">
            <a href="{{ route('user.menu') }}" class="text-amber-300 hover:underline font-medium">Kembali ke halaman utama</a>
        </div>
    </div>

    <script>
        const packageSelect = document.getElementById('package');
        const packageDescription = document.getElementById('package-description');

        packageSelect.addEventListener('change', () => {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];

            if (selectedOption) {
                packageDescription.textContent = selectedOption.dataset.description;
            }
        });
    </script>
</body>

</html>
