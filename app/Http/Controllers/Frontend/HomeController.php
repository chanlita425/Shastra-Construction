<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomePageSetting;
use App\Models\Product;
use App\Models\Why;
use App\Services\SiteContentService;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(SiteContentService $siteContentService): View
    {
        $getHomePage = HomePageSetting::first();
        $banners = Banner::all();

        // --- Step 2: Fetch Projects / Portfolio from      DB ---
        $products = Product::with('category')->latest()->get();
        $categories = Category::orderBy('order')->get();

        // Fetch Why Us items from dashboard and map to the existing services-card format
        $whyCards = Why::all()->take(4)->map(fn (Why $why): array => [
            'icon'        => filled($why->image) ? route('public.media.show', ['path' => $why->image]) : null,
            'icon_tone'   => 'dark-source',
            'title'       => $why->title_en ?? '',
            'description' => filled($why->content_en) ? strip_tags($why->content_en) : '',
            'theme'       => 'dark',
            'cta_label'   => 'Find Out More',
        ])->all();

        $tabs = $categories->pluck('name_en')->filter()->values()->all();

        $projects = $products->map(function (Product $product): array {
            $categoryName = $product->category?->name_en ?? 'Uncategorized';
            $images = $product->images ?? [];

            // Prefix 'storage/' so asset() generates the correct public URL
            $gallery = collect($images)
                ->map(fn(string $path): string => 'storage/' . $path)
                ->values()
                ->all();

            return [
                'id'             => $product->id,
                'title'          => $product->name,
                'type_label'     => $categoryName,
                'categories'     => [$categoryName],   // Alpine.js expects an array
                'specification'  => strip_tags($product->specifications ?? ''),
                'concept'        => strip_tags($product->desc ?? ''),
                'location'       => $product->location ?? '',
                'cover_image'    => filled($images[0] ?? '') ? 'storage/' . $images[0] : '',
                'gallery'        => $gallery,
            ];
        })->values()->all();

        $portfolioData = [
            'heading'  => ['eyebrow' => '', 'title' => ''],
            'tabs'     => $tabs,
            'projects' => $projects,
        ];

        $media = [
    'videos' => $banners
        ->filter(fn($b) => ($b->media['type'] ?? 
        null) === 'video')
        ->map(fn($b) => [
            // Ensure this points to the path stored in your DB (e.g., 'uploads/videos/filename.mp4')
            'src' => $b->media['path'] ?? null, 
        ])
        ->filter(fn($v) => !empty($v['src']))
        ->values()
        ->all(),

    // These keys are required by your partial's <span> tags
    'headline_prefix'   => 'Delivering thoughtful ',
    'headline_emphasis' => 'design & quality',
    'headline_suffix'   => 'construction for modern living',
    'accent'            => '.',
];


        $allWhyItems = Why::all();

        // Get the 5th item specifically
        $fifthItem = $allWhyItems->skip(4)->first();

        $differenceData = [];
        if ($fifthItem) {
            $differenceData = [
                'image'      => $fifthItem->image, // Ensure this path is correct for asset()
                'eyebrow'    => 'THE DIFFERENCE',
                'title'      => $fifthItem->title_en,
                // 'paragraphs' => [strip_tags($fifthItem->content_en)],
                'paragraphs' => [$fifthItem->content_en],
            ];
        }

        return view('frontend.home', [
            'site'          => $siteContentService->getSite(),
            'getHomePage'   => $getHomePage,
            'banners'       => $banners,
            'portfolioData' => $portfolioData,
            'whyCards'      => $whyCards,
            'media'        => $media,
            'difference' => $differenceData,
        ]);
    }
}
