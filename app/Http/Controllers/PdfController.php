<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function printOutgoing(OutgoingLetter $record)
    {
        // Validasi: Cuma surat yang sudah Approved yang bisa dicetak resmi
        // Kalau belum approved, kita kasih watermark "DRAFT" (Opsional logic)
        
        $pdf = Pdf::loadView('pdf.outgoing-letter', [
            'data' => $record,
        ]);

        // Setup ukuran kertas F4 atau A4 (Standar surat Indonesia biasanya F4/Legal atau A4)
        $pdf->setPaper('A4', 'portrait');

        $safeNumber = str_replace('/', '-', $record->letter_number);
        
        return $pdf->stream("Surat-{$safeNumber}.pdf");
    }
}