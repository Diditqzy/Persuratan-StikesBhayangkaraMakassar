<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingDisposition extends Model
{
    protected $guarded = ['id'];

    public function sender() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
