{{-- Portfolio Section --}}
@php
    $portfolio = $portfolio ?? [];
    $isStandalone = $isStandalone ?? false;
    $heading = $portfolio['heading'] ?? [];
    $tabs = $portfolio['tabs'] ?? [];
    $projects = $portfolio['projects'] ?? [];

    $resolveMediaAsset = static function (mixed $asset, string $projectTitle, int $index): ?array {
        if (is_string($asset)) {
            $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
            $isVideo = in_array($extension, ['mp4', 'webm', 'ogg'], true);
            return [
                'type' => $isVideo ? 'video' : 'image',
                'src' => asset($asset),
                'poster' => null,
                'mime' => $isVideo ? 'video/' . $extension : null,
                'alt' => sprintf('%s image %d', $projectTitle, $index),
            ];
        }
        if (!is_array($asset) || !filled($asset['src'] ?? null)) {
            return null;
        }
        return [
            'type' => $asset['type'] ?? 'image',
            'src' => asset($asset['src']),
            'poster' => filled($asset['poster'] ?? null) ? asset($asset['poster']) : null,
            'mime' => $asset['mime'] ?? null,
            'alt' => $asset['alt'] ?? sprintf('%s image %d', $projectTitle, $index),
        ];
    };

    $interactiveProjects = collect($projects)
        ->map(function (array $project) use ($resolveMediaAsset): array {
            $gallery = collect($project['gallery'] ?? [])
                ->map(fn(mixed $asset, int $index): ?array => $resolveMediaAsset($asset, $project['title'], $index + 1))
                ->filter()
                ->values()
                ->all();
            return [
                ...$project,
                'cover_image' =>
                    data_get($gallery, '0.poster') ?? (data_get($gallery, '0.src') ?? asset($project['cover_image'])),
                'gallery' => $gallery,
            ];
        })
        ->values()
        ->all();
@endphp

