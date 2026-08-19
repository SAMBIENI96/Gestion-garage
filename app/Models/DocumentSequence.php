<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class DocumentSequence
{
    public static function next(string $type, int $year): int
    {
        return DB::transaction(function () use ($type, $year) {
            DB::table('document_sequences')->insertOrIgnore([
                'type' => $type,
                'year' => $year,
                'current_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('document_sequences')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = $sequence->current_number + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'current_number' => $next,
                    'updated_at' => now(),
                ]);

            return $next;
        });
    }
}
