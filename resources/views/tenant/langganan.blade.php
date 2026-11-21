<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langganan &amp; Tagihan - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-[#0b0f19] font-sans text-gray-200">
    <main class="mx-auto max-w-6xl space-y-6 p-4 sm:p-8">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">Billing Owner</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Langganan &amp; Tagihan</h1>
                <p class="mt-1 text-sm text-gray-400">{{ $tenant->getAttribute('tenant_name') }}</p>
            </div>
            <a href="{{ route('owner.home') }}" class="rounded-lg border border-gray-700 px-4 py-2 text-xs font-medium text-gray-300 transition hover:border-amber-500 hover:text-white">Kembali ke Dashboard</a>
        </header>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
                <p class="text-xs uppercase tracking-wider text-gray-400">Paket aktif</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $activePackageName }}</p>
            </div>
            <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
                <p class="text-xs uppercase tracking-wider text-gray-400">Mulai berlangganan</p>
                <p class="mt-2 text-lg font-bold text-white">
                    {{ $subscriptionStartsAt ? \Carbon\CarbonImmutable::parse($subscriptionStartsAt)->translatedFormat('d M Y') : 'Belum aktif' }}
                </p>
            </div>
            <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
                <p class="text-xs uppercase tracking-wider text-gray-400">Berlaku sampai</p>
                <p class="mt-2 text-lg font-bold text-white">
                    {{ $subscriptionEndsAt ? \Carbon\CarbonImmutable::parse($subscriptionEndsAt)->translatedFormat('d M Y') : 'Belum aktif' }}
                </p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-800 bg-[#111827]">
            <div class="border-b border-gray-800 px-5 py-4">
                <h2 class="font-semibold text-white">Riwayat Invoice Paket</h2>
            </div>
            @if ($invoices->isEmpty())
                <p class="px-5 py-8 text-sm text-gray-400">Belum ada tagihan langganan.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-[#161e2e] text-xs uppercase tracking-wider text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Paket</th>
                                <th class="px-5 py-3">Nominal</th>
                                <th class="px-5 py-3">Metode</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="px-5 py-4 font-mono text-xs text-amber-300">{{ $invoice->invoice_number }}</td>
                                    <td class="px-5 py-4 text-white">{{ $invoice->package_name }}</td>
                                    <td class="px-5 py-4 text-white">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-gray-300">
                                        @if ($invoice->payment_method === 'qris')
                                            QRIS
                                        @elseif ($invoice->payment_method === 'virtual_account')
                                            Virtual Account
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $invoice->status === 'paid' ? 'bg-green-500/10 text-green-300' : 'bg-yellow-500/10 text-yellow-300' }}">
                                            {{ $invoice->status === 'paid' ? 'Lunas' : 'Menunggu pembayaran' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-400">
                                        {{ \Carbon\CarbonImmutable::parse($invoice->created_at)->translatedFormat('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</body>

</html>
