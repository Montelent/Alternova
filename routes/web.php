<?php

use App\Livewire\AlternativeDetail;
use App\Livewire\DomainCombinator;
use App\Livewire\OpenSourceFinder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/alternatives', OpenSourceFinder::class)->name('finder');
Route::get('/alternatives/{alternative:slug}', AlternativeDetail::class)->name('alternatives.show');

Route::get('/domains', DomainCombinator::class)->name('domains');
