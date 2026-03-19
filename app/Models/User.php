<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'role', 'password', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Role helpers ──────────────────────────────────────────
    public function isPatron(): bool   { return $this->role === 'patron'; }
    public function isAccueil(): bool  { return $this->role === 'accueil'; }
    public function isMecanicien(): bool { return $this->role === 'mecanicien'; }

    // ── Relations ─────────────────────────────────────────────
    public function repairOrdersAssigned(): HasMany
    {
        return $this->hasMany(RepairOrder::class, 'assigned_to');
    }

    public function repairOrdersCreated(): HasMany
    {
        return $this->hasMany(RepairOrder::class, 'created_by');
    }

    public function interventionNotes(): HasMany
    {
        return $this->hasMany(InterventionNote::class);
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeMecaniciens($query)
    {
        return $query->where('role', 'mecanicien')->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Computed ──────────────────────────────────────────────
    public function getChargeActuelleAttribute(): int
    {
        return $this->repairOrdersAssigned()
            ->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours'])
            ->count();
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'patron'     => 'Patron / Gérant',
            'accueil'    => 'Agent d\'accueil',
            'mecanicien' => 'Mécanicien',
            default      => $this->role,
        };
    }
}
