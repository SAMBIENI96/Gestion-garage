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
        'name', 'email', 'phone', 'role', 'password', 'is_active', 'theme',
        'google_id', 'avatar_url', 'approved_at',
    ];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    protected $casts = [
        'is_active'   => 'boolean',
        'approved_at' => 'datetime',
    ];

    // ── Role helpers ──────────────────────────────────────────
    public function isPatron(): bool   { return $this->role === 'patron'; }
    public function isAccueil(): bool  { return $this->role === 'accueil'; }
    public function isMecanicien(): bool { return $this->role === 'mecanicien'; }

    // Inscription (formulaire ou Google) pas encore validée par le patron
    public function isPending(): bool { return $this->approved_at === null; }

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

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    // ── Computed ──────────────────────────────────────────────
    public function getChargeActuelleAttribute(): int
    {
        return $this->repairOrdersAssigned()
            ->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours'])
            ->count();
    }

    public function getInitialesAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $initiales = mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
        return mb_strtoupper($initiales ?: '?');
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
