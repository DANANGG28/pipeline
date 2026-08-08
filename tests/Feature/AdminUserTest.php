<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Kaldera',
            'username' => 'admin',
            'email' => 'admin@kaldera.id',
            'password' => 'admin123',
            'is_admin' => true,
        ]);

        $this->staff = User::create([
            'name' => 'Staf Gudang',
            'username' => 'staff01',
            'email' => 'staff@kaldera.id',
            'password' => 'staff01',
            'is_admin' => false,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_users_page(): void
    {
        $this->actingAs($this->staff)->get('/users')->assertForbidden();
    }

    public function test_non_admin_cannot_add_user(): void
    {
        $this->actingAs($this->staff)->post('/users', [
            'name' => 'Orang Baru',
            'username' => 'orang01',
            'email' => 'orang@kaldera.id',
            'password' => 'orang123',
            'password_confirmation' => 'orang123',
        ])->assertForbidden();
    }

    public function test_admin_can_see_users_list(): void
    {
        $this->actingAs($this->admin)
            ->get('/users')
            ->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee($this->staff->username);
    }

    public function test_admin_can_add_user(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'Orang Baru',
                'username' => 'orang01',
                'email' => 'orangep@kaldera.id',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'));

        $created = User::where('username', 'orang01')->first();
        $this->assertNotNull($created);
        $this->assertFalse($created->is_admin);
        $this->assertTrue(password_verify('password123', $created->password));
    }

    public function test_add_user_validation_requires_min_5_characters(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'O',
                'username' => 'abcd',
                'email' => 'bad-email',
                'password' => '1234',
                'password_confirmation' => '1234',
            ])
            ->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_add_user_rejects_duplicate_username_and_email(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'Duplikat',
                'username' => 'admin',
                'email' => 'staff@kaldera.id',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors(['username', 'email']);
    }

    public function test_add_user_rejects_mismatched_password_confirmation(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'Salah Konfirmasi',
                'username' => 'konfirmasi',
                'email' => 'konfirmasi@kaldera.id',
                'password' => 'password123',
                'password_confirmation' => 'password999',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_admin_can_delete_staff_user(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->staff))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $this->staff->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_delete_another_admin(): void
    {
        $otherAdmin = User::create([
            'name' => 'Admin Dua',
            'username' => 'admin02',
            'email' => 'admin2@kaldera.id',
            'password' => 'admin123',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $otherAdmin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }
}