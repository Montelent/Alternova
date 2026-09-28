<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'title' => 'About Alternova',
            'description' => 'Learn about Alternova — a free directory of open-source software alternatives and a brandable domain name idea generator.',
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'title' => 'Privacy Policy — Alternova',
            'description' => 'Privacy policy for Alternova: how we handle data, cookies, and third-party services including advertising.',
        ]);
    }

    public function terms(): View
    {
        return view('pages.terms', [
            'title' => 'Terms of Use — Alternova',
            'description' => 'Terms of use for the Alternova website and tools.',
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'title' => 'Contact — Alternova',
            'description' => 'Contact the Alternova team for feedback, corrections, or partnership inquiries.',
        ]);
    }
}
