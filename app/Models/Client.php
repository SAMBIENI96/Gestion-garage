<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'prenom', 'telephone', 'telephone2',
        'email', 'adresse', 'notes', 'created_by',
    ];

    // ── Relations ─────────────────────────────────────────────
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = "%{$term}%";
        return $query->where(function($q) use ($term) {
            $q->where('nom', 'like', $term)
              ->orWhere('prenom', 'like', $term)
              ->orWhere('telephone', 'like', $term)
              ->orWhere('email', 'like', $term);
        });
    }

    // ── Computed ──────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
