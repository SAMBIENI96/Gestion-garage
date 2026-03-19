<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionNote extends Model
{
    protected $fillable = [
        'repair_order_id', 'user_id', 'contenu',
        'photo_path', 'ancien_statut', 'nouveau_statut',
    ];

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
