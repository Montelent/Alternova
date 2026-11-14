<?php

namespace Tests\\Unit;

use App\\Models\\User;
use PHPUnit\\Framework\\TestCase;

class UserAuthorizationTest extends TestCase
{
    public function test_legacy_null_role_never_grants_system_access(): void
    {
        $user = new User();
        $user->forceFill([
            'role' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        self::assertFalse($user->isAdmin());
        self::assertFalse($user->canManageSystem());
        self::assertFalse($user->canManageUsers());
    }

    public function test_member_role_never_grants_system_access(): void
    {
        $user = new User();
        $user->forceFill([
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        self::assertFalse($user->canManageSystem());
        self::assertFalse($user->canManageUsers());
    }

    public function test_only_active_verified_admin_can_manage_system(): void
    {
        $admin = new User();
        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        self::assertTrue($admin->canManageSystem());
        self::assertTrue($admin->canManageUsers());

        $unverified = new User();
        $unverified->forceFill([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        self::assertFalse($unverified->canManageSystem());

        $disabled = new User();
        $disabled->forceFill([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
            'email_verified_at' => now(),
        ]);

        self::assertFalse($disabled->canManageSystem());
    }
}
