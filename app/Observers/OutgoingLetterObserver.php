<?php

namespace App\Observers;

use App\Models\OutgoingLetter;
use Carbon\Carbon;

class OutgoingLetterObserver
{
    /**
     * Handle the OutgoingLetter "saving" event.
     */
    public function saving(OutgoingLetter $outgoingLetter): void
    {
        if ($outgoingLetter->isDirty('status') && 
            in_array($outgoingLetter->status, ['approved', 'completed'])) {
            
            if (empty($outgoingLetter->letter_number)) {
                $outgoingLetter->letter_number = $this->generateLetterNumber($outgoingLetter);
            }
            
            if (empty($outgoingLetter->approved_at)) {
                $outgoingLetter->approved_at = now();
            }
        }
    }

    /**
     * Logika Generate Nomor Surat
     */
    private function generateLetterNumber(OutgoingLetter $letter): string
    {
        $date = Carbon::parse($letter->letter_date);
        $month = $date->month;
        $year = $date->year;

        $typeCode = $letter->type->code ?? 'UM';
        if (!$letter->relationLoaded('type')) {
            $letter->load('type');
            $typeCode = $letter->type->code ?? 'UM';
        }

        // Format Template Akhiran: /KODE/STIKES/ROMAWI/TAHUN
        $romanMonth = $this->getRomanMonth($month);
        $formatSuffix = "/{$typeCode}/STIKES/{$romanMonth}/{$year}";

        $lastLetter = OutgoingLetter::where('letter_number', 'LIKE', "%{$formatSuffix}")
            ->orderBy('id', 'desc')
            ->first();

        $newSequence = 1;
        if ($lastLetter) {
            $parts = explode('/', $lastLetter->letter_number);
            $newSequence = (int) $parts[0] + 1;
        }

        do {
            $sequenceString = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
            $candidateNumber = "{$sequenceString}{$formatSuffix}";

            $exists = OutgoingLetter::where('letter_number', $candidateNumber)
                ->where('id', '!=', $letter->id) 
                ->exists();

            if ($exists) {
                $newSequence++; 
            }

        } while ($exists);

        return $candidateNumber;
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