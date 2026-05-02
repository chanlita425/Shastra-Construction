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
    <section class="pt-10 pb-10 md:pt-14 md:pb-12 xl:pt-[clamp(4.5rem,7vw,6.5rem)] xl:pb-[clamp(3rem,5vw,4.75rem)]">
        <div class="mx-auto max-w-[1904px]">
            <div class="mx-auto w-full max-w-[79rem] px-[clamp(1rem,2vw,1.25rem)] xl:max-w-[1206px] xl:px-0">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-[clamp(1rem,1.35vw,1.45rem)] xl:grid-cols-[repeat(4,286px)] xl:justify-center xl:gap-x-[20.666px] xl:gap-y-0 items-stretch">

                    @foreach ($cards as $card)
                        <article class="group relative mx-auto flex h-full w-full max-w-[22.5rem] flex-col overflow-hidden bg-[#1f1f1f] px-6 pt-7 pb-5 text-white transition-[background-color,color,box-shadow] duration-200 
                                before:absolute before:inset-x-0 before:top-0 before:h-2 before:bg-[#ff8800] before:content-[''] before:transition-colors before:duration-200 
                                hover:bg-[#ff9100] hover:text-[#181818] hover:shadow-[0_22px_38px_rgba(0,0,0,0.16)] hover:before:bg-[#1f1f1f]

                                sm:max-w-none sm:px-[1.6rem] sm:pt-8 sm:pb-[1.4rem] xl:px-[27px] xl:pt-[18px] xl:pb-5 xl:before:h-[7px]"
                            data-aos="fade-up" data-aos-delay="{{ 60 + $loop->index * 90 }}">

                            <div class="flex flex-col h-auto">

                                {{-- ICON --}}
                                {{-- @if (filled($card['icon'] ?? null))
                                    <img src="{{ Str::startsWith($card['icon'], ['http', '/']) ? $card['icon'] : asset($card['icon']) }}"
                                        alt="" @class([
                                            'mt-3 h-[2.15rem] w-auto max-w-[2.15rem] object-contain transition-[filter] duration-200 group-hover:[filter:brightness(0)_saturate(100%)] 
                                                                                        sm:mt-[1.2rem] sm:h-[2.375rem] sm:max-w-[2.375rem] 
                                                                                        xl:mt-[14px] xl:h-[30px] xl:max-w-[34px]',

                                            '[filter:brightness(0)_saturate(100%)_invert(57%)_sepia(85%)_saturate(2782%)_hue-rotate(4deg)_brightness(103%)_contrast(103%)]'
                                            => ($card['icon_tone'] ?? null) === 'dark-source',
                                        ]) loading="lazy" decoding="async">
                                @endif --}}
                                 <img src="{{ asset('assets/icon/icon/' . $iconcards[$loop->index]['icon']) }}"
                                        alt="" class="mt-3 h-[2.15rem] w-auto max-w-[2.15rem] object-contain transition-[filter] duration-200 group-hover:[filter:brightness(0)_saturate(100%)] 
                                                                                        sm:mt-[1.2rem] sm:h-[2.375rem] sm:max-w-[2.375rem] 
                                                                                        xl:mt-[14px] xl:h-[30px] xl:max-w-[34px]">

                                {{-- TITLE --}}
                                <h2 class="mt-2 w-full xl:max-w-[15rem] text-lg h-auto md:h-16 leading-[1.05] font-normal tracking-[-0.04em] 
                                        sm:mt-[1.8rem] sm:text-[clamp(1.55rem,1.45vw,2rem)] 
                                        xl:mt-[28px] xl:max-w-[220px] xl:text-[18px] xl:leading-[1.08] xl:tracking-[-0.03em]">
                                    {{ $card['title'] }}
                                </h2>

                                {{-- DESCRIPTION --}}
                                <p class="h-auto md:h-40 mt-5 flex-grow text-[0.95rem] leading-[1.25] font-light opacity-[0.92] 
                                        sm:mt-[1.6rem] w-full sm:leading-[1.28] 
                                        xl:mt-[22px] xl:max-w-[225px] xl:text-[12.5px] xl:leading-[1.18]">
                                    {{ $card['description'] }}
                                </p>

                                {{-- BUTTON --}}
                                <a href="{{ route('contact') }}" class="flex justify-center items-center mt-4 h-9 self-start border-0 bg-[#ff9500] px-6 text-[0.8rem] font-normal text-[#1f1f1f] 
                                        transition-[background-color,color] duration-200 
                                        group-hover:bg-white group-hover:text-[#ff8800] 
                                        disabled:cursor-not-allowed disabled:opacity-100

                                        sm:min-h-6 sm:min-w-[97px] sm:px-[1.1rem] sm:py-2 sm:text-[0.75rem] 
                                        xl:h-[25px] xl:w-[97px] xl:min-h-0 xl:min-w-0 xl:px-0 xl:py-0 xl:text-[10.5px]">
                                    Find Out More
                                </a>

                            </div>
                        </article>
                    @endforeach

                </div>
            </div>
        </div>
    </section>
@endif

<script>
document.addEventListener("DOMContentLoaded", function () {
    new Swiper(".cardsSwiper", {
        slidesPerView: 1.1,
        spaceBetween: 16,
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 24,
            }
        }
    });
});
</script>