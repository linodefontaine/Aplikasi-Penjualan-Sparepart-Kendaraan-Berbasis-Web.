<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Baru - AMAHAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b0f19] text-gray-200 min-h-screen flex items-center justify-center font-sans px-4 py-10">
    <main class="bg-[#111827] border border-gray-800 p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center gap-2.5 mb-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.9 6.9a2.12 2.12 0 1 1-3-3l6.9-6.9a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold tracking-wider text-white">AMAHAY</span>
            </div>
            <h1 class="text-lg font-semibold text-white mt-1">Buat Password Baru</h1>
            <p class="text-xs text-gray-400 mt-1">Gunakan password minimal 8 karakter.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-xl border border-red-800 bg-red-950/40 px-4 py-3 text-xs text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="block text-xs font-medium text-gray-300 mb-1.5">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email"
                    class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label for="password" class="block text-xs font-medium text-gray-300 mb-1.5">Password baru</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-gray-300 mb-1.5">Konfirmasi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    class="w-full bg-[#1f2937] border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl shadow-lg shadow-blue-600/30 transition duration-200 text-sm">Simpan password baru</button>
        </form>

        <div class="text-center mt-6 text-xs text-gray-400">
            <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-medium">Kembali ke login</a>
        </div>
    </main>
</body>
</html>
