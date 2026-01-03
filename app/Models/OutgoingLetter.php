<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutgoingLetter extends Model
{
    protected $casts = [
        'content_data' => 'array',
        'letter_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        
    ];

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::updating(function ($letter) {
            if ($letter->isDirty('status') && 
                in_array($letter->status, ['approved', 'completed']) && 
                is_null($letter->signature_code)) {
                
                $letter->signature_code = (string) Str::uuid();
            }
        });
    }

    // Relasi ke Pembuat Surat
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Jenis Surat
    public function type(): BelongsTo
    {
        return $this->belongsTo(LetterType::class, 'type_id');
    }

    // Relasi ke Penanda Tangan
    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    // Relasi ke Disposisi (History Revisi)
    public function dispositions(): HasMany
    {
        return $this->hasMany(OutgoingDisposition::class);
    }

    // Relasi ke Lampiran
    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class);
    }
}