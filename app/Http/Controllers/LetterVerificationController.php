<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use Illuminate\Http\Request;

class LetterVerificationController extends Controller
{
    public function verify($code)
    {
        // Cari surat berdasarkan signature_code
        $letter = OutgoingLetter::with(['type', 'signer', 'user'])
            ->where('signature_code', $code)
            ->whereIn('status', ['approved', 'completed']) // Hanya yang sudah disetujui
            ->first();

        if (!$letter) {
            // Jika tidak ketemu, tampilkan halaman error/palsu
            return view('verification.invalid');
        }

        // Jika ketemu, tampilkan halaman valid
        return view('verification.valid', compact('letter'));
    }
}