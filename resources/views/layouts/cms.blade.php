<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Shastra CMS')</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/logo/Logo_not_text.png') }}">
        <link rel="shortcut icon" href="{{ asset('assets/logo/Logo_not_text.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo/Logo_not_text.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        {{-- CMS shell header --}}
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
                        Shastra CMS
                    </a>
                    <p class="text-sm text-slate-500">Marketing content administration</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                    >
                        Log out
                    </button>
                </form>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl gap-6 px-6 py-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
            {{-- CMS sidebar navigation --}}
            <aside class="rounded-3xl bg-slate-900 px-5 py-6 text-white shadow-sm">
                <nav class="space-y-2" aria-label="CMS navigation">
                    <a
                        href="{{ route('dashboard') }}"
                        @class([
                            'block rounded-2xl px-4 py-3 text-sm transition',
                            'bg-white/12 text-white' => request()->routeIs('dashboard'),
                            'text-white/72 hover:bg-white/8 hover:text-white' => ! request()->routeIs('dashboard'),
                        ])
                    >
                        Dashboard
                    </a>

                    <a
                        href="{{ route('settings.edit') }}"
                        @class([
                            'block rounded-2xl px-4 py-3 text-sm transition',
                            'bg-white/12 text-white' => request()->routeIs('settings.*'),
                            'text-white/72 hover:bg-white/8 hover:text-white' => ! request()->routeIs('settings.*'),
                        ])
                    >
                        Global Settings
                    </a>

                    @foreach (config('editable_pages', []) as $slug => $page)
                        <a
                            href="{{ route('pages.edit', ['page' => $slug]) }}"
                            @class([
                                'block rounded-2xl px-4 py-3 text-sm transition',
                                'bg-white/12 text-white' => request()->routeIs('pages.*') && request()->route('page') === $slug,
                                'text-white/72 hover:bg-white/8 hover:text-white' => ! (request()->routeIs('pages.*') && request()->route('page') === $slug),
                            ])
                        >
                            {{ $page['label'] }}
                        </a>
                    @endforeach

                    <a
                        href="{{ route('contact-submissions.index') }}"
                        @class([
                            'block rounded-2xl px-4 py-3 text-sm transition',
                            'bg-white/12 text-white' => request()->routeIs('contact-submissions.*'),
                            'text-white/72 hover:bg-white/8 hover:text-white' => ! request()->routeIs('contact-submissions.*'),
                        ])
                    >
                        Contact Submissions
                    </a>
                </nav>
            </aside>

            {{-- CMS main content --}}
            <main class="space-y-6">
                @if (session('cms_status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('cms_status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
