<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $__defaultOg = \App\Support\Branding::appLogoUrl();
    $__seoTitle = $seoTitle ?? (isset($title) && trim((string) $title) !== '' ? trim($title).' | WPS-SME' : 'WPS-SME');
    $__seoDesc = $seoDescription ?? \App\Support\Branding::defaultSeoDescription();
    $__seoImage = $seoImage ?? $__defaultOg;
    $__seoUrl = $seoCanonicalUrl ?? url()->current();
@endphp
    <title>{{ $__seoTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($__seoDesc), 160) }}">
    <link rel="canonical" href="{{ $__seoUrl }}">
    <link rel="icon" href="{{ $__seoImage }}">
    <link rel="apple-touch-icon" href="{{ $__seoImage }}">
    <meta property="og:title" content="{{ $__seoTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($__seoDesc), 200) }}">
    <meta property="og:image" content="{{ $__seoImage }}">
    <meta property="og:url" content="{{ $__seoUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="WPS-SME">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $__seoTitle }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($__seoDesc), 200) }}">
    <meta name="twitter:image" content="{{ $__seoImage }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --sea: #0ea5a8;
            --sea-dark: #0b7f82;
            --sea-deep: #063a3b;
            --sea-muted: rgba(14,165,168,.08);
            --sea-border: rgba(14,165,168,.22);
            --card-radius: 1rem;
            --card-radius-lg: 1.25rem;
            --card-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 16px rgba(0,0,0,.04);
            --card-shadow-elevated: 0 4px 6px rgba(0,0,0,.04), 0 12px 32px rgba(0,0,0,.08);
            --nav-height: 3.5rem;
            --page-bg: linear-gradient(180deg, #fafbfc 0%, #f1f5f9 100%);
        }
        body { font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; }
        .glass{
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(10px);
        }
        .glass-strong{
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px);
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            border-radius:.75rem;
            padding:.65rem 1.05rem;
            font-weight:600;
            line-height:1.1;
            transition: transform .08s ease, background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .btn:active{ transform: translateY(1px); }
        .btn-primary{
            background: var(--sea);
            color:#fff;
            box-shadow: 0 10px 24px rgba(14,165,168,.18);
        }
        .btn-primary:hover{ background: var(--sea-dark); }
        .btn-soft{
            background: rgba(14,165,168,.10);
            border: 1px solid rgba(14,165,168,.22);
            color: #0f172a;
        }
        .btn-soft:hover{
            background: rgba(14,165,168,.16);
            border-color: rgba(14,165,168,.30);
        }
        .link-sea{ color: var(--sea); }
        .link-sea:hover{ color: var(--sea-dark); }
        .ring-sea:focus{ outline: none; box-shadow: 0 0 0 3px rgba(14,165,168,.25); }
        .navlink{
            border-radius: .75rem;
            padding: .45rem .65rem;
            color: #475569; /* slate-600 */
            transition: background-color .15s ease, color .15s ease;
        }
        .navlink:hover{
            color: var(--sea-dark);
            background: rgba(14,165,168,.10);
        }

        /* Premium cards & layout */
        .card{
            background: #fff;
            border-radius: var(--card-radius);
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        .card-elevated{
            background: #fff;
            border-radius: var(--card-radius-lg);
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow-elevated);
            overflow: hidden;
        }
        .stat-card{
            background: #fff;
            border-radius: var(--card-radius);
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            padding: 1.25rem 1.5rem;
            transition: box-shadow .2s ease, border-color .2s ease;
        }
        .stat-card:hover{
            box-shadow: 0 4px 12px rgba(0,0,0,.06);
            border-color: #cbd5e1;
        }
        .stat-card .stat-label{ color: #64748b; font-size: .8125rem; font-weight: 500; }
        .stat-card .stat-value{ font-size: 1.5rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
        .page-title{ font-size: 1.625rem; font-weight: 700; color: #0f172a; letter-spacing: -0.03em; margin-bottom: .75rem; line-height: 1.2; }
        .page-lead{ font-size: .9375rem; color: #64748b; line-height: 1.6; max-width: 42rem; margin-bottom: 1.75rem; }
        .section-title{ font-size: 1.0625rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; margin-bottom: .35rem; }
        .section-sub{ font-size: .8125rem; color: #64748b; line-height: 1.5; margin-bottom: 1.25rem; }

        /* —— Modern form system (optional wrappers; global inputs upgraded below) —— */
        .form-stack{ display: flex; flex-direction: column; gap: 1.35rem; }
        .form-field{ display: flex; flex-direction: column; gap: .45rem; }
        .form-label{
            font-size: .8125rem;
            font-weight: 600;
            color: #334155;
            letter-spacing: .02em;
        }
        .form-label .req{ color: #e11d48; font-weight: 700; }
        .form-hint{ font-size: .75rem; color: #64748b; line-height: 1.45; margin-top: .1rem; }

        .form-shell{
            background: #fff;
            border-radius: var(--card-radius-lg);
            border: 1px solid #e8edf2;
            box-shadow:
                0 0 0 1px rgba(255,255,255,.8) inset,
                0 2px 6px rgba(15,23,42,.03),
                0 16px 48px rgba(15,23,42,.07);
            padding: 1.35rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px){
            .form-shell{ padding: 1.65rem 1.85rem; }
        }
        .form-shell-header{
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .form-shell-icon{
            width: 2.5rem;
            height: 2.5rem;
            border-radius: .8rem;
            background: linear-gradient(155deg, rgba(14,165,168,.14) 0%, rgba(14,165,168,.05) 100%);
            border: 1px solid rgba(14,165,168,.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.15rem;
            line-height: 1;
            color: var(--sea-dark);
        }
        .form-shell-icon svg{
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
        }
        .form-shell-header .section-title{ margin-bottom: .2rem !important; }
        .form-shell-header .section-sub{ margin-bottom: 0 !important; }

        .surface-inset{
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: .4rem;
            box-shadow: inset 0 1px 2px rgba(15,23,42,.04);
        }
        .list-row{
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1rem;
            background: #fff;
            border-radius: .75rem;
            border: 1px solid transparent;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .list-row + .list-row{ margin-top: .4rem; }
        .list-row:hover{
            border-color: rgba(14,165,168,.2);
            box-shadow: 0 2px 8px rgba(14,165,168,.06);
        }
        .list-row-title{ font-weight: 600; color: #0f172a; font-size: .9375rem; }
        .btn-ghost{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .85rem;
            border-radius: .65rem;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--sea-dark);
            background: rgba(14,165,168,.1);
            border: 1px solid rgba(14,165,168,.15);
            transition: background .15s ease, border-color .15s ease, transform .08s ease;
        }
        .btn-ghost:hover{
            background: rgba(14,165,168,.16);
            border-color: rgba(14,165,168,.28);
            color: #063a3b;
        }
        .btn-ghost:active{ transform: translateY(1px); }
        .btn-secondary{
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover{ background: #e2e8f0; border-color: #cbd5e1; color: #0f172a; }
        .table-premium thead th{
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .875rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-premium tbody td{ padding: .875rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .table-premium tbody tr:hover{ background: #fafbfc; }
        .badge{ display: inline-flex; align-items: center; padding: .25rem .5rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
        .badge-neutral{ background: #f1f5f9; color: #475569; }
        .badge-success{ background: rgba(14,165,168,.12); color: var(--sea-dark); }
        .badge-warning{ background: #fef3c7; color: #b45309; }
        .badge-danger{ background: #fee2e2; color: #b91c1c; }

        /* Global polish layer so legacy Tailwind utility classes follow brand colors */
        .bg-indigo-600{ background-color: var(--sea) !important; }
        .hover\:bg-indigo-700:hover{ background-color: var(--sea-dark) !important; }
        .text-indigo-600{ color: var(--sea-dark) !important; }
        .hover\:text-indigo-500:hover,
        .hover\:text-indigo-700:hover{ color: var(--sea) !important; }
        .focus\:ring-indigo-500:focus{ --tw-ring-color: rgba(14,165,168,.35) !important; }

        /* Unified form controls — modern defaults app-wide */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="search"],
        input[type="url"],
        input[type="datetime-local"],
        input[type="date"],
        input[type="time"],
        select,
        textarea{
            border: 1px solid #d8dee6 !important;
            border-radius: .8rem !important;
            padding: .7rem .95rem !important;
            min-height: 2.75rem;
            background: #fafbfc !important;
            color: #0f172a !important;
            font-size: .9375rem !important;
            transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
            box-shadow: inset 0 1px 2px rgba(15,23,42,.035) !important;
        }
        textarea{
            min-height: 6rem;
            resize: vertical;
        }
        select{
            cursor: pointer;
            background-color: #fafbfc !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .65rem center;
            background-size: 1rem;
            padding-right: 2.25rem !important;
            appearance: none;
        }
        input[type="text"]:hover,
        input[type="email"]:hover,
        input[type="password"]:hover,
        input[type="number"]:hover,
        input[type="tel"]:hover,
        input[type="search"]:hover,
        input[type="url"]:hover,
        input[type="datetime-local"]:hover,
        input[type="date"]:hover,
        input[type="time"]:hover,
        select:hover,
        textarea:hover{
            border-color: #c5cdd8 !important;
            background: #fff !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        input[type="search"]:focus,
        input[type="url"]:focus,
        input[type="datetime-local"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        select:focus,
        textarea:focus{
            outline: none;
            border-color: rgba(14,165,168,.65) !important;
            background: #fff !important;
            box-shadow:
                inset 0 1px 2px rgba(15,23,42,.03),
                0 0 0 3px rgba(14,165,168,.18) !important;
        }
        input::placeholder,
        textarea::placeholder{
            color: #94a3b8;
        }
        input[type="file"]{
            width: 100%;
            font-size: .8125rem !important;
            padding: .65rem .85rem !important;
            min-height: 2.75rem;
            border: 1px dashed #c5cdd8 !important;
            border-radius: .8rem !important;
            background: linear-gradient(180deg, #fafbfc, #f8fafc) !important;
            cursor: pointer;
        }
        input[type="file"]:hover{
            border-color: rgba(14,165,168,.35) !important;
            background: #fff !important;
        }

        /* Checkbox/radio accents */
        input[type="checkbox"],
        input[type="radio"]{
            accent-color: var(--sea);
        }

        /* Buttons: catches existing utility-only buttons too */
        button,
        input[type="submit"],
        input[type="button"],
        a.rounded,
        a.rounded-md{
            transition: transform .08s ease, box-shadow .15s ease, background-color .15s ease, color .15s ease;
        }
        button:active,
        input[type="submit"]:active,
        input[type="button"]:active{
            transform: translateY(1px);
        }

        /* Card/table consistency */
        .glass table{
            border-color: #e2e8f0;
        }
        table{
            border-collapse: separate;
            border-spacing: 0;
        }
        table thead th{
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
        }

        [x-cloak]{ display: none !important; }
        .nav-dd-panel{
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.35rem;
            min-width: 12rem;
            padding: 0.35rem 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow-elevated);
            z-index: 50;
            max-height: min(70vh, 22rem);
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .nav-dd-item{
            display: block;
            width: 100%;
            text-align: left;
            padding: 0.5rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            transition: background .12s ease, color .12s ease;
        }
        .nav-dd-item:hover{
            background: rgba(14,165,168,.08);
            color: var(--sea-dark);
        }
        .nav-dd-trigger{
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border-radius: .75rem;
            padding: .45rem .65rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            transition: background-color .15s ease, color .15s ease;
        }
        .nav-dd-trigger:hover{ background: rgba(14,165,168,.10); color: var(--sea-dark); }
        .nav-dd-trigger svg{ opacity: .55; transition: transform .2s ease, opacity .15s ease; }
        .nav-dd-trigger[aria-expanded="true"] svg{ transform: rotate(180deg); opacity: .85; }
        .nav-dd-trigger:focus-visible,
        .navlink:focus-visible,
        .nav-dd-item:focus-visible{
            outline: 2px solid rgba(14,165,168,.45);
            outline-offset: 2px;
        }
        .nav-brand-mark{
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.65rem;
            background: linear-gradient(145deg, var(--sea) 0%, var(--sea-deep) 100%);
            box-shadow: 0 4px 14px rgba(14,165,168,.35);
            flex-shrink: 0;
        }
        #nav-mobile-panel{
            max-height: min(75vh, calc(100dvh - var(--nav-height) - 1rem));
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased" style="background: var(--page-bg);">
    <nav class="glass border-b border-slate-200/80 sticky top-0 z-40" style="box-shadow: 0 1px 0 rgba(0,0,0,.04);" role="navigation" aria-label="Primary" x-data="{ mobileOpen: false, menu: null }" x-on:keydown.escape.window="mobileOpen = false; menu = null">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center gap-3" style="min-height: var(--nav-height);">
                <div class="flex items-center gap-3 min-w-0">
                    @if($navShopStore ?? null)
                        <a href="{{ route('shop.show', $navShopStore) }}" class="flex items-center gap-2.5 shrink-0 min-w-0 group" aria-label="{{ $navShopStore->name }}">
                            @if($navShopStore->logoDisplayUrl())
                                <img src="{{ $navShopStore->logoDisplayUrl() }}" alt="{{ $navShopStore->name }}" width="36" height="36" class="h-9 w-9 rounded-lg object-cover shadow-sm ring-1 ring-slate-200/80 shrink-0" loading="eager">
                            @else
                                <img src="{{ \App\Support\Branding::appLogoUrl() }}" alt="WPS-SME" width="36" height="36" class="h-9 w-9 rounded-lg object-contain shadow-sm ring-1 ring-slate-200/80 shrink-0 bg-white" loading="eager">
                            @endif
                            <span class="font-bold text-base sm:text-lg tracking-tight text-slate-900 leading-tight transition-colors group-hover:text-[var(--sea-dark)] truncate">{{ $navShopStore->name }}</span>
                        </a>
                    @else
                        <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0 group" aria-label="WPS-SME home">
                            <img src="{{ \App\Support\Branding::appLogoUrl() }}" alt="WPS-SME" width="36" height="36" class="h-9 w-9 rounded-lg object-contain shadow-sm ring-1 ring-slate-200/80 shrink-0 bg-white" loading="eager">
                            <span class="font-bold text-base sm:text-lg tracking-tight text-slate-900 leading-tight transition-colors group-hover:text-[var(--sea-dark)]">WPS-SME</span>
                        </a>
                    @endif
                    @auth
                        <span class="hidden sm:inline text-sm text-slate-500 truncate max-w-[10rem] md:max-w-xs border-l border-slate-200/90 pl-3">{{ auth()->user()->name }}</span>
                    @endauth
                </div>

                @auth
                    @php
                        $navUser = auth()->user();
                        $navIsSupplier = $navUser->hasRole('Supplier');
                        $navIsStaff = $navUser->hasRole('Supplier') || $navUser->hasRole('Admin');
                        $navStoreOwnerTools = $navUser->ownsAnyStore() && ! $navIsSupplier;
                    @endphp
                    <button type="button" class="lg:hidden inline-flex items-center gap-2 rounded-xl border border-slate-200/90 bg-white/90 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm shrink-0 hover:bg-slate-50/90 transition-colors" id="nav-mobile-toggle" x-on:click="mobileOpen = !mobileOpen" x-bind:aria-expanded="mobileOpen" aria-controls="nav-mobile-panel">
                        <span x-text="mobileOpen ? 'Close' : 'Menu'">Menu</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" x-bind:class="mobileOpen ? 'rotate-90' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="hidden lg:flex items-center gap-0.5 flex-wrap justify-end flex-1 min-w-0" x-on:click.outside="menu = null">
                        @if($navIsStaff)
                            <a href="{{ route('admin.dashboard') }}" class="navlink text-sm font-semibold text-slate-800" x-on:click="menu = null">{{ $navIsSupplier ? 'Super Admin' : 'Admin' }} overview</a>

                            <div class="relative">
                                <button type="button" class="nav-dd-trigger text-sm" id="nav-btn-ops" x-on:click.stop="menu = menu === 'ops' ? null : 'ops'" x-bind:aria-expanded="menu === 'ops'" aria-haspopup="menu" aria-controls="nav-panel-ops">
                                    Operations
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="nav-panel-ops" class="nav-dd-panel" role="menu" x-show="menu === 'ops'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                    <a href="{{ route('admin.orders.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Orders</a>
                                    <a href="{{ route('admin.bundles.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Bundles</a>
                                    <a href="{{ route('admin.notifications.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Notifications</a>
                                    <a href="{{ route('admin.settings.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Settings</a>
                                    @can('manage_withdrawals')
                                        <a href="{{ route('admin.withdrawals.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Withdrawal approvals</a>
                                    @endcan
                                </div>
                            </div>

                            @if($navIsSupplier)
                                <div class="relative">
                                    <button type="button" class="nav-dd-trigger text-sm" x-on:click.stop="menu = menu === 'users' ? null : 'users'" x-bind:aria-expanded="menu === 'users'" aria-haspopup="menu" aria-controls="nav-panel-users">
                                        Users &amp; stores
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="nav-panel-users" class="nav-dd-panel" role="menu" x-show="menu === 'users'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                        <a href="{{ route('admin.stores.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Stores for users</a>
                                        <a href="{{ route('admin.wallets.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Wallets</a>
                                        <a href="{{ route('admin.users.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Users</a>
                                        <a href="{{ route('admin.passwordResets.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Password resets</a>
                                        <a href="{{ route('admin.roles.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Roles</a>
                                    </div>
                                </div>
                            @endif

                            @if($navStoreOwnerTools)
                                <div class="relative">
                                    <button type="button" class="nav-dd-trigger text-sm" x-on:click.stop="menu = menu === 'store' ? null : 'store'" x-bind:aria-expanded="menu === 'store'" aria-haspopup="menu" aria-controls="nav-panel-store">
                                        My store
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="nav-panel-store" class="nav-dd-panel" role="menu" x-show="menu === 'store'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                        <a href="{{ route('stores.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">My stores</a>
                                        <a href="{{ route('store-owner.profile') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Payout profile (MoMo)</a>
                                        <a href="{{ route('withdrawals.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">My withdrawals</a>
                                    </div>
                                </div>
                            @elseif(! $navIsSupplier)
                                <a href="{{ route('stores.index') }}" class="navlink text-sm" x-on:click="menu = null">My stores</a>
                            @endif
                        @else
                            <a href="{{ route('dashboard') }}" class="navlink text-sm font-semibold text-slate-800" x-on:click="menu = null">Dashboard</a>
                            <a href="{{ route('stores.index') }}" class="navlink text-sm" x-on:click="menu = null">My stores</a>
                            @if($navStoreOwnerTools)
                                <a href="{{ route('store-owner.profile') }}" class="navlink text-sm" x-on:click="menu = null">Store owner profile</a>
                                <a href="{{ route('withdrawals.index') }}" class="navlink text-sm" x-on:click="menu = null">Withdrawals</a>
                            @endif
                            @can('set_sub_agent_prices')
                                <a href="{{ route('sub-agent.pricing.index') }}" class="navlink text-sm" x-on:click="menu = null">Store prices</a>
                            @endcan
                            <div class="relative">
                                <button type="button" class="nav-dd-trigger text-sm" x-on:click.stop="menu = menu === 'orders' ? null : 'orders'" x-bind:aria-expanded="menu === 'orders'" aria-haspopup="menu" aria-controls="nav-panel-orders">
                                    Orders
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="nav-panel-orders" class="nav-dd-panel" role="menu" x-show="menu === 'orders'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                    <a href="{{ route('orders.create') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Place order</a>
                                    <a href="{{ route('orders.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">My orders</a>
                                </div>
                            </div>
                            <a href="{{ route('wallet.show') }}" class="navlink text-sm" x-on:click="menu = null">Wallet</a>
                            <div class="relative">
                                <button type="button" class="nav-dd-trigger text-sm" x-on:click.stop="menu = menu === 'plans' ? null : 'plans'" x-bind:aria-expanded="menu === 'plans'" aria-haspopup="menu" aria-controls="nav-panel-plans">
                                    Plans
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="nav-panel-plans" class="nav-dd-panel" role="menu" x-show="menu === 'plans'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                    <a href="{{ route('plans.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">My plans</a>
                                    <a href="{{ route('subscriptions.index') }}" class="nav-dd-item" role="menuitem" x-on:click="menu = null">Subscriptions</a>
                                </div>
                            </div>
                        @endif

                        @can('access_api')
                            <a href="{{ route('api-tokens.index') }}" class="navlink text-sm" x-on:click="menu = null">API tokens</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}" class="inline ml-1" x-on:click="menu = null">
                            @csrf
                            <button type="submit" class="navlink text-sm font-semibold text-slate-700">Logout</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="navlink text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary text-sm py-2 px-3">Register</a>
                    </div>
                @endauth
            </div>

            @auth
            <div id="nav-mobile-panel" class="lg:hidden border-t border-slate-200/80 bg-white/60 py-2 pb-3 space-y-0.5 shadow-[inset_0_1px_0_rgba(255,255,255,.6)]" x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="region" aria-label="Mobile menu">
                @if($navIsStaff)
                    <a href="{{ route('admin.dashboard') }}" class="block nav-dd-item rounded-lg">{{ $navIsSupplier ? 'Super Admin' : 'Admin' }} overview</a>
                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest px-3 pt-3 pb-1">Operations</p>
                    <a href="{{ route('admin.orders.index') }}" class="block nav-dd-item rounded-lg">Orders</a>
                    <a href="{{ route('admin.bundles.index') }}" class="block nav-dd-item rounded-lg">Bundles</a>
                    <a href="{{ route('admin.notifications.index') }}" class="block nav-dd-item rounded-lg">Notifications</a>
                    <a href="{{ route('admin.settings.index') }}" class="block nav-dd-item rounded-lg">Settings</a>
                    @can('manage_withdrawals')
                        <a href="{{ route('admin.withdrawals.index') }}" class="block nav-dd-item rounded-lg">Withdrawal approvals</a>
                    @endcan
                    @if($navIsSupplier)
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest px-3 pt-3 pb-1">Users &amp; stores</p>
                        <a href="{{ route('admin.stores.index') }}" class="block nav-dd-item rounded-lg">Stores for users</a>
                        <a href="{{ route('admin.wallets.index') }}" class="block nav-dd-item rounded-lg">Wallets</a>
                        <a href="{{ route('admin.users.index') }}" class="block nav-dd-item rounded-lg">Users</a>
                        <a href="{{ route('admin.passwordResets.index') }}" class="block nav-dd-item rounded-lg">Password resets</a>
                        <a href="{{ route('admin.roles.index') }}" class="block nav-dd-item rounded-lg">Roles</a>
                    @endif
                    @if($navStoreOwnerTools)
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest px-3 pt-3 pb-1">My store</p>
                        <a href="{{ route('stores.index') }}" class="block nav-dd-item rounded-lg">My stores</a>
                        <a href="{{ route('store-owner.profile') }}" class="block nav-dd-item rounded-lg">Payout profile (MoMo)</a>
                        <a href="{{ route('withdrawals.index') }}" class="block nav-dd-item rounded-lg">My withdrawals</a>
                    @elseif(! $navIsSupplier)
                        <a href="{{ route('stores.index') }}" class="block nav-dd-item rounded-lg">My stores</a>
                    @endif
                @else
                    <a href="{{ route('dashboard') }}" class="block nav-dd-item rounded-lg font-medium">Dashboard</a>
                    <a href="{{ route('stores.index') }}" class="block nav-dd-item rounded-lg">My stores</a>
                    @if($navStoreOwnerTools)
                        <a href="{{ route('store-owner.profile') }}" class="block nav-dd-item rounded-lg">Store owner profile</a>
                        <a href="{{ route('withdrawals.index') }}" class="block nav-dd-item rounded-lg">Withdrawals</a>
                    @endif
                    @can('set_sub_agent_prices')
                        <a href="{{ route('sub-agent.pricing.index') }}" class="block nav-dd-item rounded-lg">Store prices</a>
                    @endcan
                    <a href="{{ route('orders.create') }}" class="block nav-dd-item rounded-lg">Place order</a>
                    <a href="{{ route('orders.index') }}" class="block nav-dd-item rounded-lg">My orders</a>
                    <a href="{{ route('wallet.show') }}" class="block nav-dd-item rounded-lg">Wallet</a>
                    <a href="{{ route('plans.index') }}" class="block nav-dd-item rounded-lg">My plans</a>
                    <a href="{{ route('subscriptions.index') }}" class="block nav-dd-item rounded-lg">Subscriptions</a>
                @endif
                @can('access_api')
                    <a href="{{ route('api-tokens.index') }}" class="block nav-dd-item rounded-lg">API tokens</a>
                @endcan
                <div class="border-t border-slate-200/80 mt-2 pt-2 px-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left nav-dd-item rounded-lg font-semibold text-slate-800">Logout</button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        @if(session('success'))
            <div class="mb-6 card-elevated p-4 flex items-center gap-3">
                <span class="badge badge-success">Success</span>
                <span class="text-slate-700">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 card-elevated p-4 flex items-center gap-3">
                <span class="badge badge-success">Info</span>
                <span class="text-slate-700">{{ session('info') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 card p-4 border-amber-200 bg-amber-50/50">
                <ul class="list-disc list-inside text-slate-700 text-sm space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- App confirm modal (replaces browser confirm()) -->
    <div id="app-confirm-backdrop" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50"></div>
    <div id="app-confirm-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
        <div class="w-full max-w-md card-elevated p-6 text-slate-900" style="border-radius: var(--card-radius-lg);">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900" id="app-confirm-title">Please confirm</p>
                    <p class="text-sm text-slate-700 mt-1" id="app-confirm-message"></p>
                </div>
                <button type="button" class="text-slate-500 hover:text-slate-700" id="app-confirm-close">✕</button>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn btn-soft" id="app-confirm-cancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="app-confirm-ok">OK</button>
            </div>
        </div>
    </div>

    @auth
    <div id="notification-popup" x-data="{
        items: [],
        show: false,
        async fetchNotifications() {
            try {
                const r = await fetch('{{ route('notifications.index') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                const data = await r.json();
                this.items = data.data || [];
                if (this.items.length) this.show = true;
            } catch (e) {}
        },
        async markRead(id) {
            try {
                await fetch('{{ url('/') }}/notifications/' + id + '/read', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', body: JSON.stringify({}) });
                this.items = this.items.filter(n => n.id !== id);
                if (!this.items.length) this.show = false;
            } catch (e) {}
        }
    }" x-init="fetchNotifications(); setInterval(fetchNotifications, 60000)">
        <template x-if="show && items.length">
            <div class="fixed bottom-4 right-4 z-50 max-w-sm card-elevated p-4 space-y-2 text-slate-900">
                <p class="font-medium text-slate-900">Notifications</p>
                <template x-for="n in items" :key="n.id">
                    <div class="rounded-xl bg-[rgba(14,165,168,.08)] border border-[rgba(14,165,168,.18)] p-3">
                        <p class="font-medium text-sm" x-text="n.title"></p>
                        <p class="text-xs text-slate-600 mt-1" x-text="n.message"></p>
                        <button type="button" @click="markRead(n.id)" class="mt-2 text-xs link-sea">Dismiss</button>
                    </div>
                </template>
            </div>
        </template>
    </div>
    @endauth

    <script>
    (() => {
        const backdrop = document.getElementById('app-confirm-backdrop');
        const modal = document.getElementById('app-confirm-modal');
        const msgEl = document.getElementById('app-confirm-message');
        const okBtn = document.getElementById('app-confirm-ok');
        const cancelBtn = document.getElementById('app-confirm-cancel');
        const closeBtn = document.getElementById('app-confirm-close');

        let onOk = null;

        const open = (message, okCallback) => {
            if (msgEl) msgEl.textContent = message || 'Are you sure?';
            onOk = typeof okCallback === 'function' ? okCallback : null;
            backdrop?.classList.remove('hidden');
            modal?.classList.remove('hidden');
            modal?.classList.add('flex');
            okBtn?.focus();
        };
        const close = () => {
            backdrop?.classList.add('hidden');
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
            onOk = null;
        };

        okBtn?.addEventListener('click', () => {
            const cb = onOk;
            close();
            cb?.();
        });
        cancelBtn?.addEventListener('click', close);
        closeBtn?.addEventListener('click', close);
        backdrop?.addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });

        // Intercept forms that request confirmation via data-confirm.
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            const message = form.getAttribute('data-confirm');
            if (!message) return;
            if (form.dataset.confirmed === '1') return;
            e.preventDefault();
            open(message, () => {
                form.dataset.confirmed = '1';
                form.requestSubmit ? form.requestSubmit() : form.submit();
            });
        }, true);
    })();
    </script>
</body>
</html>
