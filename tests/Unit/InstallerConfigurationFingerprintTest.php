<?php

namespace Tests\Unit;

use App\Support\InstallerConfigurationFingerprint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InstallerConfigurationFingerprintTest extends TestCase
{
    private function configuration(): array
    {
        return [
            'app_name' => 'Alternova',
            'app_url' => 'https://example.test',
            'db_connection' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_database' => 'alternova',
            'db_username' => 'alternova_user',
            'db_password' => 'secret-value',
        ];
    }

    public function test_same_validated_configuration_has_a_stable_fingerprint(): void
    {
        $configuration = $this->configuration();

        self::assertSame(
            InstallerConfigurationFingerprint::make($configuration, 'test-app-key'),
            InstallerConfigurationFingerprint::make($configuration, 'test-app-key')
        );
    }

    public function test_every_site_and_database_setting_is_bound_to_the_fingerprint(): void
    {
        $original = $this->configuration();
        $fingerprint = InstallerConfigurationFingerprint::make($original, 'test-app-key');

        foreach (array_keys($original) as $field) {
            $changed = $original;
            $changed[$field] = (string) $changed[$field].'-tampered';

            self::assertNotSame(
                $fingerprint,
                InstallerConfigurationFingerprint::make($changed, 'test-app-key'),
                $field.' must be included in the installer integrity fingerprint.'
            );
        }
    }

    public function test_fingerprint_requires_a_non_empty_application_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InstallerConfigurationFingerprint::make($this->configuration(), '');
    }

    public function test_fingerprint_rejects_nested_configuration_values(): void
    {
        $configuration = $this->configuration();
        $configuration['db_host'] = ['unexpected'];

        $this->expectException(InvalidArgumentException::class);

        InstallerConfigurationFingerprint::make($configuration, 'test-app-key');
    }
}
