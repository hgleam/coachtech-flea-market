<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Providers\RouteServiceProvider;

/**
 * 会員登録のテスト
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.1: 会員登録機能
     * 名前が入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required_for_registration()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => '',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'username' => 'ユーザー名を入力してください',
        ]);
    }

    /**
     * No.1: 会員登録機能
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_registration()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => 'testuser',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * No.1: 会員登録機能
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_registration()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * No.1: 会員登録機能
     * パスワードが7文字以下の場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_at_least_7_characters()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'pass',
                'password_confirmation' => 'pass',
            ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * No.1: 会員登録機能
     * パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_confirmed()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /**
     * No.1: 会員登録機能
     * 全ての項目が入力されている場合、会員情報が登録され、ログイン画面に遷移される
     */
    public function test_user_can_register()
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'testuser',
        ]);
    }
}