<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'mecanicien'): User
    {
        return User::create([
            'name'     => 'Kofi Mensah',
            'email'    => "{$role}@test.local",
            'role'     => $role,
            'password' => Hash::make('ancien-mdp-1'),
        ]);
    }

    public function test_theme_defaults_to_system(): void
    {
        $this->assertSame('system', $this->user()->fresh()->theme);
    }

    public function test_every_role_can_open_settings(): void
    {
        foreach (['patron', 'accueil', 'mecanicien'] as $role) {
            $this->actingAs($this->user($role))
                ->get('/parametres')
                ->assertOk()
                ->assertSee('Apparence');
        }
    }

    public function test_theme_is_saved_as_json(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->patchJson('/parametres/theme', ['theme' => 'dark'])
            ->assertOk()
            ->assertJson(['theme' => 'dark']);

        $this->assertSame('dark', $user->fresh()->theme);
    }

    public function test_theme_rejects_unknown_values(): void
    {
        $this->actingAs($this->user())
            ->patchJson('/parametres/theme', ['theme' => 'rose'])
            ->assertUnprocessable();
    }

    public function test_saved_theme_is_applied_on_next_page(): void
    {
        $user = $this->user('patron');
        $user->update(['theme' => 'light']);

        $this->actingAs($user)->get('/parametres')->assertSee('var pref = "light"', false);
    }

    public function test_profile_can_be_updated_but_not_the_role(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->patch('/parametres/profil', ['name' => 'Kofi A.', 'email' => 'kofi@test.local', 'phone' => '+229 1', 'role' => 'patron'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Kofi A.', $user->name);
        $this->assertSame('mecanicien', $user->role);
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put('/parametres/mot-de-passe', ['current_password' => 'faux', 'password' => 'nouveau-mdp-2', 'password_confirmation' => 'nouveau-mdp-2'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put('/parametres/mot-de-passe', ['current_password' => 'ancien-mdp-1', 'password' => 'nouveau-mdp-2', 'password_confirmation' => 'nouveau-mdp-2'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nouveau-mdp-2', $user->fresh()->password));
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Adresse e-mail');
    }
}
