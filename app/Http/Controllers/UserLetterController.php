<?php

namespace App\Http\Controllers;

use App\Models\LetterType;
use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserLetterController extends Controller
{
    /**
     * Menampilkan daftar surat milik user yang login.
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
     * Menampilkan form pembuatan surat.
     */
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

    /**
     * Menyimpan surat baru ke database.
     */
    public function store(Request $request)
    {
        $type = LetterType::findOrFail($request->type_id);
        
        $rules = ['type_id' => 'required|exists:letter_types,id'];

        if ($type->id === 1) { 
            $rules = array_merge($rules, [
                'nama' => 'required|string', 'nim' => 'required|string',
                'prodi' => 'required|string', 'semester' => 'required|numeric',
                'tingkat' => 'required|string', 'tempat_lahir' => 'required|string',
                'tanggal_lahir' => 'required|date', 'alamat' => 'required|string',
                'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', 
            ]);
        } else { 
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

        $additionalData = [];
        $attachments = [];

        try {
            DB::beginTransaction();

            if ($type->id === 1) {
                $additionalData = $request->only(['nama', 'nim', 'prodi', 'semester', 'tingkat', 'tempat_lahir', 'tanggal_lahir', 'alamat']);
                $additionalData['nama'] = Auth::user()->name; 
                
                if ($request->hasFile('lampiran')) {
                    $path = $request->file('lampiran')->store('lampiran-surat-keluar', 'public');
                    $attachments[] = [
                        'filename' => 'Lampiran Pendukung',
                        'file_path' => $path,
                    ];
                }
            } else {
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

            $letter = OutgoingLetter::create([
                'user_id' => Auth::id(),
                'type_id' => $type->id,
                'status' => 'submitted',
                'letter_date' => now(),
                'subject' => $type->name, 
                'recipient' => '-', 
                'additional_data' => $additionalData,
                'final_file_path' => null, 
            ]);

            if (!empty($attachments)) {
                $letter->attachments()->createMany($attachments);
            }

            DB::commit();

            return redirect()->route('user.letters.index')
                ->with('success', 'Pengajuan berhasil dikirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($attachments as $att) {
                Storage::disk('public')->delete($att['file_path']);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan surat: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form edit surat.
     */
    public function edit(OutgoingLetter $letter) 
    {
        if ($letter->user_id !== Auth::id()) abort(403);
        
        if (!in_array($letter->status, ['submitted', 'rejected', 'revision_needed'])) {
            return redirect()->route('user.letters.index')
                ->with('error', 'Surat yang sedang diproses atau sudah selesai tidak dapat diedit.');
        }

        $formConfig = $letter->type->form_config ?? [];
        return view('user.letters.edit', compact('letter', 'formConfig'));
    }

    /**
     * Memperbarui surat di database.
     */
    public function update(Request $request, OutgoingLetter $letter)
    {
        if ($letter->user_id !== Auth::id()) abort(403);

        if (!in_array($letter->status, ['submitted', 'rejected', 'revision_needed'])) {
            return redirect()->route('user.letters.index')
                ->with('error', 'Surat tidak dapat diedit karena statusnya sudah berubah.');
        }

        $currentData = $letter->additional_data ?? [];
        
        DB::beginTransaction();
        try {
            if ($letter->type_id == 1) {
                $request->validate([
                    'nim' => 'required', 'prodi' => 'required', 'semester' => 'required',
                    'tingkat' => 'required', 'tempat_lahir' => 'required', 'tanggal_lahir' => 'required',
                    'alamat' => 'required', 'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);

                $currentData = array_merge($currentData, $request->only(['nim', 'prodi', 'semester', 'tingkat', 'tempat_lahir', 'tanggal_lahir', 'alamat']));
                $currentData['nama'] = Auth::user()->name;

                if ($request->hasFile('lampiran')) {
                    $oldAttachment = $letter->attachments()->where('filename', 'Lampiran Pendukung')->first();
                    if ($oldAttachment) {
                        Storage::disk('public')->delete($oldAttachment->file_path);
                        $oldAttachment->delete();
                    }

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
                                $oldAttachment = $letter->attachments()->where('filename', $field['label'])->first();
                                if ($oldAttachment) {
                                    Storage::disk('public')->delete($oldAttachment->file_path);
                                    $oldAttachment->delete();
                                }
                                
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

            $letter->update([
                'additional_data' => $currentData,
                'status' => 'submitted', 
                'rejection_note' => null,
                'rejected_at' => null,
                'rejected_by' => null,
            ]);

            DB::commit();
            return redirect()->route('user.letters.index')
                ->with('success', 'Surat berhasil diperbarui dan diajukan ulang!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui surat: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus surat dari database (Method yang sebelumnya hilang).
     */
    public function destroy(string $id)
    {
        $letter = OutgoingLetter::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
        if (!in_array($letter->status, ['submitted', 'rejected', 'revision_needed'])) {
            return back()->with('error', 'Surat yang sedang diproses atau sudah selesai tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($letter) {
                if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
                    Storage::disk('public')->delete($letter->file_path);
                }
                if (method_exists($letter, 'attachments')) {
                    foreach ($letter->attachments as $attachment) {
                        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                            Storage::disk('public')->delete($attachment->file_path);
                        }
                        $attachment->delete();
                    }
                }

                if (method_exists($letter, 'histories')) {
                    $letter->histories()->delete(); 
                }
                $letter->delete();
            });

            return redirect()->route('user.letters.index')
                ->with('success', 'Pengajuan surat berhasil dibatalkan dan dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus surat: ' . $e->getMessage());
        }
    }
}