<style>
    /* ═══════════════════════════════════════════════════════
   CARD BASE
═══════════════════════════════════════════════════════ */
    .project-card {
        cursor: pointer;
        outline: none;
        box-shadow: none !important;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .project-card:focus-visible {
        outline: 2px solid rgba(255, 138, 5, 0.55);
        outline-offset: 2px;
    }

    .project-card--expanded {
        box-shadow: none !important;
    }

    /* ═══════════════════════════════════════════════════════
   FILTER TABS
═══════════════════════════════════════════════════════ */
    .project-filter-tab {
        position: relative;
        padding-bottom: 6px;
        transition: color 0.2s;
    }

    .project-filter-tab::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #ff8a05;
        border-radius: 1px;
        transform: scaleX(0);
        transform-origin: left center;
        transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .project-filter-tab--active::after,
    .project-filter-tab[data-active="true"]::after {
        transform: scaleX(1);
    }

    .project-filter-tab--active,
    .project-filter-tab[data-active="true"] {
        color: #ff8a05;
    }

    /* ═══════════════════════════════════════════════════════
   COLLAPSED THUMBNAIL
═══════════════════════════════════════════════════════ */
    .project-story-fallback {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .project-story-fallback__media {
        width: 100%;
        height: 100%;
    }

    .project-story-fallback__media img,
    .project-story-fallback__media video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .project-card:hover .project-story-fallback__media img,
    .project-card:hover .project-story-fallback__media video {
        transform: scale(1.03);
    }

    /* ═══════════════════════════════════════════════════════
   STORY VIEW — desktop + mobile shared
═══════════════════════════════════════════════════════ */
    .project-story-view {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        gap: 12px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        height: 540px;
        max-height: 540px;
        min-height: 0;
        align-items: stretch;
        scroll-behavior: smooth;
        width: 100%;
        padding: 0;
        box-sizing: border-box;
        scrollbar-width: none;
        -ms-overflow-style: none;
        opacity: 0;
        animation: storyFadeIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        cursor: grab;
        user-select: none;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        overscroll-behavior-y: none;
        -webkit-overflow-scrolling: touch;
        will-change: scroll-position;
    }

    .project-story-view.is-dragging {
        cursor: grabbing;
        scroll-behavior: auto;
        scroll-snap-type: none;
    }

    .project-story-view::-webkit-scrollbar {
        display: none;
    }

    @keyframes storyFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── Lead panel ── */
    .project-story-panel--lead {
        flex: 0 0 calc(100% - 88px);
        height: 100%;
        scroll-snap-align: start;
        overflow: hidden;
        position: relative;
        border-radius: 0.75rem;
        opacity: 0;
        animation: leadIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.04s forwards;
        pointer-events: none;
    }

    .project-story-view:not(.is-dragging) .project-story-panel--lead {
        pointer-events: auto;
    }

    @keyframes leadIn {
        from {
            opacity: 0;
            transform: scale(0.985) translateY(6px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .project-story-panel--lead img,
    .project-story-panel--lead video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        pointer-events: none;
        -webkit-user-drag: none;
    }

    .project-story-panel--lead:hover img,
    .project-story-panel--lead:hover video {
        transform: scale(1.04);
    }

    /* ── Details panel ── */
    .project-story-panel--details {
        flex: 0 0 calc(48% - 44px);
        min-width: 280px;
        height: 100%;
        scroll-snap-align: start;
        background: #faf7f2;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2.75rem 2.5rem 2.75rem 2.25rem;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior-y: contain;
        touch-action: pan-x pan-y;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box;
        border-radius: 0.75rem;
        opacity: 0;
        animation: detailsIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) 0.18s forwards;
        cursor: default;
        pointer-events: auto !important;
    }

    @keyframes detailsIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .project-story-panel--details .swipe-hint {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 2rem;
        opacity: 0.35;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #1f1f1f;
        user-select: none;
        animation: swipeHintPulse 2s ease-in-out 1.2s 2 alternate;
    }

    @keyframes swipeHintPulse {
        from {
            opacity: 0.35;
            transform: translateX(0);
        }

        to {
            opacity: 0.65;
            transform: translateX(4px);
        }
    }

    .project-story-panel--details .swipe-hint svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* ── Secondary image panels ── */
    .project-story-panel {
        flex: 0 0 68%;
        height: 100%;
        scroll-snap-align: start;
        overflow: hidden;
        position: relative;
        border-radius: 0.75rem;
        opacity: 0;
        animation: panelIn 0.48s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .project-story-panel:nth-child(3) {
        animation-delay: 0.20s;
    }

    .project-story-panel:nth-child(4) {
        animation-delay: 0.26s;
    }

    .project-story-panel:nth-child(5) {
        animation-delay: 0.32s;
    }

    .project-story-panel:nth-child(6) {
        animation-delay: 0.38s;
    }

    .project-story-panel:nth-child(7) {
        animation-delay: 0.44s;
    }

    @keyframes panelIn {
        from {
            opacity: 0;
            transform: translateX(22px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .project-story-panel__media {
        height: 100%;
        width: 100%;
        overflow: hidden;
        border-radius: inherit;
    }

    .project-story-panel__media img,
    .project-story-panel__media video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
        pointer-events: none;
        -webkit-user-drag: none;
    }

    .project-story-panel:hover .project-story-panel__media img,
    .project-story-panel:hover .project-story-panel__media video {
        transform: scale(1.03);
    }

    /* ── Details copy ── */
    .project-story-copy__label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #ff8800;
        margin: 0 0 0.6rem;
    }

    .project-story-copy__title {
        font-size: clamp(1.4rem, 2.2vw, 2rem);
        font-weight: 600;
        line-height: 1.15;
        color: #1f1f1f;
        margin: 0 0 1.6rem;
        padding-bottom: 1.4rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .project-story-copy__fields {
        display: flex;
        flex-direction: column;
        gap: 1.35rem;
    }

    .project-story-copy__field {
        display: flex;
        flex-direction: column;
        gap: 0.28rem;
    }

    .project-story-copy__field-label {
        font-size: 0.67rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #b0b0b0;
    }

    .project-story-copy__field-value {
        font-size: 0.96rem;
        line-height: 1.62;
        color: #2e2e2e;
    }

    .project-row__meta--hidden {
        display: none !important;
    }

    .project-row__preview {
        width: 100%;
        overflow: hidden;
        max-height: inherit;
    }

    .project-row {
        transition: border-radius 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        overflow: hidden;
    }

    /* ═══════════════════════════════════════════════════════
   MOBILE EXPANDED
═══════════════════════════════════════════════════════ */
    .project-mobile-expanded {
        display: none !important;
    }

    @media (max-width: 767px) {
        .project-story-view {
            display: flex !important;
            height: 420px;
            max-height: 420px;
            min-height: 0;
        }

        .project-story-panel--lead {
            flex: 0 0 calc(100% - 72px);
        }

        .project-story-panel--details {
            flex: 0 0 calc(55% - 36px);
            min-width: 240px;
            padding: 1.75rem 1.5rem 1.75rem 1.4rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x pan-y;
            pointer-events: auto !important;
        }

        .project-story-panel {
            flex: 0 0 72%;
        }

        .project-story-copy__title {
            font-size: clamp(1.1rem, 4vw, 1.5rem);
            margin: 0 0 1.1rem;
            padding-bottom: 1rem;
        }

        .project-story-copy__fields {
            gap: 1rem;
        }

        .project-story-panel--details .swipe-hint {
            display: flex;
        }
    }

    /* ═══════════════════════════════════════════════════════
   SCROLL FAB
═══════════════════════════════════════════════════════ */
    .portfolio-fab {
        position: fixed;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 700;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.28s ease, translate 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        translate: 16px 0;
    }

    .portfolio-fab.is-visible {
        opacity: 1;
        pointer-events: auto;
        translate: 0 0;
    }

    .portfolio-fab__toggle {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 0.48rem 0.85rem;
        background: #1a1a1a;
        color: #fff;
        border: none;
        border-radius: 2rem;
        font-size: 0.77rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
        transition: background 0.16s;
    }

    .portfolio-fab__toggle:hover {
        background: #2e2e2e;
    }

    .portfolio-fab__toggle svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .portfolio-fab__menu {
        background: #fff;
        border: 0.5px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.9rem;
        padding: 0.28rem;
        min-width: 138px;
        display: flex;
        flex-direction: column;
        gap: 1px;
        box-shadow: 0 6px 22px rgba(0, 0, 0, 0.13);
        opacity: 0;
        transform: scale(0.92) translateY(8px);
        transform-origin: top right;
        pointer-events: none;
        transition: opacity 0.2s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.2s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .portfolio-fab__menu.is-open {
        opacity: 1;
        transform: scale(1) translateY(0);
        pointer-events: auto;
    }

    .portfolio-fab__item {
        padding: 0.38rem 0.65rem;
        font-size: 0.81rem;
        border-radius: 0.55rem;
        cursor: pointer;
        color: #2a2a2a;
        text-align: right;
        transition: background 0.13s;
        white-space: nowrap;
    }

    .portfolio-fab__item:hover {
        background: rgba(255, 138, 5, 0.09);
    }

    .portfolio-fab__item.is-active {
        color: #ff8a05;
        font-weight: 600;
    }
</style>

<section x-data="projectsPortfolio(@js([
    'tabs' => $tabs,
    'projects' => $interactiveProjects,
    'isStandalone' => $isStandalone,
]))" x-init="activeCategory = null" @class([
    'portfolio-section',
    'portfolio-section--standalone' => $isStandalone,
    'portfolio-section--embedded' => !$isStandalone,
])>
    <div class="mx-auto max-w-[1904px]">
        <div class="mx-auto max-w-[92rem] px-4 sm:px-6 lg:px-8">

            @if ($isStandalone)
                <div class="" data-aos="fade-up">
                    <div class="text-[#ff8a05] pb-12" aria-hidden="true"><span>OUR PORTFOLIOS</span></div>
                    <div class="projects-topbar__tabs">
                        @foreach ($tabs as $tab)
                            <button type="button" class="project-filter-tab"
                                :class="{ 'project-filter-tab--active': activeCategory === '{{ $tab }}' }"
                                :data-active="activeCategory === '{{ $tab }}' ? 'true' : 'false'"
                                :aria-pressed="activeCategory === '{{ $tab }}' ? 'true' : 'false'"
                                @click="setCategory('{{ $tab }}')">{{ $tab }}</button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="projects-topbar-embedded" data-aos="fade-up">
                    <div class="projects-topbar-embedded__heading"><span>OUR PORTFOLIOS</span></div>
                    <div class="projects-topbar-embedded__tabs">
                        @foreach ($tabs as $tab)
                            <button type="button" class="project-filter-tab"
                                :class="{ 'project-filter-tab--active': activeCategory === '{{ $tab }}' }"
                                :data-active="activeCategory === '{{ $tab }}' ? 'true' : 'false'"
                                :aria-pressed="activeCategory === '{{ $tab }}' ? 'true' : 'false'"
                                @click="setCategory('{{ $tab }}')">{{ $tab }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="project-list space-y-5 lg:space-y-6"
                :class="{
                    'project-list--compact': visibleProjectCount() <= 2,
                    'project-list--single': visibleProjectCount() <= 1,
                    'project-list--has-expanded': expandedProjectId !== null
                }">
                <template x-if="visibleProjectCount() === 0">
                    <div
                        class="rounded-[1.75rem] border border-black/8 bg-[#faf7f2] px-6 py-10 text-center text-[0.96rem] text-[#5f5f5f]">
                        No projects are available in this category yet.
                    </div>
                </template>

                @foreach ($interactiveProjects as $project)
                    @php
                        $leadMedia = $project['gallery'][0] ?? null;
                        $secondaryMedia = array_slice($project['gallery'], 1);
                        $categorySlug = strtolower($project['type_label'] ?? '');
                    @endphp

                    <article data-project-id="{{ $project['id'] }}" x-show="isProjectVisible('{{ $project['id'] }}')"
                        x-transition:enter="transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-[0.982]"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition duration-250 ease-[cubic-bezier(0.4,0,1,1)]"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.99]" class="project-card"
                        :class="{ 'project-card--expanded': isExpanded('{{ $project['id'] }}') }" data-aos="fade-up"
                        data-aos-duration="760" data-aos-delay="{{ 80 + $loop->index * 70 }}" role="button"
                        tabindex="0" @click="handleCardClick($event, '{{ $project['id'] }}')"
                        @keydown.enter.self.prevent="toggleProject('{{ $project['id'] }}', { source: 'keyboard' })"
                        @keydown.space.self.prevent="toggleProject('{{ $project['id'] }}', { source: 'keyboard' })"
                        :aria-expanded="isExpanded('{{ $project['id'] }}').toString()"
                        aria-controls="project-detail-{{ $project['id'] }}">
                        <div class="project-row"
                            :class="{ 'project-row--expanded': isExpanded('{{ $project['id'] }}') }">
                            <div class="project-row__preview" data-project-preview>

                                {{-- COLLAPSED: cover thumbnail --}}
                                <div class="project-story-fallback" x-show="!isExpanded('{{ $project['id'] }}')"
                                    x-transition:leave="transition duration-280 ease-in"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    aria-hidden="true">
                                    <div class="project-story-fallback__media">
                                        @if ($leadMedia)
                                            <img src="{{ $leadMedia['src'] }}" alt="{{ $leadMedia['alt'] }}"
                                                class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        @endif
                                    </div>
                                </div>

                                {{-- EXPANDED: horizontal snap-scroll story --}}
                                <div class="project-story-view js-project-story-view" data-project-story-view
                                    x-show="isExpanded('{{ $project['id'] }}')"
                                    x-effect="if (isExpanded('{{ $project['id'] }}')) { $nextTick(() => { $el.scrollLeft = 0; }); }">
                                    @if ($leadMedia)
                                        <div class="project-story-panel--lead">
                                            <img src="{{ $leadMedia['src'] }}" alt="{{ $leadMedia['alt'] }}"
                                                class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        </div>
                                    @endif

                                    <div class="project-story-panel--details">
                                        <p class="swipe-hint" aria-hidden="true">
                                            MORE IMAGES
                                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 3l5 5-5 5" />
                                            </svg>
                                        </p>
                                        <p class="project-story-copy__label">{{ strtoupper($project['type_label']) }}
                                        </p>
                                        <h3 class="project-story-copy__title">{{ $project['title'] }}</h3>
                                        <div class="project-story-copy__fields">

                                            @if ($categorySlug === 'construction')
                                                {{-- CONSTRUCTION: Show Concept --}}
                                                @if (!empty($project['concept']))
                                                    <div class="project-story-copy__field">
                                                        <div class="project-story-copy__field-label">CONCEPT</div>
                                                        <div class="project-story-copy__field-value">
                                                            {!! $project['concept'] !!}</div>
                                                    </div>
                                                @endif
                                            @elseif($categorySlug === 'interior')
                                                {{-- INTERIOR: Show Scope of Work --}}
                                                @if (!empty($project['scope_of_work'] ?? '') || !empty($project['concept']))
                                                    <div class="project-story-copy__field">
                                                        <div class="project-story-copy__field-label">SCOPE OF WORK</div>
                                                        <div class="project-story-copy__field-value">
                                                            {!! ($project['scope_of_work'] ?? $project['concept']) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif($categorySlug === 'renovation')
                                                {{-- RENOVATION: Show Scope of Work --}}
                                                @if (!empty($project['scope_of_work'] ?? '') || !empty($project['concept']))
                                                    <div class="project-story-copy__field">
                                                        <div class="project-story-copy__field-label">SCOPE OF WORK</div>
                                                        <div class="project-story-copy__field-value">
                                                            {!! ($project['scope_of_work'] ?? $project['concept']) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            {{-- LOCATION - always show if exists --}}
                                            @if (!empty($project['location']))
                                                <div class="project-story-copy__field">
                                                    <div class="project-story-copy__field-label">LOCATION</div>
                                                    <div class="project-story-copy__field-value">{!! $project['location'] !!}
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- YEAR - always show if exists --}}
                                            @if (!empty($project['year']))
                                                <div class="project-story-copy__field">
                                                    <div class="project-story-copy__field-label">YEAR</div>
                                                    <div class="project-story-copy__field-value">{{ $project['year'] }}
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </div>

                                    @foreach ($secondaryMedia as $media)
                                        <div class="project-story-panel">
                                            <div class="project-story-panel__media">
                                                <img src="{{ $media['src'] }}" alt="{{ $media['alt'] }}"
                                                    class="h-full w-full object-cover" loading="lazy"
                                                    decoding="async">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- COLLAPSED META COLUMN --}}
                            <div class="project-row__meta"
                                :class="{ 'project-row__meta--hidden': isExpanded('{{ $project['id'] }}') }"
                                id="project-detail-{{ $project['id'] }}">
                                <div>
                                    <p class="project-row__type"
                                        x-text="activeCategory || '{{ addslashes($project['type_label']) }}'">
                                        {{ $project['type_label'] }}</p>
                                    <h2 class="project-row__title">{{ $project['title'] }}</h2>
                                </div>
                                <div class="space-y-2">
                                    @if (!empty($project['year']))
                                        <div class="project-row__detail">Completed: {{ $project['year'] }}</div>
                                    @endif
                                    @if (!empty($project['location']))
                                        <div class="project-row__detail">{{ $project['location'] }}</div>
                                    @endif
                                </div>
                                <div class="project-concept-panel"
                                    :class="{ 'project-concept-panel--expanded': isExpanded('{{ $project['id'] }}') }"
                                    :aria-hidden="(!isExpanded('{{ $project['id'] }}')).toString()">
                                    <div class="project-concept-panel__inner">
                                        @if ($categorySlug === 'construction')
                                            <p class="project-row__section-title">Concept</p>
                                            <div class="project-row__detail project-row__concept">
                                                {!! $project['concept'] !!}</div>
                                        @elseif($categorySlug === 'interior' || $categorySlug === 'renovation')
                                            <p class="project-row__section-title">Scope of Work</p>
                                            <div class="project-row__detail project-row__concept">
                                                {!! ($project['scope_of_work'] ?? $project['concept']) !!}
                                            </div>
                                        @else
                                            <p class="project-row__section-title">Specifications</p>
                                            <div class="project-row__detail project-row__concept">
                                                {!! $project['specification'] !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SCROLL FAB --}}
    <div class="portfolio-fab" id="js-portfolio-fab" aria-label="Filter projects">
        <button class="portfolio-fab__toggle" id="js-fab-toggle" type="button" aria-haspopup="listbox"
            aria-expanded="false">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="1" y1="4" x2="15" y2="4" />
                <line x1="3" y1="8" x2="13" y2="8" />
                <line x1="5" y1="12" x2="11" y2="12" />
            </svg>
            <span id="js-fab-label">All</span>
            <svg id="js-fab-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <path d="M4 6l4 4 4-4" />
            </svg>
        </button>
        <div class="portfolio-fab__menu" id="js-fab-menu" role="listbox">
            @foreach ($tabs as $tab)
                <div class="portfolio-fab__item" role="option" tabindex="0" data-tab="{{ $tab }}">
                    {{ $tab }}</div>
            @endforeach
        </div>
    </div>
