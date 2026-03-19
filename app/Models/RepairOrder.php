<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class RepairOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'client_id', 'vehicle_id', 'created_by', 'assigned_to',
        'description_panne', 'notes_patron', 'pieces_estimees', 'cout_estime',
        'statut', 'urgence', 'kilometrage_entree',
        'date_entree', 'date_sortie_prevue', 'date_sortie_effective',
    ];

    protected $casts = [
        'date_entree'           => 'date',
        'date_sortie_prevue'    => 'date',
        'date_sortie_effective' => 'datetime',
        'cout_estime'           => 'decimal:2',
    ];

    // ── Boot: auto-generate numero ─────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (RepairOrder $order) {
            if (empty($order->numero)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $order->numero = sprintf('OR-%d-%04d', $year, $count);
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────
    public function client(): BelongsTo   { return $this->belongsTo(Client::class); }
    public function vehicle(): BelongsTo  { return $this->belongsTo(Vehicle::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function notes(): HasMany      { return $this->hasMany(InterventionNote::class); }
    public function invoice(): HasOne     { return $this->hasOne(Invoice::class); }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActifs(Builder $query): Builder
    {
        return $query->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours', 'probleme']);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = "%{$term}%";
        return $query->where(function($q) use ($term) {
            $q->where('numero', 'like', $term)
              ->orWhere('description_panne', 'like', $term)
              ->orWhereHas('client', fn($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))
              ->orWhereHas('vehicle', fn($v) => $v->where('immatriculation', 'like', $term));
        });
    }

    // ── Computed ──────────────────────────────────────────────
    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'nouveau'           => 'Nouveau',
            'en_attente_pieces' => 'En attente pièces',
            'en_cours'          => 'En cours',
            'termine'           => 'Terminé',
            'probleme'          => 'Problème détecté',
            'annule'            => 'Annulé',
            default             => $this->statut,
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match($this->statut) {
            'nouveau'           => 'blue',
            'en_attente_pieces' => 'yellow',
            'en_cours'          => 'orange',
            'termine'           => 'green',
            'probleme'          => 'red',
            'annule'            => 'gray',
            default             => 'gray',
        };
    }

    public function getUrgenceLabelAttribute(): string
    {
        return match($this->urgence) {
            'urgent' => 'Urgent',
            'vip'    => 'VIP',
            default  => 'Normal',
        };
    }
}
