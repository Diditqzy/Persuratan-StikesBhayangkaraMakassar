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
        $letter = $incomingDisposition->incomingLetter;

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
