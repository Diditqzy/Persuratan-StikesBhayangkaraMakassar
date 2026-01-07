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
        $typeId = $request->query('type_id');
        
        $type = LetterType::find($typeId);

        if (!$type) {
            return redirect()->route('user.letters.create')->with('error', 'Jenis surat tidak ditemukan.');
        }

        return view('user.letters.create', compact('type'));
    }

    $types = LetterType::all();
    
    return view('user.letters.select-type', compact('types'));
    }

    public function store(Request $request)
    {
        $type = LetterType::findOrFail($request->type_id);
        
        // 1. RULE DASAR (Tanpa Subject/Recipient user)
        $rules = [
            'type_id' => 'required|exists:letter_types,id',
        ];

        // A. Validasi Khusus SKAK (ID 1)
        if ($type->id === 1) {
            $rules = array_merge($rules, [
                'nama' => 'required|string',
                'nim' => 'required|string',
                'prodi' => 'required|string',
                'semester' => 'required|numeric',
                'tingkat' => 'required|string',
                'tempat_lahir' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'required|string',
                'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', 
            ]);
        } 
        // B. Validasi Dinamis (Custom Form)
        else {
            if (!empty($type->form_config)) {
                foreach ($type->form_config as $field) {
                    if (isset($field['required']) && $field['required']) {
                        $key = Str::slug($field['label']);
                        $ruleType = $field['type'] === 'file' ? 'file|mimes:pdf,jpg,png|max:5120' : 'required';
                        $rules[$key] = $ruleType;
                    }
                }
            }
        }

        $validated = $request->validate($rules);

        // 2. PROSES DATA
        $additionalData = [];
        $attachments = []; 

        // A. Proses SKAK
        if ($type->id === 1) {
            $additionalData = [
                'nama' => $request->nama,
                'nim' => $request->nim,
                'prodi' => $request->prodi,
                'semester' => $request->semester,
                'tingkat' => $request->tingkat,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
            ];

            // Proses File Lampiran General
            if ($request->hasFile('lampiran')) {
                $path = $request->file('lampiran')->store('lampiran-surat-keluar', 'public');
                $attachments[] = [
                    'filename' => 'Lampiran Pendukung',
                    'file_path' => $path,
                ];
            }
        } 
        // B. Proses Dinamis
        else {
            if (!empty($type->form_config)) {
                foreach ($type->form_config as $field) {
                    $key = Str::slug($field['label']);
                    
                    if ($field['type'] === 'file') {
                        if ($request->hasFile($key)) {
                            $path = $request->file($key)->store('lampiran-surat-keluar', 'public');
                            $attachments[] = [
                                'filename' => $field['label'],
                                'file_path' => $path,
                            ];
                        }
                    } else {
                        $additionalData[$key] = $request->input($key);
                    }
                }
            }
        }

        // 3. SIMPAN KE DATABASE
        $letter = OutgoingLetter::create([
            'user_id' => Auth::id(),
            'type_id' => $type->id,
            'status' => 'submitted',
            'letter_date' => now(),
            'subject' => $type->name, 
            'recipient' => '-', 
            'additional_data' => $additionalData,
        ]);

        if (!empty($attachments)) {
            $letter->attachments()->createMany($attachments);
        }

        return redirect()->route('user.letters.index')
            ->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function edit(OutgoingLetter $letter) 
    {
        // 1. SECURITY CHECK: Pastikan user hanya bisa edit surat miliknya sendiri
        if ($letter->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Ambil konfigurasi form dari Jenis Surat (LetterType)
        // Asumsi: di table letter_types ada kolom 'form_config' (JSON)
        $formConfig = $letter->type->form_config; 

        // 3. Kirim ke View
        return view('user.letters.edit', compact('letter', 'formConfig'));
    }

    public function update(Request $request, OutgoingLetter $letter)
    {
        // 1. SECURITY CHECK
        if ($letter->user_id !== Auth::id()) {
            abort(403);
        }

        $currentAdditionalData = $letter->additional_data ?? [];

        // ==========================================
        // SKENARIO 1: UPDATE SKAK (ID 1)
        // ==========================================
        if ($letter->type_id == 1) {
            $request->validate([
                'nim' => 'required|string',
                'prodi' => 'required|string',
                'semester' => 'required|numeric',
                'tingkat' => 'required|string',
                'tempat_lahir' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'required|string',
                'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Nullable saat update
            ]);

            // Merge data baru ke data lama
            $currentAdditionalData['nama'] = Auth::user()->name; // Pastikan nama tetap konsisten
            $currentAdditionalData['nim'] = $request->nim;
            $currentAdditionalData['prodi'] = $request->prodi;
            $currentAdditionalData['semester'] = $request->semester;
            $currentAdditionalData['tingkat'] = $request->tingkat;
            $currentAdditionalData['tempat_lahir'] = $request->tempat_lahir;
            $currentAdditionalData['tanggal_lahir'] = $request->tanggal_lahir;
            $currentAdditionalData['alamat'] = $request->alamat;

            // Cek file lampiran (Update jika ada yang baru)
            if ($request->hasFile('lampiran')) {
                // Hapus lampiran lama jika ada
                $oldAttachment = $letter->attachments()->where('filename', 'Lampiran Pendukung')->first();
                if ($oldAttachment) {
                    Storage::disk('public')->delete($oldAttachment->file_path);
                    $oldAttachment->delete();
                }

                // Simpan yang baru
                $path = $request->file('lampiran')->store('lampiran-surat-keluar', 'public');
                $letter->attachments()->create([
                    'filename' => 'Lampiran Pendukung',
                    'file_path' => $path
                ]);
            }
        } 
        
        // ==========================================
        // SKENARIO 2: UPDATE DINAMIS
        // ==========================================
        else {
            $config = $letter->type->form_config;
            if($config) {
                foreach ($config as $field) {
                    $key = Str::slug($field['label']);

                    if ($field['type'] === 'file') {
                        if ($request->hasFile($key)) {
                            // Cari attachment lama berdasarkan label
                            $oldAtt = $letter->attachments()->where('filename', $field['label'])->first();
                            if ($oldAtt) {
                                Storage::disk('public')->delete($oldAtt->file_path);
                                $oldAtt->delete();
                            }
                            
                            $path = $request->file($key)->store('letter_documents', 'public');
                            $letter->attachments()->create([
                                'filename' => $field['label'],
                                'file_path' => $path
                            ]);
                        }
                    } else {
                        $currentAdditionalData[$key] = $request->input($key);
                    }
                }
            }
        }

        // 3. SIMPAN PERUBAHAN
        $letter->update([
            'additional_data' => $currentAdditionalData, // Simpan ke additional_data (bukan 'data')
            'status' => 'submitted',  // Reset status
            'rejection_note' => null 
        ]);

        return redirect()->route('user.letters.index')
            ->with('success', 'Surat berhasil diperbarui dan diajukan ulang!');
    }
}