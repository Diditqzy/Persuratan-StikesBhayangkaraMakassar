<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingLetter extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'letter_date' => 'date',
        'received_date' => 'date',
    ];

    public function inputtedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by_user_id');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(IncomingDisposition::class);
    }
}