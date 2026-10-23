<?php

namespace Tests\Feature;

use App\Models\RepairOrder;
use App\Models\User;
use App\Support\Demo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    private function enableDemo(): void
    {
        config(['app.demo' => true]);
    }

    public function test_demo_is_hidden_and_closed_when_disabled(): void
    {
        $this->get('/login')->assertDontSee('Essayer la démo');
        $this->post('/demo/patron')->assertNotFound();
        $this->postJson('/api/demo-login')->assertNotFound();
        $this->getJson('/api/demo')->assertJson(['enabled' => false]);
    }

    public function test_one_click_login_seeds_data_and_shows_banner(): void
    {
        $this->enableDemo();

        $this->get('/login')->assertSee('Essayer la démo');
        $this->post('/demo/patron')->assertRedirect('/');

        $this->assertAuthenticatedAs(User::where('email', 'patron@garage.local')->first());
        $this->assertGreaterThan(5, RepairOrder::count());
        $this->get('/')->assertSee('Mode démo');
    }

    public function test_mechanic_demo_opens_the_mobile_app_with_a_token(): void
    {
        $this->enableDemo();

        $this->post('/demo/mecanicien')->assertRedirect('/mecanicien/?demo=1');

        $token = $this->postJson('/api/demo-login')->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/mes-taches')->assertOk()->assertJsonCount(3);
    }

    public function test_demo_accounts_cannot_change_credentials_or_manage_users(): void
    {
        $this->enableDemo();
        $this->post('/demo/patron');

        $this->put('/parametres/mot-de-passe', ['current_password' => 'password', 'password' => 'pirate123', 'password_confirmation' => 'pirate123'])
            ->assertSessionHas('error');
        $this->patch('/parametres/profil', ['name' => 'Pirate', 'email' => 'pirate@x.y'])->assertSessionHas('error');

        $kofi = User::where('email', 'kofi@garage.local')->first();
        $this->post("/users/{$kofi->id}/toggle-active")->assertSessionHas('error');

        $this->assertTrue(Hash::check('password', User::where('email', 'patron@garage.local')->first()->password));
        $this->assertTrue($kofi->fresh()->is_active);
        $this->get('/users')->assertOk(); // lecture toujours possible
    }

    public function test_demo_data_is_reset_when_stale(): void
    {
        $this->enableDemo();
        $this->post('/demo/accueil');
        \App\Models\Invoice::query()->forceDelete();
        RepairOrder::query()->forceDelete();

        Cache::put('demo:last_reset', now()->subDays(2));
        auth()->logout();
        $this->post('/demo/accueil');

        $this->assertGreaterThan(5, RepairOrder::count());
    }

    public function test_demo_reset_command_refuses_without_demo_mode(): void
    {
        $this->artisan('demo:reset')->assertFailed();
    }
}
