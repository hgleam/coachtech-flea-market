<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Providers\RouteServiceProvider;

/**
 * ログインのテスト
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.2: ログイン機能
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_login()
    {
        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * No.2: ログイン機能
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_login()
    {
        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => '',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * No.2: ログイン機能
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function test_user_cannot_login_with_incorrect_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * No.2: ログイン機能
     * 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function test_user_can_login_with_correct_credentials()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticatedAs($user);
    }
}