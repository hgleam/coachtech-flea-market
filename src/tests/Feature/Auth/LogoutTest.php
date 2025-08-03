<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

/**
 * ログアウトのテスト
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.3: ログアウト機能
     * ログアウトができる
     */
    public function test_user_can_logout()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}