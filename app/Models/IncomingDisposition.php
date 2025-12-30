<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingDisposition extends Model
{
    protected $guarded = ['id'];

    // Milik surat masuk yang mana
    public function incomingLetter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    // Siapa pimpinan yang memberi instruksi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}