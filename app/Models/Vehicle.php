<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'immatriculation', 'marque', 'modele',
        'annee', 'kilometrage', 'couleur', 'numero_chassis', 'notes',
    ];

    // ── Relations ─────────────────────────────────────────────
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class);
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = "%{$term}%";
        return $query->where(function($q) use ($term) {
            $q->where('immatriculation', 'like', $term)
              ->orWhere('marque', 'like', $term)
              ->orWhere('modele', 'like', $term);
        });
    }

    // ── Computed ──────────────────────────────────────────────
    public function getDesignationAttribute(): string
    {
        return "{$this->marque} {$this->modele} — {$this->immatriculation}";
    }
}
