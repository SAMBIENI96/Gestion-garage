<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MecanicienApiTest extends TestCase
{
    use RefreshDatabase;

    // Petite image JPEG réelle (évite de dépendre de l'extension GD)
    private const JPEG = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAAGAAgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwC5RRRXyJ+gn//Z';

    private User $kofi;
    private RepairOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kofi = User::create([
            'name' => 'Kofi Mensah', 'email' => 'kofi@test.local', 'role' => 'mecanicien',
            'password' => Hash::make('secret-123'), 'is_active' => true, 'approved_at' => now(),
        ]);
        $client = Client::create(['nom' => 'AGOSSOU', 'prenom' => 'Jean', 'telephone' => '+229 96 11 22 33', 'created_by' => $this->kofi->id]);
        $vehicle = Vehicle::create(['client_id' => $client->id, 'immatriculation' => 'BJ-1234-AA', 'marque' => 'Toyota', 'modele' => 'Corolla']);
        $this->order = RepairOrder::create([
            'client_id' => $client->id, 'vehicle_id' => $vehicle->id, 'assigned_to' => $this->kofi->id, 'created_by' => $this->kofi->id,
            'description_panne' => 'Freins usés', 'statut' => 'nouveau', 'urgence' => 'urgent', 'date_entree' => now(),
        ]);
    }

    private function token(): string
    {
        return $this->postJson('/api/login', ['email' => 'kofi@test.local', 'password' => 'secret-123'])->json('token');
    }

    public function test_login_returns_token_and_safe_user_payload(): void
    {
        $this->postJson('/api/login', ['email' => 'kofi@test.local', 'password' => 'secret-123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'initiales', 'theme']])
            ->assertJsonMissingPath('user.password');
    }

    public function test_login_does_not_reveal_whether_email_exists(): void
    {
        $unknown = $this->postJson('/api/login', ['email' => 'inconnu@test.local', 'password' => 'x'])->assertUnauthorized();
        $wrong   = $this->postJson('/api/login', ['email' => 'kofi@test.local', 'password' => 'x'])->assertUnauthorized();

        $this->assertSame($unknown->json('message'), $wrong->json('message'));
    }

    public function test_deactivated_mechanic_loses_api_access_immediately(): void
    {
        $token = $this->token();
        $this->withToken($token)->getJson('/api/mes-taches')->assertOk();

        $this->kofi->update(['is_active' => false]);
        $this->app['auth']->forgetGuards(); // nouvelle requête : l'utilisateur est relu en base

        $this->withToken($token)->getJson('/api/mes-taches')->assertUnauthorized();
        $this->assertSame(0, $this->kofi->tokens()->count());
    }

    public function test_detail_includes_history_with_author_and_labels(): void
    {
        $token = $this->token();
        $this->withToken($token)->postJson("/api/taches/{$this->order->id}/statut", ['statut' => 'en_cours', 'contenu' => 'Diagnostic effectué'])->assertOk();

        $this->withToken($token)->getJson("/api/taches/{$this->order->id}")
            ->assertOk()
            ->assertJsonPath('vehicle.immatriculation', 'BJ-1234-AA')
            ->assertJsonPath('notes.0.auteur', 'Kofi Mensah')
            ->assertJsonPath('notes.0.ancien_label', 'Nouveau')
            ->assertJsonPath('notes.0.nouveau_label', 'En cours');
    }

    public function test_status_update_accepts_a_photo(): void
    {
        Storage::fake('public');

        $this->withToken($this->token())
            ->post("/api/taches/{$this->order->id}/statut", [
                'statut' => 'termine', 'contenu' => 'Terminé', 'photo' => UploadedFile::fake()->createWithContent('avant.jpg', base64_decode(self::JPEG)),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->order->refresh();
        $this->assertSame('termine', $this->order->statut);
        $this->assertNotNull($this->order->date_sortie_effective);
        Storage::disk('public')->assertExists($this->order->notes()->first()->photo_path);
    }

    public function test_mechanic_cannot_touch_another_mechanics_repair(): void
    {
        $other = User::create(['name' => 'Seydou', 'email' => 's@test.local', 'role' => 'mecanicien',
            'password' => Hash::make('secret-123'), 'is_active' => true, 'approved_at' => now()]);
        $this->order->update(['assigned_to' => $other->id]);

        $token = $this->token();
        $this->withToken($token)->getJson("/api/taches/{$this->order->id}")->assertForbidden();
        $this->withToken($token)->postJson("/api/taches/{$this->order->id}/statut", ['statut' => 'termine', 'contenu' => 'x'])->assertForbidden();
    }

    public function test_theme_preference_is_saved(): void
    {
        $this->withToken($this->token())->patchJson('/api/preferences', ['theme' => 'dark'])->assertOk()->assertJsonPath('theme', 'dark');
        $this->assertSame('dark', $this->kofi->fresh()->theme);
    }
}
