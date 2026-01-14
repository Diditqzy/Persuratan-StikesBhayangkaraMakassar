<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use Illuminate\Http\Request;

class LetterVerificationController extends Controller
{
    public function verify($code)
    {
        $letter = OutgoingLetter::with(['type', 'signer', 'user'])
            ->where('signature_code', $code)
            ->whereIn('status', ['approved', 'completed']) 
            ->first();

        if (!$letter) {
            return view('verification.invalid');
        }

        return view('verification.valid', compact('letter'));
    }
}