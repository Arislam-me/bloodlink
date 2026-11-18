@props(['title' => null, 'description' => null])
@php
    $user = auth()->user();
    $unread = $user ? $user->unreadNotifications()->count() : 0;
    $navItems = [
        ['route' => 'donors.index', 'label' => 'ডোনার খুঁজুন', 'icon' => 'search', 'match' => 'donors.*'],
        ['route' => 'emergency.index', 'label' => 'জরুরি রক্তের অনুরোধ', 'icon' => 'siren', 'match' => 'emergency.*'],
        ['route' => 'about', 'label' => 'আমাদের সম্পর্কে', 'icon' => 'info', 'match' => 'about'],
    ];
@endphp
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'BloodLink — রক্তের প্রয়োজনে আপনার এলাকার স্বেচ্ছায় রক্তদাতা খুঁজে নিন। সারা বাংলাদেশে।' }}">
    <meta name="theme-color" content="#a51531">
    <title>{{ $title ? $title.' · ' : '' }}BloodLink — রক্তদাতা খুঁজুন</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-lift">মূল অংশে যান</a>

    {{-- ============================== Header ============================== --}}
    <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="BloodLink হোম">
                <span class="grid size-9 place-items-center rounded-xl bg-brand-700 text-white shadow-sm">
                    <x-lucide name="droplet" class="size-5 fill-white/20" />
                </span>
                <span class="text-lg font-extrabold tracking-tight">Blood<span class="text-brand-700">Link</span></span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="প্রধান মেনু">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @class(['nav-link', 'nav-link-active' => request()->routeIs($item['match'])])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('emergency.create') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                    <x-lucide name="plus" class="size-4" /> রক্ত চাই
                </a>

                @auth
                    <a href="{{ route('notifications.index') }}" class="btn-ghost relative !p-2" aria-label="নোটিফিকেশন{{ $unread ? " ($unread টি নতুন)" : '' }}">
                        <x-lucide name="bell" class="size-5" />
                        @if ($unread)
                            <span class="absolute -right-0.5 -top-0.5 grid min-w-5 place-items-center rounded-full bg-brand-700 px-1 text-[10px] font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                        @endif
                    </a>

                    <div x-data="{ menu: false }" class="relative hidden md:block" @keydown.escape.window="menu = false">
                        <button type="button" @click="menu = !menu" class="flex items-center gap-2 rounded-xl py-1 pl-1 pr-2 hover:bg-slate-100" :aria-expanded="menu" aria-haspopup="true">
                            <x-avatar :user="$user" class="size-8 text-xs" />
                            <span class="max-w-32 truncate text-sm font-semibold">{{ Str::before($user->name, ' ') }}</span>
                            <x-lucide name="chevron-down" class="size-4 text-slate-500" />
                        </button>
                        <div x-cloak x-show="menu" x-transition.origin.top.right @click.outside="menu = false"
                             class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-lift">
                            <div class="border-b border-slate-100 px-4 pb-2">
                                <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $user->phone }}</p>
                            </div>
                            @foreach ([['dashboard', 'ড্যাশবোর্ড', 'layout-dashboard'], ['profile.edit', 'প্রোফাইল সম্পাদনা', 'settings'], ['donations.index', 'পূর্বের রক্তদান', 'history'], ['emergency.index', 'আমার রক্তের অনুরোধ', 'siren', ['scope' => 'mine']]] as $m)
                                <a href="{{ route($m[0], $m[3] ?? []) }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <x-lucide :name="$m[2]" class="size-4 text-slate-400" /> {{ $m[1] }}
                                </a>
                            @endforeach
                            @if ($user->isAdmin())
                                <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                    <x-lucide name="shield-check" class="size-4" /> অ্যাডমিন প্যানেল
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                @csrf
                                <button class="flex w-full items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <x-lucide name="log-out" class="size-4 text-slate-400" /> লগ আউট
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm hidden md:inline-flex">লগইন</a>
                    <a href="{{ route('register') }}" class="btn-dark btn-sm hidden md:inline-flex">ডোনার হিসেবে যুক্ত হোন</a>
                @endauth

                <button type="button" class="btn-ghost !p-2 md:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="মেনু খুলুন">
                    <span x-show="!open"><x-lucide name="menu" class="size-6" /></span>
                    <span x-show="open" x-cloak><x-lucide name="x" class="size-6" /></span>
                </button>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <div id="mobile-nav" x-cloak x-show="open" x-transition class="border-t border-slate-200 bg-white md:hidden">
            <nav class="container-page flex flex-col gap-1 py-3" aria-label="মোবাইল মেনু">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @class(['nav-link flex items-center gap-3', 'nav-link-active' => request()->routeIs($item['match'])])>
                        <x-lucide :name="$item['icon']" class="size-4" /> {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('emergency.create') }}" class="btn-primary mt-2"><x-lucide name="plus" /> রক্ত চাই</a>
                <div class="my-2 border-t border-slate-100"></div>
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link flex items-center gap-3"><x-lucide name="layout-dashboard" /> ড্যাশবোর্ড</a>
                    <a href="{{ route('profile.edit') }}" class="nav-link flex items-center gap-3"><x-lucide name="settings" /> প্রোফাইল সম্পাদনা</a>
                    <a href="{{ route('donations.index') }}" class="nav-link flex items-center gap-3"><x-lucide name="history" /> পূর্বের রক্তদান</a>
                    @if ($user->isAdmin())
                        <a href="{{ url('/admin') }}" class="nav-link flex items-center gap-3 text-brand-800"><x-lucide name="shield-check" /> অ্যাডমিন প্যানেল</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="nav-link flex w-full items-center gap-3"><x-lucide name="log-out" /> লগ আউট</button>
                    </form>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="btn-outline">লগইন</a>
                        <a href="{{ route('register') }}" class="btn-dark">ডোনার হোন</a>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Phone verification reminder --}}
    @auth
        @unless ($user->hasVerifiedPhone())
            <div class="border-b border-amber-200 bg-amber-50">
                <div class="container-page flex flex-wrap items-center gap-x-4 gap-y-2 py-2.5 text-sm text-amber-900">
                    <x-lucide name="triangle-alert" class="size-4" />
                    <span class="font-medium">ডোনারের নম্বর দেখতে ও রক্তের অনুরোধ দিতে আগে আপনার মোবাইল নম্বর যাচাই করুন।</span>
                    <a href="{{ route('phone.verify.notice') }}" class="font-semibold underline underline-offset-2">এখনই যাচাই করুন</a>
                </div>
            </div>
        @endunless
    @endauth

    <x-flash />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    {{-- ============================== Footer ============================== --}}
    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="grid size-9 place-items-center rounded-xl bg-brand-700 text-white"><x-lucide name="droplet" class="size-5" /></span>
                    <span class="text-lg font-extrabold">Blood<span class="text-brand-700">Link</span></span>
                </a>
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-600">
                    রোগী ও তাঁর পরিবারকে দেশের ৬৪ জেলার স্বেচ্ছায় রক্তদাতাদের সাথে নিরাপদে যুক্ত করার একটি প্ল্যাটফর্ম।
                </p>
                <p class="mt-4 flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                    <x-lucide name="info" class="mt-0.5 size-4 text-slate-400" />
                    <span>BloodLink কোনো চিকিৎসা সেবা নয়। ডোনারের তথ্য তাঁরা নিজেরাই দেন, "যাচাইকৃত" চিহ্ন না থাকলে তা যাচাই করা নয়। রক্ত দেওয়ার আগে হাসপাতালের পরীক্ষা মেনে চলুন। জরুরি প্রয়োজনে <strong class="text-slate-900">৯৯৯</strong>-এ ফোন করুন।</span>
                </p>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">সেবা</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a class="hover:text-brand-700" href="{{ route('donors.index') }}">রক্তদাতা খুঁজুন</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('emergency.index') }}">জরুরি অনুরোধ</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('emergency.create') }}">রক্তের অনুরোধ দিন</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('register') }}">ডোনার হিসেবে নিবন্ধন</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">নিরাপত্তা ও তথ্য</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a class="hover:text-brand-700" href="{{ route('about') }}">আমাদের সম্পর্কে</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('privacy') }}">গোপনীয়তা নীতি</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('terms') }}">শর্তাবলি</a></li>
                    <li><a class="hover:text-brand-700" href="mailto:{{ config('bloodlink.support_email') }}">সাহায্য চাই</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100">
            <div class="container-page flex flex-col gap-2 py-5 text-xs text-slate-500 sm:flex-row sm:justify-between">
                <p>&copy; {{ date('Y') }} BloodLink. বাংলাদেশের জন্য ভালোবাসা নিয়ে তৈরি।</p>
                <p>ম্যাপের তথ্য &copy; OpenStreetMap contributors</p>
            </div>
        </div>
    </footer>
</body>
</html>
