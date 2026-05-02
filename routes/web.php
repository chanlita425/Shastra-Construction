<?php

declare(strict_types=1);

use App\Http\Controllers\Cms\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cms\ContactSubmissionController as CmsContactSubmissionController;
use App\Http\Controllers\Cms\DashboardController;
use App\Http\Controllers\admin\GlobalSettingsController;
use App\Http\Controllers\Cms\PageContentController as CmsPageContentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\StoreContactSubmissionController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\admin\WhyController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\admin\PageContentController;
use App\Http\Controllers\admin\BannerController;
use App\Http\Controllers\admin\ClientController;
use App\Http\Controllers\admin\HistoryController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\AboutBackendController;
use App\Http\Controllers\admin\GlobalSettingsController as AdminGlobalSettingsController;
use App\Http\Controllers\admin\ProductBackendController;
use App\Http\Controllers\admin\ProjectBackendController;
use App\Http\Controllers\admin\UploadController;
use App\Http\Controllers\frontend\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::get('/', HomeController::class)->name('home');
Route::get('/services', PageController::class)->defaults('page', 'services')->name('services');
Route::get('/about-us', PageController::class)->defaults('page', 'about')->name('about');
Route::get('/project', PageController::class)->defaults('page', 'projects')->name('projects');
Route::get('/templates', PageController::class)->defaults('page', 'templates')->name('templates');
Route::get('/contact', PageController::class)->defaults('page', 'contact')->name('contact');
Route::post('/contact', StoreContactSubmissionController::class)
    ->middleware('throttle:6,1')
    ->name('contact.store');

// web.php
Route::post('/contact-telegram', [ContactController::class, 'redirectToTelegram'])
    ->name('contact.telegram');


    // In your media controller / route
Route::get('/media/{path}', function (Request $request, string $path) {

    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = Storage::disk('custom');

    abort_unless($disk->exists($path), 404);

    $downloadName = $request->query('download_name', basename($path));
    $mime = $disk->mimeType($path);

    return response()->streamDownload(
        function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        },
        $downloadName,
        ['Content-Type' => $mime]
    );
})->where('path', '.*')->name('public.media.show');
// Route::prefix('cms')->name('cms.')->group(function (): void {
//     Route::middleware('guest')->group(function (): void {
//         Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
//         Route::post('/login', [AuthenticatedSessionController::class, 'store'])
//             ->middleware('throttle:6,1')
//             ->name('login.store');
//     });

// Route::middleware(['auth', 'admin'])->group(function (): void {
//     Route::get('/', DashboardController::class)->name('dashboard');
//     Route::get('/settings/global', [GlobalSettingsController::class, 'edit'])->name('settings.edit');
//     Route::put('/settings/global', [GlobalSettingsController::class, 'update'])->name('settings.update');
//     Route::get('/pages/{page}', [CmsPageContentController::class, 'edit'])->name('pages.edit');
//     Route::put('/pages/{page}', [CmsPageContentController::class, 'update'])->name('pages.update');
//     Route::get('/contact-submissions', [CmsContactSubmissionController::class, 'index'])->name('contact-submissions.index');
//     Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
// });
// });

Route::get('/google/auth', [GoogleAuthController::class, 'auth']);
Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
Route::get('/media/public/{path}', [PublicMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('public.media.show');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings/edit', [GlobalSettingsController::class, 'edit'])->name('cms.settings.edit');
    Route::put('/settings/global', [GlobalSettingsController::class, 'update'])->name('settings.update');
    Route::get('/pages/{page}', [PageContentController::class, 'edit'])
        ->whereIn('page', ['home', 'contact'])
        ->name('cms.pages.edit');
    Route::put('/pages/{page}', [PageContentController::class, 'update'])
        ->whereIn('page', ['home', 'contact'])
        ->name('cms.pages.update');

    Route::resource('banner', BannerController::class)->only(['index', 'edit', 'update']);

    Route::resource('category', CategoryController::class)->except(['destroy', 'show']);
    Route::get('category/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');

    Route::resource('about', AboutBackendController::class)->except(['destroy', 'show']);
    Route::resource('why', WhyController::class)->except(['destroy', 'show']);
    Route::post('/category/reorder', [CategoryController::class, 'reorder'])->name('category.reorder');


    Route::resource('projects', ProjectBackendController::class)
        ->parameters(['projects' => 'project_backend'])
        ->names([
            'index' => 'project_backend.index',
            'create' => 'project_backend.create',
            'store' => 'project_backend.store',
            'edit' => 'project_backend.edit',
            'update' => 'project_backend.update',
        ])
        ->except(['destroy', 'show']);
    Route::get('projects/delete/{project_backend}', [ProjectBackendController::class, 'delete'])->name('project_backend.delete');
});

require __DIR__ . '/auth.php';
