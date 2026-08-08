<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): void
    {
        $this->seed();
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_is_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Kaldera Admin')->assertSee('username');
    }

    public function test_authenticated_user_visiting_login_is_redirected(): void
    {
        $this->admin();
        $this->actingAs(User::where('username', 'admin')->first())
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_username_and_password_must_be_at_least_5_characters(): void
    {
        $this->admin();

        $this->post('/login', ['username' => 'abcd', 'password' => 'abcd'])
            ->assertSessionHasErrors(['username', 'password']);

        $this->post('/login', ['username' => 'abcd', 'password' => 'abcdef'])
            ->assertSessionHasErrors('username')
            ->assertSessionDoesntHaveErrors('password');
    }

    public function test_login_with_wrong_credentials_fails(): void
    {
        $this->admin();
        $this->post('/login', ['username' => 'admin', 'password' => 'salah123'])
            ->assertSessionHasErrors('username');
    }

    public function test_login_success_and_logout(): void
    {
        $this->admin();
        $this->followingRedirects()
            ->post('/login', ['username' => 'admin', 'password' => 'admin123'])
            ->assertOk()
            ->assertSee('Dashboard');

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}