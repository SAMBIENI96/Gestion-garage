<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Parcours de connexion / déconnexion de chaque rôle depuis la page d'accueil
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => "{$role}@test.local", 'role' => $role,
            'password' => Hash::make('secret-123'), 'is_active' => true, 'approved_at' => now(),
        ]);
    }

    public function test_patron_logs_in_to_the_dashboard_and_logs_out_to_home(): void
    {
        $this->user('patron');

        $this->post('/login', ['email' => 'patron@test.local', 'password' => 'secret-123'])->assertRedirect('/');
        $this->get('/')->assertOk()->assertSee('Tableau de bord');

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/')->assertRedirect('/login');
    }

    public function test_mechanic_login_goes_straight_to_the_mobile_app(): void
    {
        $kofi = $this->user('mecanicien');

        $this->post('/login', ['email' => 'mecanicien@test.local', 'password' => 'secret-123'])
            ->assertRedirect(route('mecanicien.handoff'));

        $location = $this->get(route('mecanicien.handoff'))->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('/mecanicien/#connexion=', $location);
        $this->assertGuest(); // plus de session web mélangée avec l'application

        $token = rawurldecode(explode('#connexion=', $location)[1]);
        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('email', $kofi->email);
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertSame(0, $kofi->tokens()->count());
    }

    public function test_mechanic_app_page_asks_for_credentials_and_has_no_demo_button(): void
    {
        $this->get('/mecanicien/')->assertOk();

        // la page est servie comme fichier statique : on vérifie son contenu directement
        $html = file_get_contents(public_path('mecanicien/index.html'));
        $this->assertStringContainsString('id="login-form"', $html);
        $this->assertStringNotContainsString('Essayer la démo', $html);
    }

    public function test_repair_order_can_be_edited(): void
    {
        config(['app.demo' => true]);
        $this->post('/demo/accueil');
        $repair = \App\Models\RepairOrder::first();

        $this->get("/repairs/{$repair->id}/edit")->assertOk()->assertSee($repair->numero);
        $this->put("/repairs/{$repair->id}", [
            'description_panne' => 'Nouvelle description', 'urgence' => 'urgent',
            'date_entree' => $repair->date_entree->format('Y-m-d'),
        ])->assertRedirect("/repairs/{$repair->id}");

        $this->assertSame('Nouvelle description', $repair->fresh()->description_panne);
    }

    public function test_patron_cannot_open_the_mechanic_handoff(): void
    {
        $this->actingAs($this->user('patron'))->get(route('mecanicien.handoff'))->assertForbidden();
    }
}
