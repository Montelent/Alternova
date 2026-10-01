<?php

use App\Http\Controllers\AccountExportController;
use App\Http\Controllers\AffiliateRedirectController;
use App\Http\Controllers\Api\AlternativeApiController;
use App\Http\Controllers\Api\ProsConsApiController;
use App\Http\Controllers\Api\SearchSuggestController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CollectionSubmissionStatusController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubmissionStatusController;
use App\Http\Controllers\WhatsNewController;
use App\Livewire\AccountPage;
use App\Livewire\AlternativeDetail;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\CollectionIndex;
use App\Livewire\CollectionShow;
use App\Livewire\CompareAlternatives;
use App\Livewire\ContactForm;
use App\Livewire\DomainCombinator;
use App\Livewire\FavoritesPage;
use App\Livewire\Leaderboard;
use App\Livewire\NotificationsPage;
use App\Livewire\OpenSourceFinder;
use App\Livewire\ProprietaryToolShow;
use App\Livewire\SuggestAlternative;
use App\Livewire\SuggestCollection;
use App\Livewire\TrendingPage;
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
Route::get('/leaderboard', Leaderboard::class)->name('leaderboard');
Route::get('/trending', TrendingPage::class)->name('trending');
Route::get('/collections', CollectionIndex::class)->name('collections.index');
Route::get('/collections/{slug}', CollectionShow::class)->name('collections.show');
Route::get('/alternatives/{slug}', AlternativeDetail::class)->name('alternatives.show');

Route::get('/tools/{slug}', ProprietaryToolShow::class)->name('tools.show');
Route::get('/embed/tools/{slug}', [EmbedController::class, 'tool'])->name('embed.tool');

Route::get('/og/alternative/{slug}', [OgImageController::class, 'alternative'])
    ->where('slug', '.*')
    ->name('og.alternative');
Route::get('/og/tool/{slug}', [OgImageController::class, 'tool'])
    ->where('slug', '.*')
    ->name('og.tool');
Route::get('/og/collection/{slug}', [OgImageController::class, 'collection'])
    ->where('slug', '.*')
    ->name('og.collection');
Route::get('/og/compare/{a}/{b}', [OgImageController::class, 'compare'])
    ->where('a', '[A-Za-z0-9\-_]+')
    ->where('b', '[A-Za-z0-9\-_.]+')
    ->name('og.compare');

Route::get('/whats-new', WhatsNewController::class)->name('whats-new');
Route::get('/favorites', FavoritesPage::class)->name('favorites');

Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request')->middleware('guest');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset')->middleware('guest');
Route::get('/account', AccountPage::class)->name('account');
Route::get('/notifications', NotificationsPage::class)->name('notifications');
Route::get('/account/export/favorites', [AccountExportController::class, 'favorites'])
    ->middleware('auth')
    ->name('account.export.favorites');
Route::get('/account/export/watchlist', [AccountExportController::class, 'watchlist'])
    ->middleware('auth')
    ->name('account.export.watchlist');

Route::get('/badge/{slug}/health.svg', [BadgeController::class, 'health'])->name('badge.health');

Route::get('/domains', DomainCombinator::class)->name('domains');
Route::get('/go/{provider}', AffiliateRedirectController::class)
    ->whereIn('provider', ['namecheap', 'porkbun', 'godaddy'])
    ->middleware('throttle:60,1')
    ->name('affiliate.go');

Route::get('/suggest', SuggestAlternative::class)->name('suggest');
Route::get('/suggest/collection', SuggestCollection::class)->name('suggest.collection');
Route::get('/submissions/{token}', SubmissionStatusController::class)->name('submissions.status');
Route::get('/collection-submissions/{token}', CollectionSubmissionStatusController::class)->name('collection-submissions.status');
Route::get('/contact', ContactForm::class)->name('contact');

Route::get('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/disclosure', [PageController::class, 'disclosure'])->name('disclosure');
Route::get('/p/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('pages.show');

Route::get('/feed', [FeedController::class, 'rss'])->name('feed.rss');
Route::get('/feed/rss', [FeedController::class, 'rss']);
Route::get('/feed/atom', [FeedController::class, 'atom'])->name('feed.atom');

Route::prefix('api')->middleware(['api.key', 'api.throttle'])->group(function () {
    Route::get('/alternatives', [AlternativeApiController::class, 'index'])->name('api.alternatives.index');
    Route::get('/alternatives/{slug}', [AlternativeApiController::class, 'show'])->name('api.alternatives.show');
    Route::get('/alternatives/{slug}/pros-cons', [ProsConsApiController::class, 'index'])->name('api.proscons.index');
    Route::post('/alternatives/{slug}/pros-cons', [ProsConsApiController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('api.proscons.store');
    Route::get('/suggest', SearchSuggestController::class)->name('api.suggest');
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
