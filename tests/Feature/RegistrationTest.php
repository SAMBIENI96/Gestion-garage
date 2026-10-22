<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function patron(): User
    {
        return User::create([
            'name' => 'Thomas Patron', 'email' => 'patron@test.local', 'role' => 'patron',
            'password' => Hash::make('secret-123'), 'is_active' => true, 'approved_at' => now(),
        ]);
    }

    private function fakeGoogle(string $email, bool $verified = true, string $id = 'g-123'): void
    {
        config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret']);

        $google = (new GoogleUser)->setRaw(['email_verified' => $verified])->map([
            'id' => $id, 'name' => 'Awa Dossou', 'email' => $email, 'avatar' => 'https://example.test/a.png',
        ]);

        Socialite::shouldReceive('driver->user')->andReturn($google);
    }

    // ── Inscription par formulaire ───────────────────────────────

    public function test_registration_creates_a_pending_account_without_logging_in(): void
    {
        $this->post('/inscription', [
            'name' => 'Awa Dossou', 'email' => 'Awa@Test.local', 'role' => 'mecanicien',
            'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $this->assertGuest();
        $user = User::where('email', 'awa@test.local')->firstOrFail();
        $this->assertTrue($user->isPending());
        $this->assertFalse($user->is_active);
        $this->assertSame('mecanicien', $user->role);
    }

    public function test_registration_cannot_request_the_patron_role(): void
    {
        $this->post('/inscription', [
            'name' => 'Pirate', 'email' => 'p@test.local', 'role' => 'patron',
            'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'p@test.local']);
    }

    public function test_pending_account_cannot_log_in(): void
    {
        User::create(['name' => 'Awa', 'email' => 'awa@test.local', 'role' => 'accueil',
            'password' => Hash::make('motdepasse1'), 'is_active' => false]);

        $this->post('/login', ['email' => 'awa@test.local', 'password' => 'motdepasse1'])
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    // ── Validation par le patron ─────────────────────────────────

    public function test_patron_approves_a_request_with_a_role(): void
    {
        $req = User::create(['name' => 'Awa', 'email' => 'awa@test.local', 'role' => 'accueil',
            'password' => Hash::make('motdepasse1'), 'is_active' => false]);

        $this->actingAs($this->patron())->get('/users')->assertSee("Demandes d'accès", false)->assertSee('awa@test.local');

        $this->post("/users/{$req->id}/approuver", ['role' => 'mecanicien'])->assertSessionHas('success');

        $req->refresh();
        $this->assertFalse($req->isPending());
        $this->assertTrue($req->is_active);
        $this->assertSame('mecanicien', $req->role);

        auth()->logout();
        $this->post('/login', ['email' => 'awa@test.local', 'password' => 'motdepasse1']);
        $this->assertAuthenticatedAs($req);
    }

    public function test_patron_rejects_a_request(): void
    {
        $req = User::create(['name' => 'Awa', 'email' => 'awa@test.local', 'role' => 'accueil', 'is_active' => false]);

        $this->actingAs($this->patron())->delete("/users/{$req->id}/refuser")->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $req->id]);
    }

    public function test_non_patron_cannot_approve(): void
    {
        $accueil = User::create(['name' => 'Marie', 'email' => 'm@test.local', 'role' => 'accueil',
            'password' => Hash::make('x-123456'), 'is_active' => true, 'approved_at' => now()]);
        $req = User::create(['name' => 'Awa', 'email' => 'awa@test.local', 'role' => 'accueil', 'is_active' => false]);

        $this->actingAs($accueil)->post("/users/{$req->id}/approuver", ['role' => 'accueil'])->assertForbidden();
        $this->assertTrue($req->fresh()->isPending());
    }

    // ── Google ───────────────────────────────────────────────────

    public function test_google_button_is_hidden_without_credentials(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/login')->assertDontSee('Continuer avec Google');
        $this->get('/auth/google')->assertNotFound();
    }

    public function test_google_new_user_becomes_a_pending_request(): void
    {
        $this->fakeGoogle('awa@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHas('status');

        $this->assertGuest();
        $user = User::where('email', 'awa@gmail.com')->firstOrFail();
        $this->assertTrue($user->isPending());
        $this->assertNull($user->password);
        $this->assertSame('g-123', $user->google_id);
    }

    public function test_google_logs_in_an_approved_user_matched_by_email(): void
    {
        $patron = $this->patron();
        $this->fakeGoogle('PATRON@test.local');

        $this->get('/auth/google/callback')->assertRedirect('/');

        $this->assertAuthenticatedAs($patron);
        $this->assertSame('g-123', $patron->fresh()->google_id);
    }

    public function test_google_refuses_unverified_email(): void
    {
        $this->patron();
        $this->fakeGoogle('patron@test.local', verified: false);

        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_only_user_can_set_a_password_without_current_one(): void
    {
        $user = User::create(['name' => 'Awa', 'email' => 'awa@gmail.com', 'google_id' => 'g-1', 'role' => 'accueil',
            'is_active' => true, 'approved_at' => now()]);

        $this->actingAs($user)
            ->put('/parametres/mot-de-passe', ['password' => 'nouveau-mdp-2', 'password_confirmation' => 'nouveau-mdp-2'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nouveau-mdp-2', $user->fresh()->password));
    }
}
