<?php

namespace Tests\Feature;

use App\Actions\ChangeUsername;
use App\Classes\IPLookup;
use App\Models\Admin;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Services\UsernamePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UsernameLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_username_normalization_and_blacklist_are_canonical_and_exact(): void
    {
        DB::table('settings')->where('key', 'username')->update([
            'value' => json_encode([
                'blacklisted_usernames' => ' Admin, @SUPPORT, help, admin, , Diget ',
            ]),
        ]);

        $policy = app(UsernamePolicy::class);

        $this->assertSame('selamstudio', $policy->normalize('  @SelamStudio  '));
        $this->assertSame(['admin', 'support', 'help', 'diget'], $policy->blacklist()->all());
        $this->assertTrue($policy->isBlacklisted('@SUPPORT'));
        $this->assertFalse($policy->isBlacklisted('supportcreative'));
        $this->assertTrue($policy->isSystemReserved('CHECKOUT'));
        $this->assertSame('', $policy->normalize('  @  '));
        $this->assertNotEmpty($policy->errors('@'));
        $this->assertNotEmpty($policy->errors('bad/name'));

        DB::table('settings')->where('key', 'username')->update([
            'value' => json_encode(['blacklisted_usernames' => null]),
        ]);
        $this->assertSame([], $policy->blacklist()->all());

        DB::table('settings')->where('key', 'username')->update([
            'value' => json_encode(['blacklisted_usernames' => '  ,   ']),
        ]);
        $this->assertSame([], $policy->blacklist()->all());
    }

    public function test_successful_change_records_history_and_enforces_exact_thirty_day_boundary(): void
    {
        Carbon::setTestNow('2026-07-22 12:00:00');
        $user = $this->createUser('firstone');
        $action = app(ChangeUsername::class);

        $history = $action->execute($user, ' @SecondOne ', $user);

        $this->assertNotNull($history);
        $this->assertSame('firstone', $history->old_username);
        $this->assertSame('secondone', $history->new_username);
        $this->assertSame('secondone', $user->fresh()->username);

        Carbon::setTestNow('2026-08-21 11:59:59');
        try {
            $action->execute($user->fresh(), 'thirdone', $user);
            $this->fail('The cooldown should block one second before the boundary.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('username', $e->errors());
        }

        $this->assertSame(1, UsernameHistory::where('user_id', $user->id)->count());

        Carbon::setTestNow('2026-08-21 12:00:00');
        $action->execute($user->fresh(), 'thirdone', $user);

        $this->assertSame('thirdone', $user->fresh()->username);
        $this->assertSame(2, UsernameHistory::where('user_id', $user->id)->count());
    }

    public function test_no_op_and_failed_change_do_not_create_history_or_start_cooldown(): void
    {
        $user = $this->createUser('sameuser');
        $action = app(ChangeUsername::class);

        $this->assertNull($action->execute($user, ' @SAMEUSER ', $user));
        $this->assertSame(0, UsernameHistory::where('user_id', $user->id)->count());

        try {
            $action->execute($user, 'admin', $user);
            $this->fail('A reserved username should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('username', $e->errors());
        }

        $this->assertSame('sameuser', $user->fresh()->username);
        $this->assertSame(0, UsernameHistory::where('user_id', $user->id)->count());
        $this->assertTrue(app(UsernamePolicy::class)->canChange($user));
    }

    public function test_historical_username_redirects_directly_and_cannot_be_reassigned(): void
    {
        $user = $this->createUser('oldname');
        app(ChangeUsername::class)->execute($user, 'newname', $user);

        $response = $this->get('/@oldname?campaign=launch');
        $response->assertStatus(301);
        $response->assertRedirect(route('profile.index', 'newname') . '?campaign=launch');

        $this->get('/@oldname/portfolio?sort=latest')
            ->assertStatus(301)
            ->assertRedirect(route('profile.portfolio', 'newname') . '?sort=latest');

        $other = $this->createUser('otherone');
        $this->expectException(ValidationException::class);
        app(ChangeUsername::class)->execute($other, 'oldname', $other);
    }

    public function test_every_historical_username_redirects_directly_to_the_latest_name(): void
    {
        Carbon::setTestNow('2026-07-01 08:00:00');
        $user = $this->createUser('nameone');
        $action = app(ChangeUsername::class);
        $action->execute($user, 'nametwo', $user);

        Carbon::setTestNow('2026-07-31 08:00:00');
        $action->execute($user->fresh(), 'namethree', $user);

        $this->get('/@nameone')->assertRedirect(route('profile.index', 'namethree'), 301);
        $this->get('/@nametwo')->assertRedirect(route('profile.index', 'namethree'), 301);
    }

    public function test_admin_correction_is_audited_and_bypasses_only_the_cooldown(): void
    {
        $user = $this->createUser('adminold');
        app(ChangeUsername::class)->execute($user, 'adminmid', $user);

        $admin = Admin::create([
            'firstname' => 'Site',
            'lastname' => 'Admin',
            'username' => 'siteadmin',
            'email' => 'siteadmin@example.test',
            'password' => bcrypt('Password123!'),
        ]);

        $history = app(ChangeUsername::class)->execute(
            $user->fresh(),
            'adminnew',
            $admin,
            'admin',
            true,
        );

        $this->assertSame(Admin::class, $history->actor_type);
        $this->assertSame($admin->id, $history->actor_id);
        $this->assertSame('admin', $history->source);

        $other = $this->createUser('adminother');
        $this->expectException(ValidationException::class);
        app(ChangeUsername::class)->execute($other, 'adminold', $admin, 'admin', true);
    }

    public function test_historical_route_does_not_reveal_an_inactive_profile(): void
    {
        $user = $this->createUser('hiddenone');
        app(ChangeUsername::class)->execute($user, 'hiddennew', $user);
        $user->update(['status' => User::STATUS_BANNED]);

        $this->get('/@hiddenone')->assertNotFound();
    }

    public function test_account_username_change_requires_current_password(): void
    {
        $user = $this->createUser('secureone');

        $this->actingAs($user)
            ->post(route('workspace.settings.username.update'), [
                'username' => 'securetwo',
                'current_password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('secureone', $user->fresh()->username);
        $this->assertDatabaseCount('username_histories', 0);

        $this->actingAs($user)
            ->post(route('workspace.settings.username.update'), [
                'username' => '@SecureTwo',
                'current_password' => 'Password123!',
            ])
            ->assertRedirect(route('workspace.settings.index'));

        $this->assertSame('securetwo', $user->fresh()->username);
        $this->assertDatabaseHas('username_histories', [
            'user_id' => $user->id,
            'old_username' => 'secureone',
            'new_username' => 'securetwo',
            'source' => 'account',
        ]);
    }

    public function test_registration_uses_the_same_policy_and_stores_without_at_prefix(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\Trustip::class);
        $this->configureRegistration('blockedname');
        $this->mockIpLookup();

        $this->post('/register', [
            'firstname' => 'New',
            'lastname' => 'Creator',
            'username' => '@BlockedName',
            'email' => 'blocked@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.test']);

        $this->post('/register', [
            'firstname' => 'New',
            'lastname' => 'Creator',
            'username' => ' @FreshCreator ',
            'email' => 'fresh@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'freshcreator',
            'email' => 'fresh@example.test',
        ]);
        $this->assertDatabaseMissing('username_histories', ['new_username' => 'freshcreator']);

        auth()->logout();

        $this->post('/register', [
            'firstname' => 'Duplicate',
            'lastname' => 'Creator',
            'username' => '@FRESHCREATOR',
            'email' => 'duplicate@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('username');
    }

    public function test_registration_rejects_a_historical_username(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\Trustip::class);
        $this->configureRegistration();
        $this->mockIpLookup();

        $user = $this->createUser('formername');
        app(ChangeUsername::class)->execute($user, 'currentname', $user);

        $this->post('/register', [
            'firstname' => 'History',
            'lastname' => 'Claim',
            'username' => '@FormerName',
            'email' => 'history-claim@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', ['email' => 'history-claim@example.test']);
    }

    public function test_database_constraint_classifier_distinguishes_username_and_email_races(): void
    {
        $user = $this->createUser('constraintone');
        $policy = app(UsernamePolicy::class);

        try {
            User::create([
                'firstname' => 'Duplicate',
                'lastname' => 'Username',
                'username' => 'CONSTRAINTONE',
                'email' => 'unique-email@example.test',
                'password' => bcrypt('Password123!'),
            ]);
            $this->fail('The database should reject a case-variant username duplicate.');
        } catch (QueryException $e) {
            $this->assertTrue($policy->isUsernameConstraintViolation($e));
        }

        try {
            User::create([
                'firstname' => 'Duplicate',
                'lastname' => 'Email',
                'username' => 'constrainttwo',
                'email' => $user->email,
                'password' => bcrypt('Password123!'),
            ]);
            $this->fail('The database should reject a duplicate email.');
        } catch (QueryException $e) {
            $this->assertFalse($policy->isUsernameConstraintViolation($e));
        }
    }

    private function configureRegistration(string $blacklist = ''): void
    {
        DB::table('settings')->updateOrInsert([
            'key' => 'actions',
        ], [
            'value' => json_encode([
                'registration' => true,
                'email_verification' => false,
                'force_ssl' => false,
            ]),
        ]);
        DB::table('settings')->updateOrInsert(['key' => 'links'], ['value' => json_encode([])]);
        DB::table('settings')->where('key', 'username')->update([
            'value' => json_encode(['blacklisted_usernames' => $blacklist]),
        ]);
    }

    private function mockIpLookup(): void
    {
        $this->mock(IPLookup::class, function ($mock) {
            $mock->shouldReceive('lookup')->andReturn((object) [
                'ip' => '127.0.0.1',
                'country' => 'Unknown',
                'country_code' => 'Unknown',
                'timezone' => 'UTC',
                'location' => 'Unknown',
                'latitude' => '0',
                'longitude' => '0',
                'currency' => 'USD',
            ]);
        });
    }

    private function createUser(string $username): User
    {
        return User::create([
            'firstname' => 'Test',
            'lastname' => 'Creator',
            'username' => $username,
            'email' => $username . '@example.test',
            'password' => bcrypt('Password123!'),
            'is_author' => User::AUTHOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }
}
