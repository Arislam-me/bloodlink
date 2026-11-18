{{-- Self-contained error layout: works even when the session/auth middleware did not run. --}}
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · BloodLink</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center px-4">
    <main class="w-full max-w-md text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-xl bg-brand-700 text-white"><x-lucide name="droplet" class="size-5" /></span>
            <span class="text-xl font-extrabold">Blood<span class="text-brand-700">Link</span></span>
        </a>
        <div class="card card-body mt-8">
            <p class="text-5xl font-extrabold text-brand-700">@yield('code')</p>
            <h1 class="mt-3 text-xl font-bold">@yield('title')</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">@yield('message')</p>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ url('/') }}" class="btn-primary"><x-lucide name="house" /> হোম পেজে যান</a>
                <button type="button" onclick="history.back()" class="btn-outline"><x-lucide name="chevron-left" /> আগের পেজ</button>
            </div>
        </div>
    </main>
</body>
</html>
