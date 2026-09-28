<?php

use App\Http\Controllers\Api\AlternativeApiController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Livewire\AlternativeDetail;
use App\Livewire\CompareAlternatives;
use App\Livewire\ContactForm;
use App\Livewire\DomainCombinator;
use App\Livewire\OpenSourceFinder;
use App\Livewire\ProprietaryToolShow;
use App\Livewire\SuggestAlternative;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'not.installed'])->prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::post('/next', [InstallController::class, 'next'])->name('install.next');
    Route::post('/back', [InstallController::class, 'back'])->name('install.back');
    Route::post('/recheck', [InstallController::class, 'recheck'])->name('install.recheck');
});

Route::get('/', HomeController::class)->name('home');

Route::get('/alternatives', OpenSourceFinder::class)->name('finder');
Route::get('/alternatives/compare', CompareAlternatives::class)->name('alternatives.compare');
Route::get('/alternatives/{alternative}', AlternativeDetail::class)->name('alternatives.show');

Route::get('/tools/{tool}', ProprietaryToolShow::class)->name('tools.show');

Route::get('/domains', DomainCombinator::class)->name('domains');
Route::get('/suggest', SuggestAlternative::class)->name('suggest');
Route::get('/contact', ContactForm::class)->name('contact');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/disclosure', [PageController::class, 'disclosure'])->name('disclosure');

Route::get('/feed', [FeedController::class, 'rss'])->name('feed.rss');
Route::get('/feed/rss', [FeedController::class, 'rss']);
Route::get('/feed/atom', [FeedController::class, 'atom'])->name('feed.atom');

Route::prefix('api')->group(function () {
    Route::get('/alternatives', [AlternativeApiController::class, 'index'])->name('api.alternatives.index');
    Route::get('/alternatives/{slug}', [AlternativeApiController::class, 'show'])->name('api.alternatives.show');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/ads.txt', function () {
    $path = public_path('ads.txt');
    abort_unless(is_file($path), 404);

    return response(file_get_contents($path), 200)->header('Content-Type', 'text/plain');
});

Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');
    $base = rtrim(config('app.url'), '/');
    $body = is_file($path) ? file_get_contents($path) : "User-agent: *\nAllow: /\n";
    $body = preg_replace('/^Sitemap:.*$/m', 'Sitemap: '.$base.'/sitemap.xml', $body);
    if (! str_contains($body, 'Sitemap:')) {
        $body = rtrim($body)."\n\nSitemap: {$base}/sitemap.xml\n";
    }

    return response($body, 200)->header('Content-Type', 'text/plain');
});
