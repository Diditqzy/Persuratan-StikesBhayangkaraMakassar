<?php

namespace App\Observers;

use App\Models\IncomingDisposition;
use App\Models\IncomingLetter;

class IncomingDispositionObserver
{
    /**
     * Handle the IncomingDisposition "created" event.
     */
    public function created(IncomingDisposition $incomingDisposition): void
    {
        // Ambil surat induknya
        $letter = $incomingDisposition->incomingLetter;

        // Cek: Kalau statusnya masih 'waiting_disposition', ubah jadi 'dispositioned'
        if ($letter && $letter->status === 'waiting_disposition') {
            $letter->update([
                'status' => 'dispositioned',
            ]);
        }
    }

    /**
     * Handle the IncomingDisposition "updated" event.
     */
    public function updated(IncomingDisposition $incomingDisposition): void
    {
        //
    }

    /**
     * Handle the IncomingDisposition "deleted" event.
     */
    public function deleted(IncomingDisposition $incomingDisposition): void
    {
        // Opsional: Kalau semua disposisi dihapus, balikin status jadi waiting?
        // Logic ini agak tricky, sementara kita biarkan saja.
        // Kalau mau perfect: Cek jumlah disposisi tersisa. Kalau 0, ubah ke waiting.
        
        $letter = $incomingDisposition->incomingLetter;
        
        if ($letter && $letter->dispositions()->count() === 0) {
            $letter->update([
                'status' => 'waiting_disposition',
            ]);
        }
    }

    /**
     * Handle the IncomingDisposition "restored" event.
     */
    public function restored(IncomingDisposition $incomingDisposition): void
    {
        //
    }

    /**
     * Handle the IncomingDisposition "force deleted" event.
     */
    public function forceDeleted(IncomingDisposition $incomingDisposition): void
    {
        //
    }
}