</section>

<script>
    /* DESKTOP WHEEL HANDLER */
    (function() {
        'use strict';
        document.addEventListener('wheel', function(e) {
            var sv = e.target.closest && e.target.closest('.project-story-view');
            if (!sv) return;
            var absX = Math.abs(e.deltaX),
                absY = Math.abs(e.deltaY);
            if (absX >= absY) return;
            var dp = e.target.closest && e.target.closest('.project-story-panel--details');
            e.preventDefault();
            e.stopPropagation();
            if (dp) {
                var atBottom = dp.scrollTop >= dp.scrollHeight - dp.clientHeight - 1;
                var atTop = dp.scrollTop <= 0;
                if ((e.deltaY > 0 && !atBottom) || (e.deltaY < 0 && !atTop)) {
                    dp.scrollTop += e.deltaY;
                } else {
                    window.scrollBy({
                        top: e.deltaY,
                        behavior: 'auto'
                    });
                }
            } else {
                window.scrollBy({
                    top: e.deltaY,
                    behavior: 'auto'
                });
            }
        }, {
            passive: false,
            capture: true
        });
    }());

    /* DESKTOP MOUSE-DRAG TO SCROLL */
    (function() {
        'use strict';
        var dragging = false;
        var startX = 0;
        var scrollX = 0;
        var target = null;
        document.addEventListener('mousedown', function(e) {
            var sv = e.target.closest && e.target.closest('.project-story-view');
            if (!sv) return;
            if (e.button !== 0) return;
            if (e.target.closest('.project-story-panel--details')) return;
            dragging = true;
            target = sv;
            startX = e.pageX - sv.offsetLeft;
            scrollX = sv.scrollLeft;
            sv.classList.add('is-dragging');
            e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging || !target) return;
            var x = e.pageX - target.offsetLeft;
            var walk = (x - startX) * 1.4;
            target.scrollLeft = scrollX - walk;
        });

        function stopDrag() {
            if (!dragging || !target) return;
            dragging = false;
            target.classList.remove('is-dragging');
            target = null;
        }
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('mouseleave', stopDrag);
    }());

    /* TOUCH HANDLER */
    (function() {
        'use strict';
        var LOCK_THRESHOLD = 6;
        var COAST_FACTOR = 0.38;
        var startX = 0;
        var startY = 0;
        var lastX = 0;
        var lastTime = 0;
        var velX = 0;
        var direction = null;
        var activeSv = null;
        var activeDp = null;
        var coastRAF = null;

        function cancelCoast() {
            if (coastRAF) {
                cancelAnimationFrame(coastRAF);
                coastRAF = null;
            }
        }
        document.addEventListener('touchstart', function(e) {
            cancelCoast();
            direction = null;
            activeSv = e.target.closest && e.target.closest('.project-story-view');
            activeDp = e.target.closest && e.target.closest('.project-story-panel--details');
            if (!activeSv) return;
            var t = e.touches[0];
            startX = lastX = t.clientX;
            startY = t.clientY;
            lastTime = Date.now();
            velX = 0;
            activeSv.style.scrollSnapType = 'none';
            activeSv.style.scrollBehavior = 'auto';
        }, {
            passive: true
        });
        document.addEventListener('touchmove', function(e) {
            if (!activeSv) return;
            var t = e.touches[0];
            var dx = t.clientX - startX;
            var dy = t.clientY - startY;
            var now = Date.now();
            var dt = now - lastTime || 1;
            var instantVel = (t.clientX - lastX) / dt;
            velX = velX * 0.6 + instantVel * 0.4;
            lastX = t.clientX;
            lastTime = now;
            if (!direction && (Math.abs(dx) > LOCK_THRESHOLD || Math.abs(dy) > LOCK_THRESHOLD)) {
                direction = Math.abs(dx) >= Math.abs(dy) ? 'h' : 'v';
            }
            if (!direction) return;
            if (direction === 'h') {
                return;
            }
            if (activeDp) {
                var atBottom = activeDp.scrollTop >= activeDp.scrollHeight - activeDp.clientHeight - 1;
                var atTop = activeDp.scrollTop <= 0;
                var goingDown = dy < 0;
                var goingUp = dy > 0;
                if ((goingDown && !atBottom) || (goingUp && !atTop)) {
                    e.stopPropagation();
                }
            }
        }, {
            passive: true
        });
        document.addEventListener('touchend', function() {
            if (!activeSv) {
                direction = null;
                activeSv = null;
                activeDp = null;
                return;
            }
            var sv = activeSv;
            if (direction === 'h' && Math.abs(velX) > 0.1) {
                var coast = velX * -320 * COAST_FACTOR;
                sv.scrollBy({
                    left: coast,
                    behavior: 'smooth'
                });
            }
            setTimeout(function() {
                if (sv) {
                    sv.style.scrollSnapType = '';
                    sv.style.scrollBehavior = '';
                }
            }, 380);
            direction = null;
            activeSv = null;
            activeDp = null;
            velX = 0;
        }, {
            passive: true
        });
    }());
