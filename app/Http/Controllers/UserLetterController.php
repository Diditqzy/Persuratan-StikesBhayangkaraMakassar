<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use App\Models\LetterType;
use App\Models\Signer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserLetterController extends Controller
{
    public function index()
    {
        $letters = OutgoingLetter::with(['type', 'signer'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.letters.index', compact('letters'));
    }

    // --- REVISI METHOD CREATE ---
    public function create(Request $request)
    {
        if (! $request->has('type_id')) {
            $types = LetterType::all();
            return view('user.letters.select-type', compact('types'));
        }

        $type = LetterType::findOrFail($request->type_id);
        
        $formConfig = $type->form_config ?? []; 

        return view('user.letters.create', compact('type', 'formConfig'));
    }

    // --- REVISI METHOD STORE ---
    public function store(Request $request)
    {
        // 1. Validasi HANYA Type ID (Subject & Recipient dihapus)
        $request->validate([
            'type_id' => 'required|exists:letter_types,id',
        ]);

        $type = LetterType::findOrFail($request->type_id);
        $additionalData = [];

        // 2. Loop Form Builder (Tetap Sama)
        if (!empty($type->form_config)) {
            foreach ($type->form_config as $field) {
                $key = Str::slug($field['label']);
                $label = $field['label'];
                $isRequired = $field['required'] ?? false;

                if ($isRequired && !$request->has($key) && !$request->file($key)) {
                    return back()->withInput()->withErrors(["$key" => "Kolom '$label' wajib diisi!"]);
                }

                if ($field['type'] === 'file' && $request->hasFile($key)) {
                    $file = $request->file($key);
                    $path = $file->store('lampiran-tambahan', 'public');
                    $additionalData[$label] = [
                        'type' => 'file',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName()
                    ];
                } else {
                    $value = $request->input($key);
                    if ($value) $additionalData[$label] = $value;
                }
            }
        }

        // 3. Simpan ke Database
        $defaultSignerId = Signer::where('is_active', true)->first()->id ?? 1;

        OutgoingLetter::create([
            'user_id' => Auth::id(),
            'type_id' => $request->type_id,
            'signer_id' => $defaultSignerId,
            'letter_date' => now(),
            
            // ISI OTOMATIS (Karena input user dihapus)
            'recipient' => 'Bagian Administrasi', // Default
            'subject' => 'Pengajuan ' . $type->name, // Otomatis ambil nama jenis surat
            
            'status' => 'submitted',
            'additional_data' => $additionalData,
        ]);

        return redirect()->route('user.letters.index')
            ->with('success', 'Surat berhasil diajukan! Menunggu verifikasi admin.');
    }

    public function edit(OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);
        if (!in_array($letter->status, ['submitted', 'rejected'])) {
            return redirect()->route('user.letters.index')->with('error', 'Surat tidak bisa diedit.');
        }

        $type = $letter->type;
        $formConfig = $type->form_config ?? [];

        return view('user.letters.edit', compact('letter', 'type', 'formConfig'));
    }

    public function update(Request $request, OutgoingLetter $letter)
    {
        $letter->update([
            'recipient' => $request->recipient,
            'subject' => $request->subject,
            'status' => 'submitted', 
        ]);
        return redirect()->route('user.letters.index')->with('success', 'Surat diperbarui.');
    }

    public function destroy(OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);
        if (!in_array($letter->status, ['submitted', 'rejected'])) {
            return back()->with('error', 'Gagal hapus.');
        }
        $letter->delete();
        return back()->with('success', 'Dihapus.');
    }
}