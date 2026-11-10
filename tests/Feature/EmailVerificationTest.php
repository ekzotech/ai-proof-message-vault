<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_matching_account_can_use_a_valid_signed_link(): void
    {
        $author = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();
        $url = (new VerifyEmail)->toMail($author)->actionUrl;

        $this->actingAs($other)->get($url)->assertForbidden();
        $this->actingAs($author)->get($url.'x')->assertForbidden();
        $this->assertFalse($author->fresh()->hasVerifiedEmail());

        $this->get($url)->assertRedirect(route('verification.notice'));
        $this->assertTrue($author->fresh()->hasVerifiedEmail());
    }

    public function test_resending_is_throttled_and_skipped_for_verified_accounts(): void
    {
        Notification::fake();
        $author = User::factory()->unverified()->create();
        $this->actingAs($author);

        for ($request = 0; $request < 3; $request++) {
            $this->post(route('verification.send'))->assertRedirect(route('verification.notice'));
        }

        $this->post(route('verification.send'))->assertTooManyRequests();
        Notification::assertSentToTimes($author, VerifyEmail::class, 3);

        $verified = User::factory()->create();
        $this->actingAs($verified)->post(route('verification.send'))->assertRedirect(route('verification.notice'));
        Notification::assertNotSentTo($verified, VerifyEmail::class);
    }
}
