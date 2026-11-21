<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AMAHAY - Manajemen Bengkel</title>

    <style>
        :root {
            --bg: #0b0f19;
            --bg-soft: #111827;
            --card: #111827;
            --card-light: #1f2937;
            --border: rgba(148, 163, 184, 0.24);

            --accent: #f59e0b;
            --accent-light: #fbbf24;
            --green: #19c37d;
            --yellow: #f4c542;
            --red: #ff5c70;
            --purple: #9b7cff;

            --text: #f8fafc;
            --muted: #cbd5e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font-family: inherit;
        }

        .container {
            width: min(1180px, calc(100% - 48px));
            margin: auto;
        }

        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {
            position: relative;
            z-index: 20;
            height: 72px;
            border-bottom: 1px solid var(--border);
            background: rgba(8, 15, 34, 0.96);
        }

        .navbar-inner {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .brand-icon {
            width: 25px;
            height: 25px;
            border-radius: 6px;
            background: linear-gradient(135deg, #fbbf24, #f97316);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(245, 158, 11, .25);
            flex-shrink: 0;
        }

        .brand-icon svg {
            width: 16px;
            height: 16px;
            display: block;
        }

        .brand span {
            color: #ffffff;
        }

        .brand em {
            color: var(--accent-light);
            font-style: normal;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-links a {
            color: #cbd5e1;
            font-size: 12px;
            transition: .2s;
        }

        .nav-links a:hover {
            color: white;
        }

        .nav-login {
            padding: 9px 17px;
            border-radius: 6px;
            background: #f59e0b;
            color: white !important;
            font-weight: 600;
        }

        /* =====================================================
           HERO
        ===================================================== */

        .hero {
            position: relative;
            overflow: hidden;
            padding: 75px 0 65px;
            background:
                radial-gradient(circle at 78% 38%, rgba(245, 158, 11, .13), transparent 28%),
                radial-gradient(circle at 20% 30%, rgba(245, 158, 11, .08), transparent 28%),
                var(--bg);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: 55px;
        }

        .hero-copy {
            max-width: 560px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #fbbf24;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .eyebrow::before {
            content: "";
            width: 18px;
            height: 1px;
            background: var(--accent);
        }

        .hero h1 {
            font-size: clamp(39px, 5vw, 58px);
            line-height: 1.04;
            letter-spacing: -2.4px;
            margin-bottom: 21px;
            font-weight: 800;
        }

        .hero h1 span {
            color: var(--accent-light);
        }

        .hero-description {
            max-width: 480px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 27px;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 18px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            transition: .2s;
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
            box-shadow: 0 7px 22px rgba(245, 158, 11, .2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            background: #f97316;
        }

        .btn-outline {
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .025);
            color: #cbd5e1;
        }

        .btn-outline:hover {
            border-color: rgba(245, 158, 11, .5);
            color: white;
        }

        /* =====================================================
           HERO DASHBOARD
        ===================================================== */

        .hero-dashboard {
            position: relative;
            min-height: 320px;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, .07);
            background:
                linear-gradient(145deg, rgba(20, 34, 68, .98), rgba(10, 19, 41, .98));
            box-shadow:
                0 25px 60px rgba(0, 0, 0, .35),
                0 0 50px rgba(245, 158, 11, .07);
        }

        .dashboard-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 17px;
        }

        .dashboard-title {
            font-size: 11px;
            font-weight: 700;
        }

        .dashboard-date {
            color: #94a3b8;
            font-size: 9px;
        }

        .chart-area {
            height: 140px;
            position: relative;
            padding: 12px 10px;
            border-radius: 7px;
            background: rgba(255, 255, 255, .025);
            overflow: hidden;
        }

        .chart-grid {
            position: absolute;
            inset: 10px;
            background:
                repeating-linear-gradient(to bottom,
                    rgba(255, 255, 255, .055) 0,
                    rgba(255, 255, 255, .055) 1px,
                    transparent 1px,
                    transparent 28px);
        }

        .chart-bars {
            position: absolute;
            inset: 15px 18px 18px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            gap: 12px;
        }

        .bar {
            width: 18px;
            border-radius: 3px 3px 0 0;
            background: linear-gradient(to top, #d97706, #f97316);
            box-shadow: 0 0 14px rgba(245, 158, 11, .18);
        }

        .bar:nth-child(1) {
            height: 42%;
        }

        .bar:nth-child(2) {
            height: 61%;
        }

        .bar:nth-child(3) {
            height: 48%;
        }

        .bar:nth-child(4) {
            height: 76%;
        }

        .bar:nth-child(5) {
            height: 55%;
        }

        .bar:nth-child(6) {
            height: 88%;
        }

        .bar:nth-child(7) {
            height: 69%;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 9px;
        }

        .mini-stat {
            padding: 11px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, .05);
            background: rgba(255, 255, 255, .025);
        }

        .mini-stat small {
            display: block;
            color: #94a3b8;
            font-size: 8px;
            margin-bottom: 4px;
        }

        .mini-stat strong {
            font-size: 14px;
        }

        .mini-stat .positive {
            color: var(--green);
            font-size: 8px;
            margin-left: 3px;
        }

        /* =====================================================
           STATISTICS
        ===================================================== */

        .statistics {
            padding: 35px 0 50px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 18px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(17, 28, 56, .65);
        }

        .stat-card small {
            display: block;
            color: #94a3b8;
            font-size: 9px;
            margin-bottom: 7px;
        }

        .stat-card strong {
            font-size: 20px;
            letter-spacing: -.5px;
        }

        .stat-card p {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 8px;
        }

        .stat-card p.green {
            color: var(--green);
        }

        /* =====================================================
           SECTION HEADER
        ===================================================== */

        .section {
            padding: 65px 0;
        }

        .section-header {
            text-align: center;
            max-width: 590px;
            margin: 0 auto 35px;
        }

        .section-label {
            color: #fbbf24;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .section-header h2 {
            font-size: 28px;
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .section-header p {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.7;
        }

        /* =====================================================
           FEATURES
        ===================================================== */

        .features {
            background: #0b0f19;
            border-top: 1px solid rgba(255, 255, 255, .025);
            border-bottom: 1px solid rgba(255, 255, 255, .025);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .feature-card {
            min-height: 170px;
            padding: 20px;
            border-radius: 8px;
            background: #111827;
            border: 1px solid rgba(255, 255, 255, .06);
            transition: .2s;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: rgba(245, 158, 11, .3);
        }

        .feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            background: rgba(245, 158, 11, .11);
            color: #f59e0b;
            font-size: 14px;
            font-weight: 700;
        }

        .feature-card:nth-child(2) .feature-icon {
            background: rgba(25, 195, 125, .1);
            color: var(--green);
        }

        .feature-card:nth-child(3) .feature-icon {
            background: rgba(244, 197, 66, .1);
            color: var(--yellow);
        }

        .feature-card:nth-child(4) .feature-icon {
            background: rgba(155, 124, 255, .1);
            color: var(--purple);
        }

        .feature-card h3 {
            font-size: 12px;
            margin-bottom: 7px;
        }

        .feature-card p {
            color: #94a3b8;
            font-size: 9px;
            line-height: 1.65;
        }

        /* =====================================================
           PROCESS
        ===================================================== */

        .process-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .process-card {
            position: relative;
            padding: 22px;
            border-radius: 8px;
            background: var(--card);
            border: 1px solid var(--border);
        }

        .process-number {
            color: #f59e0b;
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .process-card h3 {
            font-size: 12px;
            margin-bottom: 7px;
        }

        .process-card p {
            color: #94a3b8;
            font-size: 9px;
            line-height: 1.7;
        }

        /* =====================================================
           PRICING
        ===================================================== */

        .pricing {
            background:
                radial-gradient(circle at 50% 10%, rgba(245, 158, 11, .08), transparent 30%),
                #0b0f19;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 13px;
            max-width: 920px;
            margin: auto;
        }

        .price-card {
            position: relative;
            padding: 25px 21px;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: #111827;
        }

        .price-card.popular {
            border-color: rgba(245, 158, 11, .7);
            box-shadow: 0 0 35px rgba(245, 158, 11, .08);
        }

        .popular-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--accent);
            color: #ffffff;
            font-size: 7px;
            font-weight: 700;
            white-space: nowrap;
        }

        .price-name {
            color: #9ba9c6;
            font-size: 10px;
            margin-bottom: 9px;
        }

        .price {
            font-size: 27px;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .price span {
            color: #94a3b8;
            font-size: 9px;
            font-weight: 400;
        }

        .price-description {
            color: #94a3b8;
            font-size: 8px;
            min-height: 31px;
            margin-bottom: 18px;
        }

        .price-features {
            list-style: none;
            border-top: 1px solid var(--border);
            padding-top: 14px;
            margin-bottom: 18px;
        }

        .price-features li {
            color: #9aa7c0;
            font-size: 8px;
            margin-bottom: 8px;
        }

        .price-features li::before {
            content: "✓";
            color: var(--green);
            margin-right: 7px;
        }

        .price-button {
            width: 100%;
            min-height: 34px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .1);
            color: #cbd5e1;
            font-size: 9px;
            font-weight: 700;
        }

        .price-card.popular .price-button {
            border-color: var(--accent);
            background: var(--accent);
            color: #ffffff;
        }

        /* =====================================================
           CTA
        ===================================================== */

        .cta {
            padding: 70px 0;
        }

        .cta-box {
            position: relative;
            overflow: hidden;
            padding: 55px 40px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid rgba(245, 158, 11, .18);
            background:
                radial-gradient(circle at 50% 0%, rgba(245, 158, 11, .17), transparent 45%),
                #111827;
        }

        .cta-box h2 {
            font-size: 27px;
            letter-spacing: -1px;
            margin-bottom: 9px;
        }

        .cta-box p {
            color: #94a3b8;
            font-size: 10px;
            margin-bottom: 21px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        footer {
            padding: 28px 0;
            border-top: 1px solid var(--border);
            background: #0b0f19;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-brand {
            color: #9aa8c1;
            font-size: 10px;
        }

        .footer-brand strong {
            color: white;
        }

        .footer-copy {
            color: #94a3b8;
            font-size: 8px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 950px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .hero-copy {
                max-width: 700px;
            }

            .hero-dashboard {
                max-width: 650px;
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .container {
                width: min(100% - 30px, 1180px);
            }

            .navbar {
                height: 62px;
            }

            .nav-links {
                gap: 12px;
            }

            .nav-links a:not(.nav-login) {
                display: none;
            }

            .hero {
                padding: 55px 0;
            }

            .hero h1 {
                font-size: 39px;
            }

            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-dashboard {
                padding: 13px;
            }

            .stats-grid,
            .feature-grid,
            .process-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .footer-inner {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>

<body>

   <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMAHAY - Sistem Manajemen Bengkel & Portal Konsumen</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b0f19] text-gray-200 font-sans selection:bg-amber-500 selection:text-white">

    <!-- =====================================================
         NAVBAR
    ====================================================== -->
    <header class="border-b border-gray-700/80 bg-[#0b0f19]/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2.5 group">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center shadow-lg shadow-amber-950/30 group-hover:bg-amber-600 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.9 6.9a2.12 2.12 0 1 1-3-3l6.9-6.9a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-wider text-white">AMA<span class="text-amber-300">HAY</span></span>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center space-x-8 text-xs font-medium text-gray-400">
                <a href="#fitur" class="hover:text-white transition">Fitur</a>
                <a href="#cara-kerja" class="hover:text-white transition">Cara Kerja</a>
                <a href="#paket" class="hover:text-white transition">Paket</a>
               <a href="{{ route('public.bengkel') }}" class="hover:text-white transition">Cari Bengkel Publik</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-3">
                <a href="/login" class="text-xs font-medium text-gray-300 hover:text-white px-4 py-2 transition">
                    Login
                </a>
                <a href="/register" class="text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl shadow-lg shadow-amber-950/30 transition">
                    Daftar Gratis
                </a>
            </div>

        </div>
    </header>


    <!-- =====================================================
         HERO SECTION
    ====================================================== -->
    <main>
        <section class="max-w-6xl mx-auto px-6 py-16 md:py-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Hero Copy -->
            <div class="space-y-6">
                <div class="inline-block text-[11px] font-semibold tracking-wider text-amber-300 bg-amber-500/10 px-3 py-1 rounded-full uppercase border border-amber-400/30">
                    Sistem Manajemen Bengkel
                </div>

                <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Revolusi Digital untuk <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-400">Manajemen Bengkel Anda</span>
                </h1>

                <p class="text-sm text-gray-400 leading-relaxed max-w-lg">
                    Kelola operasional bengkel dengan lebih mudah. Atur antrean, mekanik, sparepart, transaksi, hingga laporan dalam satu sistem terintegrasi.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="/register" class="bg-amber-500 hover:bg-amber-600 text-white font-medium text-xs px-6 py-3 rounded-xl shadow-lg shadow-amber-950/30 transition">
                        Mulai Sekarang
                    </a>
                    <a href="#fitur" class="bg-[#1f2937] hover:bg-gray-800 text-gray-300 border border-gray-700/80 font-medium text-xs px-6 py-3 rounded-xl transition">
                        Lihat Fitur
                    </a>
                </div>
            </div>

            <!-- Dashboard Preview Mockup -->
            <div class="bg-[#111827] border border-gray-700/80 rounded-3xl p-6 shadow-2xl relative">
                <div class="flex items-center justify-between pb-4 border-b border-gray-700 text-xs mb-4">
                    <span class="font-bold text-white">Pratinjau Dashboard</span>
                    <span class="text-gray-500">Ilustrasi</span>
                </div>

                <!-- Chart Bars Simulation -->
                <div class="h-32 flex items-end justify-between gap-2 px-2 bg-[#1f2937]/50 rounded-xl p-4 mb-4 border border-gray-700/50">
                    <div class="w-full bg-amber-400/40 hover:bg-amber-600 h-[40%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/60 hover:bg-amber-600 h-[65%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/50 hover:bg-amber-600 h-[50%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/80 hover:bg-amber-600 h-[80%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/60 hover:bg-amber-600 h-[60%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/90 hover:bg-amber-600 h-[95%] rounded-t transition"></div>
                    <div class="w-full bg-amber-400/70 hover:bg-amber-600 h-[70%] rounded-t transition"></div>
                </div>

                <!-- Mini Stats Grid -->
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-[#1f2937] p-3 rounded-xl border border-gray-700">
                        <small class="block text-[10px] text-gray-400 mb-1">Kendaraan</small>
                        <strong class="text-sm font-bold text-white">—</strong>
                        <span class="block text-[10px] text-gray-400 mt-0.5">Data contoh</span>
                    </div>
                    <div class="bg-[#1f2937] p-3 rounded-xl border border-gray-700">
                        <small class="block text-[10px] text-gray-400 mb-1">Antrean</small>
                        <strong class="text-sm font-bold text-white">—</strong>
                        <span class="block text-[10px] text-gray-400 mt-0.5">Data contoh</span>
                    </div>
                    <div class="bg-[#1f2937] p-3 rounded-xl border border-gray-700">
                        <small class="block text-[10px] text-gray-400 mb-1">Transaksi</small>
                        <strong class="text-sm font-bold text-white">—</strong>
                        <span class="block text-[10px] text-gray-400 mt-0.5">Data contoh</span>
                    </div>
                </div>
            </div>

        </section>


        <!-- =====================================================
             STATISTICS BANNER
        ====================================================== -->
        <section class="border-y border-gray-700/80 bg-[#111827]/50 py-10">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1">
                    <small class="text-xs text-gray-400">Total Kendaraan</small>
                    <strong class="text-xl font-extrabold text-white block">{{ number_format($statistics['totalVehicles'], 0, ',', '.') }}</strong>
                    <p class="text-[11px] text-gray-400 font-medium">{{ $statistics['description'] }}</p>
                </div>
                <div class="space-y-1">
                    <small class="text-xs text-gray-400">Transaksi Selesai</small>
                    <strong class="text-xl font-extrabold text-white block">{{ number_format($statistics['completedTransactions'], 0, ',', '.') }}</strong>
                    <p class="text-[11px] text-gray-400 font-medium">{{ $statistics['description'] }}</p>
                </div>
                <div class="space-y-1">
                    <small class="text-xs text-gray-400">Sparepart Terjual</small>
                    <strong class="text-xl font-extrabold text-white block">{{ number_format($statistics['sparePartsSold'], 0, ',', '.') }}</strong>
                    <p class="text-[11px] text-gray-400 font-medium">{{ $statistics['description'] }}</p>
                </div>
                <div class="space-y-1">
                    <small class="text-xs text-gray-400">Pendapatan</small>
                    <strong class="text-xl font-extrabold text-white block">Rp {{ number_format($statistics['revenue'], 0, ',', '.') }}</strong>
                    <p class="text-[11px] text-gray-400 font-medium">{{ $statistics['description'] }}</p>
                </div>
            </div>
        </section>


        <!-- =====================================================
             FITUR UTAMA
        ====================================================== -->
        <section class="py-20" id="fitur">
            <div class="max-w-6xl mx-auto px-6">
                
                <div class="text-center max-w-xl mx-auto mb-16">
                    <span class="text-[11px] font-semibold text-amber-300 uppercase tracking-wider bg-amber-500/10 px-3 py-1 rounded-full border border-amber-400/30">Fitur Utama</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mt-3 mb-2">Semua yang Dibutuhkan Bengkel Anda</h2>
                    <p class="text-xs text-gray-400">Kelola berbagai aktivitas bengkel dari satu sistem yang terintegrasi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl space-y-3">
                        <div class="w-10 h-10 bg-amber-400/10 text-amber-300 font-bold rounded-xl flex items-center justify-center border border-amber-400/30">A</div>
                        <h3 class="text-sm font-bold text-white">Antrean Kendaraan</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Kelola antrean kendaraan dan pantau status servis dengan mudah.</p>
                    </div>

                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl space-y-3">
                        <div class="w-10 h-10 bg-amber-400/10 text-amber-300 font-bold rounded-xl flex items-center justify-center border border-amber-400/30">S</div>
                        <h3 class="text-sm font-bold text-white">Manajemen Sparepart</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Pantau stok sparepart, barang masuk, barang keluar, dan persediaan.</p>
                    </div>

                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl space-y-3">
                        <div class="w-10 h-10 bg-amber-400/10 text-amber-300 font-bold rounded-xl flex items-center justify-center border border-amber-400/30">K</div>
                        <h3 class="text-sm font-bold text-white">Kasir & Transaksi</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Proses transaksi pelanggan dengan pencatatan yang lebih teratur.</p>
                    </div>

                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl space-y-3">
                        <div class="w-10 h-10 bg-amber-400/10 text-amber-300 font-bold rounded-xl flex items-center justify-center border border-amber-400/30">M</div>
                        <h3 class="text-sm font-bold text-white">Manajemen Mekanik</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Atur mekanik dan pekerjaan servis secara lebih terstruktur.</p>
                    </div>

                </div>

            </div>
        </section>


        <!-- =====================================================
             CARA KERJA
        ====================================================== -->
        <section class="py-20 bg-[#111827]/30 border-y border-gray-700/80" id="cara-kerja">
            <div class="max-w-6xl mx-auto px-6">
                
                <div class="text-center max-w-xl mx-auto mb-16">
                    <span class="text-[11px] font-semibold text-amber-300 uppercase tracking-wider bg-amber-500/10 px-3 py-1 rounded-full border border-amber-400/30">Cara Kerja</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mt-3 mb-2">Sederhana untuk Digunakan</h2>
                    <p class="text-xs text-gray-400">Semua aktivitas operasional bengkel dikelola melalui beberapa langkah sederhana.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl relative space-y-3">
                        <span class="text-3xl font-black text-amber-300/35 block">01</span>
                        <h3 class="text-sm font-bold text-white">Catat Kendaraan</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Masukkan data pelanggan dan kendaraan yang datang ke bengkel.</p>
                    </div>

                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl relative space-y-3">
                        <span class="text-3xl font-black text-amber-300/35 block">02</span>
                        <h3 class="text-sm font-bold text-white">Proses Servis</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Mekanik mengerjakan servis dan seluruh proses dapat dipantau.</p>
                    </div>

                    <div class="bg-[#111827] border border-gray-700/80 p-6 rounded-2xl relative space-y-3">
                        <span class="text-3xl font-black text-amber-300/35 block">03</span>
                        <h3 class="text-sm font-bold text-white">Selesaikan Transaksi</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">Kasir mencatat transaksi dan pelanggan dapat menyelesaikan pembayaran.</p>
                    </div>

                </div>

            </div>
        </section>


        <!-- =====================================================
             PAKET / PRICING
        ====================================================== -->
        <section class="py-20" id="paket">
            <div class="max-w-6xl mx-auto px-6">
                
                <div class="text-center max-w-xl mx-auto mb-16">
                    <span class="text-[11px] font-semibold text-amber-300 uppercase tracking-wider bg-amber-500/10 px-3 py-1 rounded-full border border-amber-400/30">Pilihan Paket</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mt-3 mb-2">Paket yang Sesuai untuk Bengkel Anda</h2>
                    <p class="text-xs text-gray-400">Pilih paket sesuai kebutuhan operasional bengkel Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                    
                    <!-- Basic -->
                    <div class="bg-[#111827] border border-gray-700/80 p-8 rounded-3xl flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="text-sm font-bold text-white">Basic</div>
                            <div class="text-3xl font-black text-white">Rp 99rb <span class="text-xs font-normal text-gray-400">/bulan</span></div>
                            <p class="text-xs text-gray-400">Untuk bengkel kecil yang baru memulai digitalisasi.</p>
                            <ul class="space-y-2 text-xs text-gray-300 pt-4 border-t border-gray-700">
                                <li>✓ Manajemen kendaraan</li>
                                <li>✓ Manajemen antrean</li>
                                <li>✓ Manajemen sparepart</li>
                                <li>✓ Laporan dasar</li>
                            </ul>
                        </div>
                        <a href="/register" class="mt-8 block text-center bg-[#1f2937] hover:bg-amber-600 hover:text-white text-gray-300 font-medium py-3 rounded-xl text-xs transition border border-gray-700/50">
                            Pilih Paket
                        </a>
                    </div>

                    <!-- Professional (Popular) -->
                    <div class="bg-[#111827] border-2 border-amber-400 p-8 rounded-3xl flex flex-col justify-between relative shadow-xl shadow-amber-950/20">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            Paling Populer
                        </div>
                        <div class="space-y-4 pt-2">
                            <div class="text-sm font-bold text-white">Professional</div>
                            <div class="text-3xl font-black text-white">Rp 249rb <span class="text-xs font-normal text-gray-400">/bulan</span></div>
                            <p class="text-xs text-gray-400">Untuk bengkel yang membutuhkan pengelolaan lebih lengkap.</p>
                            <ul class="space-y-2 text-xs text-gray-300 pt-4 border-t border-gray-700">
                                <li>✓ Semua fitur Basic</li>
                                <li>✓ Kasir & transaksi</li>
                                <li>✓ Manajemen mekanik</li>
                                <li>✓ Laporan lengkap</li>
                                <li>✓ Pengingat servis</li>
                            </ul>
                        </div>
                        <a href="/register" class="mt-8 block text-center bg-amber-500 hover:bg-amber-600 text-white font-medium py-3 rounded-xl text-xs transition shadow-lg shadow-amber-950/30">
                            Pilih Paket
                        </a>
                    </div>

                    <!-- Business -->
                    <div class="bg-[#111827] border border-gray-700/80 p-8 rounded-3xl flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="text-sm font-bold text-white">Business</div>
                            <div class="text-3xl font-black text-white">Rp 499rb <span class="text-xs font-normal text-gray-400">/bulan</span></div>
                            <p class="text-xs text-gray-400">Untuk bengkel dengan kebutuhan operasional yang lebih besar.</p>
                            <ul class="space-y-2 text-xs text-gray-300 pt-4 border-t border-gray-700">
                                <li>✓ Semua fitur Professional</li>
                                <li>✓ Multi pengguna</li>
                                <li>✓ Analitik lanjutan</li>
                                <li>✓ Laporan bisnis</li>
                            </ul>
                        </div>
                        <a href="/register" class="mt-8 block text-center bg-[#1f2937] hover:bg-amber-600 hover:text-white text-gray-300 font-medium py-3 rounded-xl text-xs transition border border-gray-700/50">
                            Pilih Paket
                        </a>
                    </div>

                </div>

            </div>
        </section>


        <!-- =====================================================
             CTA SECTION
        ====================================================== -->
        <section class="max-w-6xl mx-auto px-6 pb-20">
            <div class="bg-gradient-to-r from-amber-900/40 via-[#111827] to-amber-900/40 border border-amber-400/30 p-10 md:p-14 rounded-3xl text-center space-y-4 shadow-2xl">
                <h2 class="text-xl md:text-2xl font-bold text-white">Siap Mengelola Bengkel Lebih Mudah?</h2>
                <p class="text-xs text-gray-400 max-w-md mx-auto">Mulai gunakan AMAHAY untuk membantu mengelola operasional bengkel Anda.</p>
                <div class="pt-2">
                    <a href="/register" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-medium text-xs px-8 py-3 rounded-xl shadow-lg shadow-amber-950/30 transition">
                        Mulai Sekarang
                    </a>
                </div>
            </div>
        </section>
    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->
    <footer class="border-t border-gray-700/80 py-6 px-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between text-xs text-gray-500 gap-3">
            <div>
                © 2026 <strong>AMAHAY</strong> — Sistem Manajemen Bengkel
            </div>
            <div>
                Semua hak dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>