<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterAttachment extends Model
{
    protected $guarded = ['id'];

    public function outgoingLetter(): BelongsTo
    {
        return $this->belongsTo(OutgoingLetter::class);
    }
}