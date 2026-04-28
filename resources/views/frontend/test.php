{{-- Home Page --}}
@extends('layouts.marketing')

@section('title', 'Shastra')
@section('meta_description', 'Shastra is an architectural design and engineering firm that delivers innovative solutions
    for a sustainable future. With a focus on cutting-edge technology and sustainable practices, Shastra creates spaces that
    inspire and connect people while minimizing environmental impact.')

@section('content')
    @php
        // Hero data
       $heroVideos = collect($banners ?? [])
        ->filter(fn($banner) => ($banner->media['type'] ?? null) === 'video')
        ->map(fn($banner) => [
            'label' => $banner->name,
            'src' => asset($banner->media['path']),
        ])
        ->values()
        ->all();
        $hasMultipleHeroVideos = count($heroVideos) > 1;
        $heroTitleAccent = $site['hero']['titleAccent'] ?? null;
        $firstVideoSrc = $heroVideos[0]['src'] ?? '';
        $stats = collect($site['stats'] ?? [])
            ->map(function (array $item): array {
                $numericValue = preg_replace('/\D+/', '', $item['value']);
                $digits = max(strlen($numericValue), 1);
                $suffix = preg_replace('/\d+/', '', $item['value']);

                return [
                    'label' => $item['label'],
                    'value' => $item['value'],
                    'target' => (int) $numericValue,
                    'digits' => $digits,
                    'suffix' => $suffix,
                    'display' => str_repeat('0', $digits) . $suffix,
                ];
            })
            ->values()
            ->all();
        // Use DB Why items for the services cards if available, fallback to static config
        $sharedServicesCards = ! empty($whyCards) ? $whyCards : data_get($site, 'pages.services.cards', []);
        $servicesPage = data_get($site, 'pages.services', []);
    @endphp

    {{-- Hero --}}
    <section id="overview" x-data="{
        // Hero state
        activeVideo: 0,
        videos: @js($heroVideos),
        stats: @js($stats),
        statsVisible: false,
        statsAnimationStarted: false,
        playActive() {
            const video = this.$refs.heroVideo;

            if (!video || !this.videos.length) {
                return;
            }

            // Force muted via JS — some mobile browsers strip the HTML attribute
            video.muted = true;
            video.src = this.videos[this.activeVideo].src;
            video.load();

            const playPromise = video.play();

            if (playPromise !== undefined) {
                playPromise.catch(() => {});
            }
        },
        initVideo() {
            const video = this.$refs.heroVideo;

            if (!video || !this.videos.length) {
                return;
            }

            // Ensure muted is set programmatically for mobile browsers
            video.muted = true;

            // Attempt to play; the static src attribute already set on the element
            // handles the initial load so autoplay fires before JS kicks in
            const playPromise = video.play();

            if (playPromise !== undefined) {
                playPromise.catch(() => {
                    // Some mobile browsers require a user gesture; silently ignore
                });
            }
        },
        formatStat(item, value) {
            return `${String(value).padStart(item.digits, '0')}${item.suffix}`;
        },
        initStatsAnimation() {
            // Start once when stats enter view
            const statsSection = this.$refs.statsSection;

            if (!statsSection || !this.stats.length) {
                return;
            }

            const observer = new IntersectionObserver(
                (entries) => {
                    const [entry] = entries;

                    if (!entry?.isIntersecting) {
                        return;
                    }

                    this.statsVisible = true;

                    window.setTimeout(() => {
                        if (this.statsAnimationStarted) {
                            return;
                        }

                        this.statsAnimationStarted = true;
                        this.animateStats();
                    }, 240);

                    observer.disconnect();
                }, {
                    threshold: 0.35,
                },
            );

            observer.observe(statsSection);
        },
        animateStats() {
            if (!this.stats.length) {
                return;
            }

            // Ease the count so it lands softer
            const duration = 1800;
            const startTime = performance.now();

            const tick = (currentTime) => {
                const progress = Math.min((currentTime - startTime) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 4);

               this.stats.forEach((item, i) => {
    let nextValue = Math.round(item.target * easedProgress);

    if (progress < 1 && item.target > 1) {
        nextValue = Math.min(item.target - 1, nextValue);
    }

    this.stats[i].display = this.formatStat(item, nextValue);
});
                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },
        next() {
            if (this.videos.length < 2) {
                return;
            }

            this.activeVideo = (this.activeVideo + 1) % this.videos.length;
            this.$nextTick(() => this.playActive());
        },
        previous() {
            if (this.videos.length < 2) {
                return;
            }

            this.activeVideo = (this.activeVideo - 1 + this.videos.length) % this.videos.length;
            this.$nextTick(() => this.playActive());
        },
    }" x-init="initVideo();
    initStatsAnimation()"
        class="relative overflow-hidden bg-[#171717] text-white">
        <div class="relative mx-auto max-w-[1904px] overflow-hidden bg-[#171717]">
            <div class="relative min-h-[24rem] overflow-hidden sm:min-h-[28rem] lg:min-h-[36rem]">
                {{--
                    MOBILE FIX: Use a static `src` attribute for the first video so the browser
                    can trigger autoplay at parse time (before Alpine/JS initialises).
                    The dynamic `:src` binding then takes over for subsequent videos.
                    `preload="auto"` instead of "metadata" ensures mobile browsers buffer
                    enough data to start playing without a user gesture.
                    `playsinline` MUST be a plain HTML attribute (not :playsinline) for iOS Safari.
                --}}
                <video
                    x-ref="heroVideo"
                    class="absolute inset-0 h-full w-full object-cover brightness-[1.05] saturate-[1.03]"
                    
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="auto"
                    webkit-playsinline>
                    <source src="{{ asset($firstVideoSrc) }}" type="video/mp4">
                </video>

                <div
                    class="absolute inset-0 bg-[linear-gradient(90deg,rgba(12,12,12,0.42)_0%,rgba(12,12,12,0.22)_34%,rgba(12,12,12,0.08)_66%,rgba(12,12,12,0.18)_100%)]">
                </div>

                {{-- Controls --}}
                @if ($hasMultipleHeroVideos)
                    <button type="button"
                        class="absolute left-4 top-1/2 z-20 hidden h-10 w-10 -translate-y-1/2 items-center justify-center border border-white/18 bg-black/28 text-white/90 backdrop-blur-sm transition hover:border-white/30 hover:text-white md:flex lg:left-8 lg:h-12 lg:w-12"
                        @click="previous()" aria-label="Show previous hero video">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button type="button"
                        class="absolute right-4 top-1/2 z-20 hidden h-10 w-10 -translate-y-1/2 items-center justify-center border border-white/18 bg-black/28 text-white/90 backdrop-blur-sm transition hover:border-white/30 hover:text-white md:flex lg:right-8 lg:h-12 lg:w-12"
                        @click="next()" aria-label="Show next hero video">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif

                <div
                    class="relative z-10 flex min-h-[24rem] items-center px-4 py-6 sm:min-h-[28rem] sm:px-8 sm:py-10 lg:min-h-[36rem] lg:px-16 lg:py-16 xl:px-[9.9375rem]">
                    <div class="relative w-full max-w-[22rem] sm:max-w-[57rem] lg:max-w-[56rem]">
                        <div class="absolute left-0 top-0 h-16 w-2 bg-[#ff8800] sm:h-28 sm:w-2.5 lg:h-28 lg:w-2.5"></div>

                        <div class="ml-2 bg-[rgba(18,18,18,0.34)] px-4 py-5 shadow-[0_18px_40px_rgba(0,0,0,0.2)] backdrop-blur-[1.5px] sm:ml-2.5 sm:px-8 sm:py-8 lg:px-[3.25rem] lg:py-[2.9rem]"
                            data-aos="fade-up" data-aos-duration="820">
                            <p
                                class="text-[0.74rem] font-normal uppercase tracking-[0.3em] text-[#ff9808] sm:text-[0.95rem] lg:text-[1rem] lg:tracking-[0.4em]">
                                {{ $site['hero']['eyebrow'] }}
                            </p>

                            <h1
                                class="mt-3 max-w-[18rem] font-display text-[1.95rem] leading-none tracking-normal font-[800] text-white sm:mt-4 sm:max-w-[34rem] sm:text-[3.1rem] lg:mt-4 lg:max-w-[40rem] lg:text-[4.6rem]">
                                <span>{{ $getHomePage->hero_title }}</span>
                                @if ($heroTitleAccent)
                                    <span class="text-[#ff8800]">{{ $heroTitleAccent }}</span>
                                @endif
                            </h1>

                            <p
                                class="mt-3 ml-2 max-w-[16.75rem] text-[0.8rem] leading-[1.18] font-light tracking-normal text-white sm:mt-3.5 sm:ml-0 sm:max-w-[29rem] sm:text-[0.98rem] md:text-[1.08rem] lg:mt-3 lg:max-w-[27rem] lg:text-[1.2rem] lg:leading-[1.08]">
                                {{ $getHomePage->hero_description }}
                            </p>

                            <div class="mt-5 flex flex-col gap-3 sm:mt-7 sm:gap-6 lg:mt-7 lg:flex-row lg:gap-4">
                                <a href="{{ route($site['hero']['primaryCta']['route']) }}"
                                    class="inline-flex h-[3.5rem] w-full items-center justify-center bg-[#ff9500] px-6 text-[0.94rem] font-semibold text-white transition hover:bg-[#ffa726] sm:h-[4.5rem] sm:px-8 md:w-[22.375rem] md:max-w-[22.375rem] md:text-[1.05rem] lg:h-[3.75rem] lg:w-[18rem] lg:max-w-[18rem] lg:text-[1rem]">
                                    {{ $getHomePage->hero_primary_cta_label }}
                                </a>
                                <a href="{{ route($site['hero']['secondaryCta']['route']) }}"
                                    class="inline-flex h-[3.5rem] w-full items-center justify-center bg-white px-6 text-[0.94rem] font-semibold text-[#171717] transition hover:bg-[#f5f5f5] sm:h-[4.5rem] sm:px-8 md:w-[22.375rem] md:max-w-[22.375rem] md:text-[1.05rem] lg:h-[3.75rem] lg:w-[18rem] lg:max-w-[18rem] lg:text-[1rem]">
                                    {{ $site['hero']['secondaryCta']['label'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div x-ref="statsSection"
                class="relative z-10 overflow-hidden border-t border-white/10 bg-[#1b1b1b] min-h-[15rem] lg:h-[24.3125rem]">
                <div class="pointer-events-none absolute inset-0 z-0 lg:hidden" aria-hidden="true">
                    <img src="{{ asset('assets/images/Background-under-nums.png') }}" alt=""
                        class="h-full w-full object-cover object-center">
                    <img src="{{ asset('assets/images/Backgrond-under-nums-1.png') }}" alt=""
                        class="absolute inset-0 h-full w-full object-cover object-center opacity-95 mix-blend-multiply">
                </div>

                <div class="pointer-events-none absolute top-0 left-1/2 z-0 hidden lg:block"
                    style="width: 1968px; height: 389px; opacity: 1; transform: translateX(calc(-50% - 32px));"
                    aria-hidden="true">
                    <img src="{{ asset('assets/images/Background-under-nums.png') }}" alt=""
                        class="absolute inset-0 h-full w-full object-cover object-center">
                    <img src="{{ asset('assets/images/Backgrond-under-nums-1.png') }}" alt=""
                        class="absolute inset-0 h-full w-full object-cover object-center opacity-100 mix-blend-multiply">
                </div>
                @php
                    $stat1 = intval($getHomePage->stat_1_value);
                    $stat2 = intval($getHomePage->stat_2_value);
                    $stat3 = intval($getHomePage->stat_3_value);

                   $stats = [
                        [
                            'value' => $stat1,
                            'label' => $getHomePage->stat_1_label ?? 'Projects Deliverable',
                        ],
                        [
                            'value' => $stat2,
                            'label' => $getHomePage->stat_2_label ?? 'Clients',
                        ],
                        [
                            'value' => $stat3,
                            'label' => $getHomePage->stat_3_label ?? 'Partners',
                        ],
                    ];
                @endphp
                
                <div
                class="relative z-20 mx-auto flex w-full max-w-[58rem] items-center justify-center gap-3 px-6 py-6 text-center sm:gap-4 sm:px-6 min-h-[15rem] lg:h-[24.3125rem] lg:gap-6 lg:px-16">

                    @foreach ($stats as $item)
                        <div
                            class="flex flex-1 min-w-0  flex-col items-center justify-center transition duration-700 ease-out will-change-transform"
                            style="transition-delay: {{ 80 + $loop->index * 90 }}ms;"
                            :class="statsVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">

                            <p
                                class="font-display font-semibold leading-none text-[#ff8800]
                                text-[1.3rem] sm:text-[1.6rem] md:text-[1.8rem] lg:text-[3rem]"
                                x-text="stats[{{ $loop->index }}].display"
                                x-init="$el.innerText = '{{ number_format($item['value']) }}+'">
                            </p>

                            <p
                                class="mt-1 text-center leading-tight text-white/72
                                text-[2px] sm:text-[0.75rem] md:text-[0.85rem] lg:text-[1rem]">
                                {{ $item['label'] ?? '' }}
                            </p>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    @include('frontend.partials.services-cards-section', [
        'cards' => $sharedServicesCards,
    ])

    {{-- Difference --}}
    @include('frontend.partials.why-choose-us-section', [
        'difference' => $difference,
        'variant' => 'services',
    ])

    {{-- Media --}}
    @include('frontend.partials.media-section', [
        'media' => $media,
    ])

    {{-- Portfolio — rendered identically to the standalone Projects page --}}
    @include('frontend.partials.portfolio-section', [
        'portfolio'    => $portfolioData,
        'isStandalone' => true,
    ])
@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    const video = document.querySelector("video");
    if (video) {
        video.play().catch(() => {
            console.log("Autoplay blocked");
        });
    }
});
</script>