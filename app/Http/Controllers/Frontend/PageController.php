<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Why;
use App\Services\SiteContentService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PageController extends Controller
{
    public function __invoke(string $page, SiteContentService $siteContentService): View
    {
        $banners = Banner::all();
        
        $site = $siteContentService->getSite();
        $pageConfig = $siteContentService->getPage($page);

        if (! is_array($pageConfig)) {
            throw new NotFoundHttpException;
        }

        $view = data_get($pageConfig, 'view', 'frontend.page');

        if (! is_string($view) || $view === '') {
            $view = 'frontend.page';
        }

        // For the About page, enrich config with live data from the abouts table
        if ($page === 'about') {
            $records    = About::all()->keyBy('section_key');
            $pageConfig = $this->enrichAboutPage($pageConfig, $records);

            // about.blade.php reads portfolio from $site['pages']['projects']['portfolio']
            // Inject DB portfolio there so it shows live products instead of static config
            $dbPortfolio = $this->buildPortfolioFromDb();
            data_set($site, 'pages.projects.portfolio', $dbPortfolio);
        }

        // For the Projects page, replace static portfolio with DB products
        if ($page === 'projects') {
            $pageConfig['portfolio'] = $this->buildPortfolioFromDb();
        }

        // For the Services page, use Why DB items for cards + DB portfolio
        if ($page === 'services') {
            $whyCards = Why::all()->take(4)->map(fn (Why $why): array => [
                'icon'        => filled($why->image) ? asset('storage/' . $why->image) : null,
                'icon_tone'   => null,
                'title'       => $why->title_en ?? '',
                'description' => filled($why->content_en) ? strip_tags($why->content_en) : '',
                'theme'       => 'dark',
                'cta_label'   => 'Find Out More',
            ])->all();

            if (! empty($whyCards)) {
                $pageConfig['cards'] = $whyCards;
            }

            // services.blade.php reads portfolio from $site['pages']['projects']['portfolio']
            data_set($site, 'pages.projects.portfolio', $this->buildPortfolioFromDb());
        }

        
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

            $media = [
                'videos' => $banners
                    ->filter(fn($b) => ($b->media['type'] ?? null) === 'video')
                    ->map(fn($b) => [
                        // Ensure this points to the path stored in your DB (e.g., 'uploads/videos/filename.mp4')
                        'src' => $b->media['path'] ?? null, 
                    ])
                    ->filter(fn($v) => !empty($v['src']))
                    ->values()
                    ->all(),

                // These keys are required by your partial's <span> tags
                'headline_prefix'   => 'Delivering thoughtful design & quality',
                'headline_emphasis' => '',
                'headline_suffix'   => 'construction for modern living.',
                'accent'            => '',
            ];

        return view($view, [
            'site' => $site,
            'page' => $pageConfig,
            'differenceData' => $differenceData,
                 'media'        => $media,
        ]);
    }

    // -------------------------------------------------------------------------
    // Portfolio: same mapping as HomeController but fetches ALL products
    // -------------------------------------------------------------------------

    private function buildPortfolioFromDb(): array
    {
        $categories = Category::orderBy('order')->get();
        $products   = Product::with('category')->latest()->get();

        $tabs = $categories->pluck('name_en')->filter()->values()->all();

        $projects = $products->map(function (Product $product): array {
    $categoryName = $product->category?->name_en ?? 'Uncategorized';
    $images = $product->images ?? [];

    // Map the paths once here
    $gallery = collect($images)
    ->map(function (string $path, int $index) use ($product) {
        return [
            'src'  => asset('storage/' . $path),
            'type' => 'image',
            'alt'  => $product->name . ' image ' . ($index + 1),
        ];
    })
    ->values()
    ->all();

    return [
        'id'            => $product->id,
        'title'         => $product->name,
        'type_label'    => $categoryName,
        'categories'    => [$categoryName],
        'specification' => strip_tags($product->specifications ?? ''),
        'concept'       => strip_tags($product->desc ?? ''),
        'location'      => $product->location ?? '',
        // Use the first item from our already-prefixed gallery array
        'cover_image' => $gallery[0]['src'] ?? '', 
        'gallery'       => $gallery,
    ];
})->values()->all();

        return [
            'heading'  => ['eyebrow' => 'OUR PORTFOLIOS', 'title' => ''],
            'tabs'     => $tabs,
            'projects' => $projects,
        ];
    }

    // -------------------------------------------------------------------------
    // About page enrichment — maps abouts table records into the same structure
    // that about.blade.php already expects, falling back to config if DB is empty
    // -------------------------------------------------------------------------

    private function enrichAboutPage(array $config, $records): array
    {
        // --- Story (overview section) ---
        $overview = $records->get('overview');
        if ($overview) {
            $story = $config['story'] ?? [];

            if (filled($overview->title_en)) {
                $story['title'] = $overview->title_en;
            }

            $paragraphs = array_values(array_filter(array_map(
                fn (?string $v): string => strip_tags($v ?? ''),
                [
                    $overview->content1_en,
                    $overview->content2_en,
                    $overview->content3_en,
                    $overview->content4_en,
                    $overview->content5_en,
                ]
            )));
            if (! empty($paragraphs)) {
                $story['paragraphs'] = $paragraphs;
            }

            if (filled($overview->image_path)) {
                $story['background_image'] = route('public.media.show', ['path' => $overview->image_path]);
                unset($story['background_video']); // DB image takes priority over default video
            }

            $config['story'] = $story;
        }

        // --- Company Profile (PDF download) ---
        $companyProfile = $records->get('company-profile');
        if ($companyProfile && filled($companyProfile->pdf_path)) {
            $config['story']['download_cta'] = array_merge($config['story']['download_cta'] ?? [], [
                'available' => true,
                'href'      => route('public.media.show', [
                    'path' => $companyProfile->pdf_path,
                    'download_name' => 'Shastra_Home_Company_Profile.pdf'
                ]),
            ]);
        }

        // --- Philosophy: Mission ---
        $mission = $records->get('mission');
        if ($mission) {
            if (filled($mission->title_en)) {
                $config['philosophy']['mission']['title'] = $mission->title_en;
            }
            if (filled($mission->content1_en)) {
                $config['philosophy']['mission']['description'] = strip_tags($mission->content1_en);
            }
        }

        // --- Philosophy: Vision ---
        $vision = $records->get('vision');
        if ($vision) {
            if (filled($vision->title_en)) {
                $config['philosophy']['vision']['title'] = $vision->title_en;
            }
            if (filled($vision->content1_en)) {
                $config['philosophy']['vision']['description'] = strip_tags($vision->content1_en);
            }
        }

        // --- Core Values: header ---
        $coreValuesHeader = $records->get('core-values');
        if ($coreValuesHeader) {
            if (filled($coreValuesHeader->title_en)) {
                $config['core_values']['title'] = $coreValuesHeader->title_en;
            }
            if (filled($coreValuesHeader->content1_en)) {
                $config['core_values']['intro'] = strip_tags($coreValuesHeader->content1_en);
            }
        }

        // --- Core Values: individual items ---
        $itemKeys = ['reliability', 'quality-assurance', 'elevated-standards', 'long-term-value'];
        $dbItems  = [];

        foreach ($itemKeys as $key) {
            /** @var About|null $record */
            $record = $records->get($key);
            if (! $record) {
                continue;
            }

            $item = [
                'title'       => $record->title_en ?? '',
                'description' => filled($record->content1_en) ? strip_tags($record->content1_en) : '',
            ];

            if (filled($record->image_path)) {
                $item['icon'] = route('public.media.show', ['path' => $record->image_path]);
            }

            $dbItems[] = $item;
        }

        if (! empty($dbItems)) {
            $config['core_values']['items'] = $dbItems;
        }

        // --- Founder ---
        $founder = $records->get('founder');
        if ($founder) {
            if (filled($founder->title_en)) { $config['founder']['name'] = $founder->title_en; }
            if (filled($founder->title_km)) { $config['founder']['role'] = $founder->title_km; }

            if (filled($founder->image_path)) {
                $config['founder']['image'] = route('public.media.show', ['path' => $founder->image_path]);
            }

            // English-only: collect only _en content fields
            $statements = [];
            for ($i = 1; $i <= 5; $i++) {
                $enField = "content{$i}_en";
                if (filled($founder->$enField)) {
                    $statements[] = ['lang' => 'en', 'text' => strip_tags($founder->$enField)];
                }
            }

            if (! empty($statements)) {
                $config['founder']['statements'] = $statements;
            }
        }

        return $config;
    }
}
