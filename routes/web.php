<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LetterVerificationController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'user') {
        return redirect()->route('user.letters.index');
    }
    
    return redirect()->to('/admin');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/outgoing-letters/{record}/print', [PdfController::class, 'printOutgoing'])
    ->name('outgoing.print')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/letters/{record}/print', [PdfController::class, 'printOutgoing'])->name('letters.print');
    Route::get('/surat-saya', [App\Http\Controllers\UserLetterController::class, 'index'])->name('user.letters.index');
    Route::get('/ajukan-surat', [App\Http\Controllers\UserLetterController::class, 'create'])->name('user.letters.create');
    Route::post('/ajukan-surat', [App\Http\Controllers\UserLetterController::class, 'store'])->name('user.letters.store');

    Route::get('/surat-saya/{letter}/edit', [App\Http\Controllers\UserLetterController::class, 'edit'])->name('user.letters.edit');
    Route::put('/surat-saya/{letter}', [App\Http\Controllers\UserLetterController::class, 'update'])->name('user.letters.update');
    Route::delete('/surat-saya/{letter}', [App\Http\Controllers\UserLetterController::class, 'destroy'])->name('user.letters.destroy');

    // Download Gambar QR Code
    Route::get('/outgoing-letters/{record}/download-qr', [PdfController::class, 'downloadQrImage'])
        ->name('outgoing-letters.download-qr');

    // Cetak Surat Final (PDF)
    Route::get('/outgoing-letters/{record}/print', [PdfController::class, 'printOutgoing'])
        ->name('outgoing.print');
});

Route::get('/verify-surat/{code}', [LetterVerificationController::class, 'verify'])->name('letter.verify');

require __DIR__.'/auth.php';
