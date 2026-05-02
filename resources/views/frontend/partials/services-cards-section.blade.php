@php
    $cards = $cards ?? [];
    $iconcards = [
        ['icon' => '1.png'],
        ['icon' => '2.png'],
        ['icon' => '3.png'],
        ['icon' => '4.png'],
    ];
@endphp

{{-- Services --}}
@if (!empty($cards))
    <section class="pt-6 pb-6 md:pt-14 md:pb-6 xl:pt-[clamp(4.5rem,7vw,6.5rem)] xl:pb-[clamp(2rem,5vw,4.75rem)]">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

        <style>
            .cardsSwiper {
                padding-bottom: 3rem !important;
                overflow: visible !important;
            }
            .cardsSwiper .swiper-wrapper {
                align-items: stretch;
            }
            .cardsSwiper .swiper-slide {
                height: auto;
            }

            /* Pagination dots */
            .cardsSwiper .swiper-pagination {
                bottom: 0;
            }
            .cardsSwiper .swiper-pagination-bullet {
                width: 6px;
                height: 6px;
                background: #555;
                opacity: 1;
                border-radius: 999px;
                transition: width 0.3s ease, background 0.3s ease;
            }
            .cardsSwiper .swiper-pagination-bullet-active {
                width: 24px;
                background: #ff8800;
            }

            /* Hide nav arrows on mobile */
            .cardsSwiper .swiper-button-prev,
            .cardsSwiper .swiper-button-next {
                display: none;
            }
            @media (min-width: 1024px) {
                .cardsSwiper .swiper-button-prev,
                .cardsSwiper .swiper-button-next {
                    display: flex;
                    width: 2.75rem;
                    height: 2.75rem;
                    background: #1f1f1f;
                    border: 1px solid rgba(255,255,255,0.12);
                    border-radius: 0;
                    top: 42%;
                    transition: background 0.2s;
                }
                .cardsSwiper .swiper-button-prev:hover,
                .cardsSwiper .swiper-button-next:hover {
                    background: #ff8800;
                }
                .cardsSwiper .swiper-button-prev::after,
                .cardsSwiper .swiper-button-next::after {
                    font-size: 0.75rem;
                    font-weight: 700;
                    color: #fff;
                }
                .cardsSwiper .swiper-button-prev { left: -1rem; }
                .cardsSwiper .swiper-button-next { right: -1rem; }
            }
        </style>

        <div class="mx-auto max-w-[1904px]">
            <div class="mx-auto w-full max-w-[79rem] px-[clamp(1rem,2vw,1.25rem)] xl:max-w-[1206px] xl:px-0">

                <div class="swiper cardsSwiper">
                    <div class="swiper-wrapper">

                        @foreach ($cards as $card)
                            <div class="swiper-slide h-auto flex">
                                <article
                                    class="group relative flex h-full w-full flex-col overflow-hidden bg-[#1f1f1f] px-6 pt-7 pb-5 text-white transition-[background-color,color,box-shadow] duration-200
                                    before:absolute before:inset-x-0 before:top-0 before:h-2 before:bg-[#ff8800] before:content-[''] before:transition-colors before:duration-200
                                    hover:bg-[#ff9100] hover:text-[#181818] hover:shadow-[0_22px_38px_rgba(0,0,0,0.16)] hover:before:bg-[#1f1f1f]
                                    sm:px-[1.6rem] sm:pt-8 sm:pb-[1.4rem]
                                    xl:px-[27px] xl:pt-[18px] xl:pb-5 xl:before:h-[7px]"
                                    data-aos="fade-up"
                                    data-aos-delay="{{ 60 + $loop->index * 90 }}">

                                    <div class="flex flex-col h-full">

                                        {{-- ICON --}}
                                        <img src="{{ asset('assets/icon/icon/' . $iconcards[$loop->index % count($iconcards)]['icon']) }}"
                                            alt=""
                                            class="mt-3 h-[2.15rem] w-auto max-w-[2.15rem] object-contain transition-[filter] duration-200
                                            group-hover:[filter:brightness(0)_saturate(100%)]
                                            sm:mt-[1.2rem] sm:h-[2.375rem] sm:max-w-[2.375rem]
                                            xl:mt-[14px] xl:h-[30px] xl:max-w-[34px]">

                                        {{-- TITLE --}}
                                        <h2 class="mt-2 w-full leading-[1.05] font-normal tracking-[-0.04em]
                                            text-[1.1rem]
                                            sm:mt-[1.8rem] sm:text-[1.3rem]
                                            md:mt-[1.4rem] md:text-[1.1rem]
                                            lg:mt-[1.6rem] lg:text-[1.25rem]
                                            xl:mt-[28px] xl:text-[18px] xl:leading-[1.08] xl:tracking-[-0.03em]">
                                            {{ $card['title'] }}
                                        </h2>

                                        {{-- DESCRIPTION --}}
                                        <p class="mt-4 flex-grow font-light leading-[1.25] opacity-[0.92]
                                            text-[0.85rem]
                                            sm:mt-[1.2rem] sm:text-[0.95rem]
                                            md:mt-[1rem] md:text-[0.82rem]
                                            lg:mt-[1.2rem] lg:text-[0.88rem]
                                            xl:mt-[22px] xl:text-[12.5px] xl:leading-[1.18]">
                                            {{ $card['description'] }}
                                        </p>

                                        {{-- BUTTON --}}
                                        <a href="{{ route('contact') }}"
                                            class="flex justify-center items-center mt-4 self-start border-0 bg-[#ff9500] font-normal text-[#1f1f1f]
                                            transition-[background-color,color] duration-200
                                            group-hover:bg-white group-hover:text-[#ff8800]
                                            h-9 px-5 text-[0.8rem]
                                            sm:h-9 sm:px-5 sm:text-[0.8rem]
                                            md:h-8 md:px-4 md:text-[0.75rem]
                                            lg:h-8 lg:px-5 lg:text-[0.78rem]
                                            xl:h-[25px] xl:w-[97px] xl:px-0 xl:text-[10.5px]">
                                            Find Out More
                                        </a>

                                    </div>
                                </article>
                            </div>
                        @endforeach

                    </div>

                    {{-- <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div> --}}
                    <div class="swiper-pagination"></div>
                </div>

            </div>
        </div>
    </section>
@endif

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    new Swiper(".cardsSwiper", {
        slidesPerView: 1,
        spaceBetween: 14,
        loop: false,
        grabCursor: true,
        speed: 500,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
                spaceBetween: 16,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 22,
            },
            1280: {
                slidesPerView: 4,
                spaceBetween: 24,
            }
        }
    });
});
</script>