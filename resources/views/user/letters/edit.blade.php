<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-indigo-700 tracking-tight">
                    Edit Pengajuan Surat
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ $letter->type->name }}
                    </span>
                    <span class="text-xs text-gray-500">| Perbaiki data sesuai catatan admin</span>
                </div>
            </div>

            <a href="{{ route('user.letters.index') }}"
               class="inline-flex items-center justify-center px-6 py-3 font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 hover:scale-105 transition-all shadow-indigo-500/30 shadow focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- ALERT --}}
@if($letter->status === 'rejected')
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r-md" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-bold">Pengajuan Ditolak!</p>
                            <p class="text-sm mt-1">Alasan: <span class="font-semibold">"{{ $letter->rejection_note }}"</span></p>
                            <p class="text-sm mt-2 italic">Silakan perbaiki data di bawah ini sesuai catatan, lalu kirim ulang.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('user.letters.update', $letter->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    {{-- TYPE ID HIDDEN --}}
                    <input type="hidden" name="type_id" value="{{ $letter->type_id }}">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-6 text-gray-800 border-b pb-2">
                            Perbarui Data Pemohon
                        </h3>

                        @if($letter->type_id == 1)
                            
                            @php
                                $data = $letter->additional_data ?? [];
                                // Cari attachment lampiran (jika ada)
                                $lampiranFile = $letter->attachments->where('filename', 'Lampiran Pendukung')->first();
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nama --}}
                                <div>
                                    <x-input-label for="nama" value="Nama Lengkap" />
                                    <x-text-input id="nama" class="block mt-1 w-full bg-gray-100" type="text" name="nama" :value="Auth::user()->name" required />
                                </div>
                                {{-- NIM --}}
                                <div>
                                    <x-input-label for="nim" value="NIM" />
                                    <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim', $data['nim'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('nim')" class="mt-2" />
                                </div>
                                {{-- Prodi --}}
                                <div>
                                    <x-input-label for="prodi" value="Program Studi" />
                                    <x-text-input id="prodi" class="block mt-1 w-full" type="text" name="prodi" :value="old('prodi', $data['prodi'] ?? '')" required />
                                </div>
                                {{-- Semester & Tingkat --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="semester" value="Semester" />
                                        <x-text-input id="semester" class="block mt-1 w-full" type="number" name="semester" :value="old('semester', $data['semester'] ?? '')" required />
                                    </div>
                                    <div>
                                        <x-input-label for="tingkat" value="Tingkat" />
                                        <x-text-input id="tingkat" class="block mt-1 w-full" type="text" name="tingkat" :value="old('tingkat', $data['tingkat'] ?? '')" required />
                                    </div>
                                </div>
                                {{-- TTL --}}
                                <div>
                                    <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                                    <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir" :value="old('tempat_lahir', $data['tempat_lahir'] ?? '')" required />
                                </div>
                                <div>
                                    <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                                    <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir" :value="old('tanggal_lahir', $data['tanggal_lahir'] ?? '')" required />
                                </div>
                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="alamat" value="Alamat Lengkap" />
                                    <textarea id="alamat" name="alamat" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" rows="2" required>{{ old('alamat', $data['alamat'] ?? '') }}</textarea>
                                </div>
                                
                                {{-- Upload Lampiran General (Khusus ID 1) --}}
                                <div class="md:col-span-2 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                    <x-input-label for="lampiran" value="Lampiran Dokumen" class="text-base font-bold text-gray-800" />
                                    
                                    {{-- Tampilkan File Lama --}}
                                    @if($lampiranFile)
                                        <div class="mb-2 flex items-center text-sm text-blue-700 bg-blue-50 p-2 rounded border border-blue-100">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="mr-2">File tersimpan:</span>
                                            <a href="{{ asset('storage/' . $lampiranFile->file_path) }}" target="_blank" class="font-bold underline hover:text-blue-900">Lihat File</a>
                                        </div>
                                    @endif

                                    <input type="file" name="lampiran" id="lampiran" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-gray-600 file:text-white hover:file:bg-gray-700"
                                    {{-- Required hanya jika file lama tidak ada --}}
                                    {{ !$lampiranFile ? 'required' : '' }}>
                                    
                                    <p class="text-xs text-gray-600 mt-1">
                                        @if($lampiranFile)
                                            Biarkan kosong jika tidak ingin mengubah file.
                                        @else
                                            Sertakan dokumen pendukung (PDF/Gambar, Max 5MB).
                                        @endif
                                    </p>
                                    <x-input-error :messages="$errors->get('lampiran')" class="mt-2" />
                                </div>
                            </div>

                        {{-- SKENARIO 2: SURAT DINAMIS (CUSTOM)      --}}
                        @elseif(!empty($formConfig))
                            @foreach($formConfig as $field)
                                @php
                                    $key = \Illuminate\Support\Str::slug($field['label']);
                                    $isRequired = $field['required'] ?? false;
                                    $label = $field['label'] . ($isRequired ? ' *' : '');
                                    
                                    $existingValue = $letter->additional_data[$key] ?? null; 
                                @endphp

                                <div class="mb-6">
                                    <label for="{{ $key }}" class="block font-medium text-sm text-gray-700 mb-2">
                                        {{ $label }}
                                    </label>
                                    
                                    @if($field['type'] === 'file')
                                        {{-- Cek apakah ada file lama untuk field ini --}}
                                        @php
                                            $existingFile = $letter->attachments->where('filename', $field['label'])->first();
                                        @endphp

                                        @if($existingFile)
                                            <div class="mb-2 flex items-center p-2 bg-blue-50 border border-blue-100 rounded-md text-sm text-blue-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <a href="{{ asset('storage/' . $existingFile->file_path) }}" target="_blank" class="underline font-semibold hover:text-blue-900">Lihat File Tersimpan</a>
                                            </div>
                                        @endif

                                        <input type="file" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md cursor-pointer"
                                               {{ ($isRequired && !$existingFile) ? 'required' : '' }}>
                                        <p class="text-xs text-gray-500 mt-1">Format: PDF/JPG/PNG (Max 5MB)</p>
                                    @else
                                        <input type="text" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               value="{{ old($key, $existingValue) }}"
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"
                                               placeholder="Isi jawaban..."
                                               {{ $isRequired ? 'required' : '' }}>
                                    @endif

                                    @error($key)
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        
                        {{-- ERROR HANDLING --}}
                        @else
                            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                <p>Konfigurasi formulir tidak ditemukan atau rusak.</p>
                            </div>
                        @endif
                    </div>
                    

                    <div class="flex items-center justify-end pt-6 border-t border-gray-100 gap-4">
                            <a href="{{ route('user.letters.index') }}" class="text-gray-500 hover:text-gray-700 font-semibold text-sm transition-colors duration-200">
                                Batal
                            </a>
                        <x-primary-button type="submit" class="inline-flex items-center justify-center px-6  py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            {{ __('Update & Kirim Ulang') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>