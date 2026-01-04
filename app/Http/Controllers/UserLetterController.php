<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use App\Models\LetterType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserLetterController extends Controller
{
    /**
     * Halaman Utama: List Surat Saya
     */
    public function index()
    {
        $letters = OutgoingLetter::with(['type', 'signer'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.letters.index', compact('letters'));
    }

    /**
     * Halaman Form Pengajuan
     */
    public function create()
    {
        $types = LetterType::all();
        return view('user.letters.create', compact('types'));
    }

    /**
     * Proses Simpan Pengajuan
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:letter_types,id',
            'recipient' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            // 'content_data' => 'required|string',
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:2048', 
        ]);

        $defaultSignerId = \App\Models\Signer::first()->id ?? 1;

        $letter = OutgoingLetter::create([
            'user_id' => Auth::id(),
            'type_id' => $request->type_id,
            'signer_id' => $defaultSignerId, 
            'letter_date' => now(),
            'recipient' => $request->recipient,
            'subject' => $request->subject,
            'content_data' => '-',
            'status' => 'submitted', 
        ]);

        return redirect()->route('user.letters.index')
            ->with('success', 'Surat berhasil diajukan! Menunggu verifikasi admin.');
    }

    public function edit(OutgoingLetter $letter)
    {
        // Validasi Kepemilikan
        if ($letter->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($letter->status, ['submitted', 'rejected'])) {
            return redirect()->route('user.letters.index')
                ->with('error', 'Surat yang sedang diproses atau sudah selesai tidak bisa diedit.');
        }

        $types = LetterType::all();
        return view('user.letters.edit', compact('letter', 'types'));
    }

    public function update(Request $request, OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);

        $request->validate([
            'type_id' => 'required|exists:letter_types,id',
            'recipient' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            // 'content_data' => 'required|string',
        ]);

        $letter->update([
            'type_id' => $request->type_id,
            'recipient' => $request->recipient,
            'subject' => $request->subject,
            // 'content_data' => $request->content_data,
            'status' => 'submitted', 
        ]);

        return redirect()->route('user.letters.index')
            ->with('success', 'Surat berhasil diperbarui dan diajukan kembali.');
    }

    public function destroy(OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);

        if (!in_array($letter->status, ['submitted', 'rejected'])) {
            return back()->with('error', 'Tidak bisa menghapus surat yang sedang diproses.');
        }

        $letter->delete();
        return back()->with('success', 'Pengajuan surat berhasil dihapus.');
    }
}