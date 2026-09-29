<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function unsubscribe(Request $request): View
    {
        $token = trim((string) $request->query('token', ''));
        $email = strtolower(trim((string) $request->query('email', '')));

        $sub = null;

        if ($token !== '') {
            $sub = NewsletterSubscriber::query()->where('unsubscribe_token', $token)->first();
        }

        if (! $sub && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $sub = NewsletterSubscriber::query()->where('email', $email)->first();
        }

        if (! $sub) {
            return view('pages.newsletter-unsubscribed', [
                'ok' => false,
                'message' => 'Invalid or missing unsubscribe link.',
            ]);
        }

        if ($sub->status === 'active') {
            $sub->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return view('pages.newsletter-unsubscribed', [
            'ok' => true,
            'message' => 'You have been unsubscribed from Alternova product updates.',
            'email' => $sub->email,
        ]);
    }
}
