<?php

use App\Livewire\AlternativeDetail;
use App\Livewire\DomainCombinator;
use App\Livewire\InstallWizard;
use App\Livewire\OpenSourceFinder;
use App\Support\Installer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installer (only when NOT installed)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'not.installed'])->group(function () {
    Route::get('/install', InstallWizard::class)->name('install.index');
});

/*
|--------------------------------------------------------------------------
| Application routes (blocked until installed via RedirectIfNotInstalled)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/alternatives', OpenSourceFinder::class)->name('finder');
Route::get('/alternatives/{alternative:slug}', AlternativeDetail::class)->name('alternatives.show');

Route::get('/domains', DomainCombinator::class)->name('domains');

/*
|--------------------------------------------------------------------------
| Optional: force unlock in local only (safety)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/install/unlock-dev', function () {
        Installer::unlock();
        return redirect()->route('install.index')
            ->with('status', 'Installer unlocked for development.');
    })->name('install.unlock-dev');
}
