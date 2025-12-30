<?php

namespace App\Observers;

use App\Models\OutgoingLetter;
use Carbon\Carbon;

class OutgoingLetterObserver
{
    /**
     * Handle the OutgoingLetter "saving" event.
     * Fungsi ini jalan OTOMATIS sesaat sebelum data disimpan ke DB.
     */
    public function saving(OutgoingLetter $outgoingLetter): void
    {
        // 1. Cek apakah status berubah menjadi 'approved'?
        if ($outgoingLetter->isDirty('status') && $outgoingLetter->status === 'approved') {
            
            // 2. Cek apakah nomor surat masih kosong? (Biar gak digenerate 2x)
            if (empty($outgoingLetter->letter_number)) {
                $outgoingLetter->letter_number = $this->generateLetterNumber($outgoingLetter);
            }
            
            // 3. Catat tanggal disetujui
            $outgoingLetter->approved_at = now();
        }
    }

    private function generateLetterNumber(OutgoingLetter $letter): string
    {
        // Format: NO_URUT/KODE_JENIS/STIKES/BULAN_ROMAWI/TAHUN
        // Contoh: 005/SK/STIKES/XII/2025

        $year = $letter->letter_date->year;
        $month = $letter->letter_date->month;
        $typeCode = $letter->type->code ?? 'UM'; // Default UM (Umum) kalau gak ada kode

        // Hitung urutan surat per TAHUN dan per JENIS SURAT yang sama
        $count = OutgoingLetter::whereYear('letter_date', $year)
            ->where('type_id', $letter->type_id)
            ->where('status', 'approved')
            ->count();
        
        // Urutan ditambah 1
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT); // 1 jadi 001

        $romanMonth = $this->getRomanMonth($month);

        return "{$sequence}/{$typeCode}/STIKES/{$romanMonth}/{$year}";
    }

    private function getRomanMonth($month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }
}