</script>

<script>
    /* Scroll FAB */
    (function() {
        var fab = document.getElementById('js-portfolio-fab');
        var btn = document.getElementById('js-fab-toggle');
        var menu = document.getElementById('js-fab-menu');
        var lbl = document.getElementById('js-fab-label');
        var chev = document.getElementById('js-fab-chevron');
        var items = menu ? Array.prototype.slice.call(menu.querySelectorAll('.portfolio-fab__item')) : [];
        if (!fab || !btn || !menu) return;
        var open = false;

        function onScroll() {
            var section = fab.closest('section') || document.querySelector('.portfolio-section');
            var inSection = false;
            if (section) {
                var rect = section.getBoundingClientRect();
                inSection = rect.top < window.innerHeight * 0.85 && rect.bottom > window.innerHeight * 0.15;
            }
            fab.classList.toggle('is-visible', inSection);
            if (!inSection && open) closeMenu();
        }
        window.addEventListener('scroll', onScroll, {
            passive: true
        });
        onScroll();

        function openMenu() {
            open = true;
            menu.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
            var p = chev.querySelector('path');
            if (p) p.setAttribute('d', 'M4 10l4-4 4 4');
        }

        function closeMenu() {
            open = false;
            menu.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
            var p = chev.querySelector('path');
            if (p) p.setAttribute('d', 'M4 6l4 4 4-4');
        }
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            open ? closeMenu() : openMenu();
        });
        document.addEventListener('click', function(e) {
            if (!fab.contains(e.target)) closeMenu();
        });

        function callSetCategory(cat) {
            var section = fab.closest('section') || document.querySelector('.portfolio-section');
            if (!section) return;
            try {
                var stack = section._x_dataStack;
                if (stack && stack[0] && typeof stack[0].setCategory === 'function') {
                    stack[0].setCategory(cat);
                    return;
                }
                if (window.Alpine && Alpine.closestDataStack) {
                    var s = Alpine.closestDataStack(section);
                    if (s && s[0] && typeof s[0].setCategory === 'function') {
                        s[0].setCategory(cat);
                        return;
                    }
                }
            } catch (err) {}
            document.querySelectorAll('.project-filter-tab').forEach(function(t) {
                if (t.textContent.trim() === cat) t.click();
            });
        }

        function selectItem(item) {
            items.forEach(function(i) {
                i.classList.toggle('is-active', i === item);
            });
            lbl.textContent = item.dataset.tab;
            callSetCategory(item.dataset.tab);
            closeMenu();
        }
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                selectItem(item);
            });
            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectItem(item);
                }
            });
        });
    }());
