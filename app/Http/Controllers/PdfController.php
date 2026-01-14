<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    /**
     * Cetak PDF Surat
     */
    public function printOutgoing(OutgoingLetter $record)
    {
        $safeNumber = str_replace(['/', '\\'], '-', $record->letter_number ?? 'DRAFT');

        if ($record->type_id === 1) {
            $pdf = Pdf::loadView('pdf.outgoing-letter', ['data' => $record]);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("Surat-{$safeNumber}.pdf");
        }

        if (!$record->final_file_path || !Storage::disk('public')->exists($record->final_file_path)) {
            abort(404, 'Dokumen fisik surat belum diunggah oleh Admin.');
        }

        return Storage::disk('public')->response(
            $record->final_file_path,
            "Dokumen-{$safeNumber}.pdf"
        );
    }

    /**
     * Download Gambar QR Code
     */
    public function downloadQrImage(OutgoingLetter $record)
    {
        if (!in_array($record->status, ['approved', 'completed']) || !$record->signature_code) {
            abort(403, 'Akses ditolak. Surat belum disetujui.');
        }

        $url = route('letter.verify', $record->signature_code);

        $qrSvg = (string) QrCode::format('svg')
            ->size(500)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        $logoPath = public_path('images/logo.png');

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoTag = sprintf(
                '<image x="35%%" y="35%%" width="30%%" height="30%%" href="data:image/png;base64,%s" />',
                $logoData
            );

            $qrSvg = str_replace('</svg>', $logoTag . '</svg>', $qrSvg);
        }

        $filename = 'QR-' . str_replace(['/', '\\'], '-', $record->letter_number) . '.svg';

        return response($qrSvg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}