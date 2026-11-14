<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /**
     * Display a confirmation page only. GET requests must not unsubscribe
     * because mail security scanners routinely prefetch links.
     */
    public function unsubscribe(Request $request): View
    {
        $token = trim((string) ($request->route('token') ?? $request->query('token', '')));
        $sub = $token !== ''
            ? NewsletterSubscriber::query()->where('unsubscribe_token', $token)->first()
            : null;

        if (! $sub) {
            return view('pages.newsletter-unsubscribed', [
                'ok' => false,
                'confirm' => false,
                'message' => 'Invalid or missing unsubscribe link.',
            ]);
        }

        return view('pages.newsletter-unsubscribed', [
            'ok' => false,
            'confirm' => true,
            'token' => $token,
            'email' => $sub->email,
            'message' => 'Confirm that you want to unsubscribe from Alternova product updates.',
        ]);
    }

    /**
     * Complete an unsubscribe only after an explicit same-origin POST.
     */
    public function confirmUnsubscribe(Request $request): View
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:32', 'max:128'],
        ]);

        $sub = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $data['token'])
            ->first();

        if (! $sub) {
            return view('pages.newsletter-unsubscribed', [
                'ok' => false,
                'confirm' => false,
                'message' => 'Invalid or expired unsubscribe link.',
            ]);
        }

        if ($sub->status === 'active') {
            $sub->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                // Revoke the bearer link so an old email cannot unsubscribe
                // the address again after a later resubscription.
                'unsubscribe_token' => Str::random(48),
            ]);
        }

        return view('pages.newsletter-unsubscribed', [
            'ok' => true,
            'confirm' => false,
            'message' => 'You have been unsubscribed from Alternova product updates.',
            'email' => $sub->email,
        ]);
    }
}
