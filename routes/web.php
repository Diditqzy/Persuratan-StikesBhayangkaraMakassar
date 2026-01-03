<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LetterVerificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/outgoing-letters/{record}/print', [PdfController::class, 'printOutgoing'])
    ->name('outgoing.print')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Download Gambar QR Code
    Route::get('/outgoing-letters/{record}/download-qr', [PdfController::class, 'downloadQrImage'])
        ->name('outgoing-letters.download-qr');

    // Cetak Surat Final (PDF)
    Route::get('/outgoing-letters/{record}/print', [PdfController::class, 'printOutgoing'])
        ->name('outgoing.print');
});

Route::get('/verify-surat/{code}', [LetterVerificationController::class, 'verify'])->name('letter.verify');

require __DIR__.'/auth.php';
