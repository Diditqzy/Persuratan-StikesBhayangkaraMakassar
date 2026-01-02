<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutgoingLetter extends Model
{
    // Casting JSON dan Date otomatis
    protected $casts = [
        'content_data' => 'array',
        'letter_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        
    ];

    protected $guarded = ['id'];

    

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