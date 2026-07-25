<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->get('/admin/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->post('/admin/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile'));

        $email = $user->email;
        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame($email, $user->email);
    }

    public function test_profile_update_requires_first_name_and_last_name(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->from('/admin/profile')
            ->post('/admin/profile', [
                'first_name' => '',
                'last_name' => '',
            ]);

        $response
            ->assertSessionHasErrors(['first_name', 'last_name'])
            ->assertRedirect('/admin/profile');
    }
}
