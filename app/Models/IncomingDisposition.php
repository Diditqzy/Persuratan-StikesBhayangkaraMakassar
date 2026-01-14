<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingDisposition extends Model
{
    protected $guarded = ['id'];

    public function incomingLetter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}