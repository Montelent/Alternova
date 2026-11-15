<?php

namespace Tests\Unit;

use App\Support\SlugAutofill;
use PHPUnit\Framework\TestCase;

class SlugAutofillTest extends TestCase
{
    public function test_slug_is_generated_from_name_when_creating_a_record(): void
    {
        self::assertSame(
            'network-wifi',
            SlugAutofill::fromName('Network & WiFi', null, null, 'create')
        );
    }

    public function test_slug_tracks_name_changes_until_manually_overridden_during_creation(): void
    {
        self::assertSame(
            'network-and-wifi',
            SlugAutofill::fromName('Network and WiFi', 'network-wifi', 'Network & WiFi', 'create')
        );

        self::assertNull(
            SlugAutofill::fromName('Network and WiFi', 'custom-network', 'Network & WiFi', 'create')
        );
    }

    public function test_existing_slug_is_never_automatically_changed_during_editing(): void
    {
        self::assertNull(
            SlugAutofill::fromName('Renamed Category', 'net-wifi', 'Network & WiFi', 'edit')
        );
    }

    public function test_blank_or_untranslatable_names_do_not_generate_slugs(): void
    {
        self::assertNull(SlugAutofill::fromName('  ', null, null, 'create'));
        self::assertNull(SlugAutofill::fromName('!!!', null, null, 'create'));
    }
}
