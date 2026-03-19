<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'repair_order_id', 'client_id', 'created_by',
        'lignes', 'sous_total', 'remise_pct', 'remise_montant',
        'total_ttc', 'statut', 'date_facture', 'notes',
    ];

    protected $casts = [
        'lignes'          => 'array',
        'sous_total'      => 'decimal:2',
        'remise_pct'      => 'decimal:2',
        'remise_montant'  => 'decimal:2',
        'total_ttc'       => 'decimal:2',
        'date_facture'    => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $inv) {
            if (empty($inv->numero)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $inv->numero = sprintf('FAC-%d-%04d', $year, $count);
            }
        });
    }

    public function repairOrder(): BelongsTo { return $this->belongsTo(RepairOrder::class); }
    public function client(): BelongsTo       { return $this->belongsTo(Client::class); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'validee'   => 'Validée',
            'payee'     => 'Payée',
            'annulee'   => 'Annulée',
            default     => $this->statut,
        };
    }
}
