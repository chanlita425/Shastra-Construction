<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Shastra')</title>
        <meta name="description" content="@yield('meta_description', 'Prepared Shastra Laravel website scaffold.')">
        <link rel="icon" type="image/png" href="{{ asset('assets/logo/Logo_not_text.png') }}">
        <link rel="shortcut icon" href="{{ asset('assets/logo/Logo_not_text.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo/Logo_not_text.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Baskervville:ital@0;1&family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Kantumruy+Pro:wght@100..700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body id="top" class="min-h-screen overflow-x-hidden bg-[var(--color-canvas)] font-sans text-[var(--color-ink)] antialiased">
        {{-- Header --}}
        <x-site-header :brand="$site['brand']" :navigation="$site['navigation']" :contact="$site['contact']" />

        {{-- Content --}}
        <main>
            @yield('content')
        </main>

        {{-- Footer --}}
        <x-site-footer
            :brand="$site['brand']"
            :navigation="$site['navigation']"
            :contact="$site['contact']"
            :footer="$site['footer']"
        />
    </body>
</html>
