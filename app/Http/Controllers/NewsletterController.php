<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function unsubscribe(Request $request): View
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return view('pages.newsletter-unsubscribed', [
                'ok' => false,
                'message' => 'Invalid or missing email address.',
            ]);
        }

        $sub = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($sub && $sub->status === 'active') {
            $sub->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return view('pages.newsletter-unsubscribed', [
            'ok' => true,
            'message' => 'You have been unsubscribed from Alternova product updates.',
            'email' => $email,
        ]);
    }
}
