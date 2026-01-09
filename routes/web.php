<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LetterVerificationController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     // Jika user biasa mencoba akses dashboard, lempar ke surat saya
//     if (auth()->user()->role === 'user') {
//         return redirect()->route('user.letters.index');
//     }
    
//     // Jika admin/pimpinan, tampilkan view dashboard
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    $user = auth()->user();

    // 1. Jika User Biasa -> Lempar ke Surat Saya
    if ($user->role === 'user') {
        return redirect()->route('user.letters.index');
    }
    
    // 2. Jika Admin/Pimpinan -> Lempar ke Filament
    // (Agar jika mereka ketik /dashboard, otomatis masuk ke /admin)
    return redirect()->to('/admin');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/outgoing-letters/{record}/print', [PdfController::class, 'printOutgoing'])
    ->name('outgoing.print')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
