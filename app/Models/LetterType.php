<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterType extends Model
{
    protected $guarded = ['id'];

    public function outgoingLetters(): HasMany
    {
        return $this->hasMany(OutgoingLetter::class, 'type_id');
    }
}