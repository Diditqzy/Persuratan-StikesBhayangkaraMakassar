<?php

namespace App\Http\Controllers;

use App\Models\LetterType;
use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function create(Request $request)
    {
        if ($request->has('type_id')) {
            $type = LetterType::find($request->query('type_id'));

            if (!$type) {
                return redirect()->route('user.letters.create')
                    ->with('error', 'Jenis surat tidak ditemukan.');
            }

            $formConfig = $type->form_config ?? [];

            return view('user.letters.create', compact('type', 'formConfig'));
        }

        $types = LetterType::all();
        return view('user.letters.select-type', compact('types'));
    }

    public function store(Request $request)
    {
        $type = LetterType::findOrFail($request->type_id);
        
        // 1. VALIDASI DATA
        $rules = ['type_id' => 'required|exists:letter_types,id'];

        if ($type->id === 1) { // SKAK
            $rules = array_merge($rules, [
                'nama' => 'required|string', 'nim' => 'required|string',
                'prodi' => 'required|string', 'semester' => 'required|numeric',
                'tingkat' => 'required|string', 'tempat_lahir' => 'required|string',
                'tanggal_lahir' => 'required|date', 'alamat' => 'required|string',
                'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', 
            ]);
        } else { // Form Dinamis
            if (!empty($type->form_config)) {
                foreach ($type->form_config as $field) {
                    if (!empty($field['required'])) {
                        $key = Str::slug($field['label']);
                        $rule = ($field['type'] === 'file') ? 'file|mimes:pdf,jpg,png|max:5120' : 'required';
                        $rules[$key] = $rule;
                    }
                }
            }
        }

        $request->validate($rules);

        // 2. PROSES INPUT
        $additionalData = [];
        $attachments = [];

        if ($type->id === 1) {
            $additionalData = $request->only(['nama', 'nim', 'prodi', 'semester', 'tingkat', 'tempat_lahir', 'tanggal_lahir', 'alamat']);
            
            if ($request->hasFile('lampiran')) {
                $attachments[] = [
                    'filename' => 'Lampiran Pendukung',
                    'file_path' => $request->file('lampiran')->store('lampiran-surat-keluar', 'public'),
                ];
            }
        } else {
            if (!empty($type->form_config)) {
                foreach ($type->form_config as $field) {
                    $key = Str::slug($field['label']);
                    
                    if ($field['type'] === 'file') {
                        if ($request->hasFile($key)) {
                            $attachments[] = [
                                'filename' => $field['label'],
                                'file_path' => $request->file($key)->store('lampiran-surat-keluar', 'public'),
                            ];
                        }
                    } else {
                        $additionalData[$key] = $request->input($key);
                    }
                }
            }
        }

        // 3. SIMPAN DATABASE
        $letter = OutgoingLetter::create([
            'user_id' => Auth::id(),
            'type_id' => $type->id,
            'status' => 'submitted',
            'letter_date' => now(),
            'subject' => $type->name, 
            'recipient' => '-', 
            'additional_data' => $additionalData,
            'final_file_path' => null, // Menunggu Admin upload file jadi
        ]);

        if (!empty($attachments)) {
            $letter->attachments()->createMany($attachments);
        }

        return redirect()->route('user.letters.index')
            ->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function edit(OutgoingLetter $letter) 
    {
        if ($letter->user_id !== Auth::id()) abort(403);

        $formConfig = $letter->type->form_config ?? [];
        return view('user.letters.edit', compact('letter', 'formConfig'));
    }

    public function update(Request $request, OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);

        $currentData = $letter->additional_data ?? [];

        // Logika Update (SKAK vs Dinamis)
        if ($letter->type_id == 1) {
            $request->validate([
                'nim' => 'required', 'prodi' => 'required', 'semester' => 'required',
                'tingkat' => 'required', 'tempat_lahir' => 'required', 'tanggal_lahir' => 'required',
                'alamat' => 'required', 'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            $currentData = array_merge($currentData, $request->only(['nim', 'prodi', 'semester', 'tingkat', 'tempat_lahir', 'tanggal_lahir', 'alamat']));
            $currentData['nama'] = Auth::user()->name;

            if ($request->hasFile('lampiran')) {
                // Replace lampiran lama
                $letter->attachments()->where('filename', 'Lampiran Pendukung')->delete();
                $letter->attachments()->create([
                    'filename' => 'Lampiran Pendukung',
                    'file_path' => $request->file('lampiran')->store('lampiran-surat-keluar', 'public')
                ]);
            }
        } else {
            $config = $letter->type->form_config;
            if ($config) {
                foreach ($config as $field) {
                    $key = Str::slug($field['label']);
                    if ($field['type'] === 'file') {
                        if ($request->hasFile($key)) {
                            $letter->attachments()->where('filename', $field['label'])->delete();
                            $letter->attachments()->create([
                                'filename' => $field['label'],
                                'file_path' => $request->file($key)->store('lampiran-surat-keluar', 'public')
                            ]);
                        }
                    } else {
                        $currentData[$key] = $request->input($key);
                    }
                }
            }
        }

        // Reset status untuk review ulang
        $letter->update([
            'additional_data' => $currentData,
            'status' => 'submitted',
            'rejection_note' => null,
            'rejected_at' => null,
            'rejected_by' => null,
        ]);

        return redirect()->route('user.letters.index')
            ->with('success', 'Surat berhasil diperbarui dan diajukan ulang!');
    }
}