<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Livewire\SuggestAlternative;
use App\Livewire\SuggestCollection;
use App\Models\AlternativeSubmission;
use App\Models\CollectionSubmission;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSubmissionFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_validates_and_persists_a_support_ticket(): void
    {
        Mail::fake();

        $component = Livewire::test(ContactForm::class)
            ->set('name', 'A Visitor')
            ->set('email', 'visitor@example.com')
            ->set('subject', 'Product feedback')
            ->set('message', 'I would like to share some product feedback.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true)
            ->assertSet('sent', true);

        $this->assertStringStartsWith('TKT-', (string) $component->get('ticketPublicId'));
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'A Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Product feedback',
            'status' => 'unread',
            'ticket_status' => 'open',
        ]);
    }

    public function test_contact_form_rejects_invalid_input_without_creating_a_ticket(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->set('message', 'short')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_honeypot_silently_discards_a_submission(): void
    {
        Livewire::test(ContactForm::class)
            ->set('website', 'https://spam.example')
            ->call('submit')
            ->assertSet('sent', true)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_enforces_its_per_ip_submission_limit(): void
    {
        Mail::fake();
        RateLimiter::clear('contact:127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            Livewire::test(ContactForm::class)
                ->set('name', 'A Visitor')
                ->set('email', 'visitor@example.com')
                ->set('message', 'This is a valid support message.')
                ->call('submit')
                ->assertSet('sent', true)
                ->assertHasNoErrors();
        }

        Livewire::test(ContactForm::class)
            ->set('name', 'A Visitor')
            ->set('email', 'visitor@example.com')
            ->set('message', 'This is a valid support message.')
            ->call('submit')
            ->assertHasErrors('email')
            ->assertSet('sent', false);

        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_alternative_suggestion_validates_and_persists_a_pending_submission(): void
    {
        $component = Livewire::test(SuggestAlternative::class)
            ->set('submitter_name', 'A Contributor')
            ->set('submitter_email', 'contributor@example.com')
            ->set('proprietary_name', 'Commercial App')
            ->set('alternative_name', 'Open App')
            ->set('repo_url', 'https://github.com/example/open-app')
            ->set('website_url', 'https://open-app.example')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSet('submitted', true);

        $this->assertSame(40, strlen((string) $component->get('trackingToken')));
        $this->assertDatabaseHas('alternative_submissions', [
            'proprietary_name' => 'Commercial App',
            'alternative_name' => 'Open App',
            'repo_url' => 'https://github.com/example/open-app',
            'status' => 'pending',
        ]);
        $this->assertNotNull(AlternativeSubmission::query()->latest('id')->value('tracking_token'));
    }

    public function test_alternative_suggestion_honeypot_silently_discards_a_submission(): void
    {
        Livewire::test(SuggestAlternative::class)
            ->set('website', 'https://spam.example')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('alternative_submissions', 0);
    }

    public function test_collection_suggestion_validates_and_persists_normalized_alternative_slugs(): void
    {
        $component = Livewire::test(SuggestCollection::class)
            ->set('submitter_name', 'A Contributor')
            ->set('submitter_email', 'contributor@example.com')
            ->set('title', 'Developer Tools')
            ->set('description', 'A curated set of developer tools.')
            ->set('alternative_slugs_text', "outline, appflowy outline")
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSet('submitted', true);

        $this->assertStringContainsString('/collection-submission-status/', (string) $component->get('trackingUrl'));
        $submission = CollectionSubmission::query()->latest('id')->first();

        $this->assertNotNull($submission);
        $this->assertSame('pending', $submission->status);
        $this->assertSame('developer-tools', $submission->proposed_slug);
        $this->assertSame(['outline', 'appflowy'], $submission->alternative_slugs);
    }

    public function test_collection_suggestion_requires_two_distinct_alternative_slugs(): void
    {
        Livewire::test(SuggestCollection::class)
            ->set('title', 'Developer Tools')
            ->set('alternative_slugs_text', 'outline outline')
            ->call('submit')
            ->assertHasErrors('alternative_slugs_text');

        $this->assertDatabaseCount('collection_submissions', 0);
    }

    public function test_collection_suggestion_honeypot_silently_discards_a_submission(): void
    {
        Livewire::test(SuggestCollection::class)
            ->set('website', 'https://spam.example')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('collection_submissions', 0);
    }
}