</script>

<script>
    /* MOBILE SCROLL DOTS */
    (function() {
        'use strict';

        function initDots(sv) {
            var panels = Array.prototype.slice.call(
                sv.querySelectorAll(
                    '.project-story-panel--lead, .project-story-panel--details, .project-story-panel'
                )
            );
            if (panels.length < 2) return;
            var wrap = document.createElement('div');
            wrap.className = 'story-dots';
            var dots = panels.map(function(_, i) {
                var d = document.createElement('span');
                d.className = 'story-dot' + (i === 0 ? ' is-active' : '');
                wrap.appendChild(d);
                return d;
            });
            sv.parentNode.insertBefore(wrap, sv.nextSibling);
            var ticking = false;
            sv.addEventListener('scroll', function() {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function() {
                    ticking = false;
                    var svLeft = sv.scrollLeft;
                    var svWidth = sv.clientWidth;
                    var best = 0,
                        bestScore = Infinity;
                    panels.forEach(function(p, i) {
                        var score = Math.abs(p.offsetLeft - svLeft - (svWidth * 0.1));
                        if (score < bestScore) {
                            bestScore = score;
                            best = i;
                        }
                    });
                    dots.forEach(function(d, i) {
                        d.classList.toggle('is-active', i === best);
                    });
                });
            }, {
                passive: true
            });
        }

        function setup() {
            document.querySelectorAll('.js-project-story-view').forEach(initDots);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
        document.addEventListener('click', function() {
            setTimeout(function() {
                document.querySelectorAll('.js-project-story-view').forEach(function(sv) {
                    if (!sv.querySelector('.story-dots')) initDots(sv);
                });
            }, 600);
        });
    }());
</script>