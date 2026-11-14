<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

class UserAuthorizationTest extends TestCase
{
    public function test_legacy_null_role_never_grants_system_access(): void
    {
        $user = $this->makeUser(null, true, true);

        self::assertFalse($user->isAdmin());
        self::assertFalse($user->canManageSystem());
        self::assertFalse($user->canManageUsers());
    }

    public function test_member_role_never_grants_system_access(): void
    {
        $user = $this->makeUser(User::ROLE_MEMBER, true, true);

        self::assertFalse($user->canManageSystem());
        self::assertFalse($user->canManageUsers());
    }

    public function test_only_active_verified_admin_can_manage_system(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, true, true);
        self::assertTrue($admin->canManageSystem());
        self::assertTrue($admin->canManageUsers());

        $unverified = $this->makeUser(User::ROLE_ADMIN, true, false);
        self::assertFalse($unverified->canManageSystem());

        $disabled = $this->makeUser(User::ROLE_ADMIN, false, true);
        self::assertFalse($disabled->canManageSystem());
    }

    public function test_api_access_fails_closed_when_schema_cannot_be_verified(): void
    {
        $schema = \Mockery::mock();
        $schema->shouldReceive('hasColumn')
            ->once()
            ->with('users', 'api_enabled')
            ->andThrow(new \RuntimeException('Database unavailable'));
        Schema::swap($schema);

        $user = new User();

        self::assertFalse($user->hasApiAccess());
    }

    public function test_api_access_fails_closed_when_schema_column_is_missing(): void
    {
        $schema = \\Mockery::mock();
        $schema->shouldReceive('hasColumn')
            ->once()
            ->with('users', 'api_enabled')
            ->andReturn(false);
        Schema::swap($schema);

        self::assertFalse((new User())->hasApiAccess());
    }

    public function test_missing_active_flag_does_not_grant_active_status(): void
    {
        self::assertFalse((new User())->isActive());
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstance('db.schema');
        parent::tearDown();
    }

    private function makeUser(?string $role, bool $active, bool $verified): User
    {
        return new class($role, $active, $verified) extends User
        {
            public function __construct(
                private ?string $testRole,
                private bool $testActive,
                private bool $testVerified,
            ) {}

            public function getAttribute($key)
            {
                return match ($key) {
                    'role' => $this->testRole,
                    'is_active' => $this->testActive,
                    'email_verified_at' => $this->testVerified ? 'verified' : null,
                    default => parent::getAttribute($key),
                };
            }
        };
    }
}
