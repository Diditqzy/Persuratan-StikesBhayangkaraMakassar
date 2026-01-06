<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

class PdfController extends Controller
{
    /**
     * Cetak PDF Surat
     */
    public function printOutgoing(OutgoingLetter $record)
    {
        $pdf = Pdf::loadView('pdf.outgoing-letter', [
            'data' => $record,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $safeNumber = str_replace(['/', '\\'], '-', $record->letter_number ?? 'DRAFT');
        
        return $pdf->stream("Surat-{$safeNumber}.pdf");
    }

    /**
     * Download Gambar QR Code
     */
    public function downloadQrImage(OutgoingLetter $record)
    {
        if (!in_array($record->status, ['approved', 'completed']) || !$record->signature_code) {
            abort(403, 'Surat belum disetujui atau kode tanda tangan belum ada.');
        }

        $url = route('letter.verify', $record->signature_code);

        // Generate QR Code SVG
        $qrSvg = (string) QrCode::format('svg')
                        ->size(500) 
                        ->margin(1) 
                        ->errorCorrection('H')
                        ->generate($url);

        $logoPath = public_path('images/logo.png');
        
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;

            $logoTag = sprintf(
                '<image x="35%%" y="35%%" width="30%%" height="30%%" href="%s" />', 
                $logoBase64
            );

            $qrSvg = str_replace('</svg>', $logoTag . '</svg>', $qrSvg);
        }

        $filename = 'QR-' . str_replace(['/', '\\'], '-', $record->letter_number) . '.svg';

        return response($qrSvg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}