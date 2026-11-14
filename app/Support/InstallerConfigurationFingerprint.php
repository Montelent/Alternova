<?php

namespace App\\Support;

use InvalidArgumentException;

final class InstallerConfigurationFingerprint
{
    /**
     * Produce a keyed fingerprint for installer settings that were validated
     * and saved before the migration and administrator-creation steps.
     *
     * @param  array<string, scalar|null>  $configuration
     */
    public static function make(array $configuration, string $key): string
    {
        if ($key === '') {
            throw new InvalidArgumentException('An application key is required.');
        }

        $keys = [
            'app_name',
            'app_url',
            'db_connection',
            'db_host',
            'db_port',
            'db_database',
            'db_username',
            'db_password',
        ];

        $normalized = [];
        foreach ($keys as $name) {
            $value = $configuration[$name] ?? null;
            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Installer configuration values must be scalar.');
            }

            $normalized[$name] = $value === null ? null : (string) $value;
        }

        return hash_hmac(
            'sha256',
            json_encode($normalized, JSON_THROW_ON_ERROR),
            $key
        );
    }
}
