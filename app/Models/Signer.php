<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Signer extends Model
{
    protected $guarded = ['id'];

    // Relasi ke User aslinya
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Daftar surat yang pernah dia tandatangani
    public function outgoingLetters(): HasMany
    {
        return $this->hasMany(OutgoingLetter::class);
    }
}