<?php

namespace Tests\Feature;

use App\Capsules\CapsuleState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCapsulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_setup_creates_an_isolated_account_with_all_capsule_states(): void
    {
        $this->artisan('vault:demo', ['--json' => true])->assertSuccessful();
        $author = User::where('email', 'like', 'vault-demo-%@example.test')->sole();
        $states = $author->capsules()->with('publication')->get()
            ->map(fn ($capsule) => CapsuleState::at($capsule->publication, now()))->all();

        $this->assertTrue($author->hasVerifiedEmail());
        $this->assertEqualsCanonicalizing(CapsuleState::cases(), $states);
    }

    public function test_demo_setup_is_unavailable_outside_local_and_testing(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->artisan('vault:demo')->assertFailed();
        $this->assertDatabaseCount('capsules', 0);
    }
}
