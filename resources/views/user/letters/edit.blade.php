<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pengajuan: ') }} {{ $letter->type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- FITUR 1: ALERT STATUS DITOLAK (Diambil dari snippet kamu) --}}
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
                
                {{-- Form mengarah ke update --}}
                <form action="{{ route('user.letters.update', $letter->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- TYPE ID HARUS TETAP SAMA (HIDDEN) --}}
                    <input type="hidden" name="type_id" value="{{ $letter->type_id }}">

                    @if(!empty($formConfig))
                        <div class="border-t pt-4 mt-2">
                            <h3 class="text-lg font-semibold mb-6 text-gray-800 border-b pb-2">
                                Perbarui Data Formulir
                            </h3>
                            
                            @foreach($formConfig as $field)
                                @php
                                    // 1. Logic Key (Harus sama persis dengan Create)
                                    $key = \Illuminate\Support\Str::slug($field['label']);
                                    $isRequired = $field['required'] ?? false;
                                    $label = $field['label'] . ($isRequired ? ' *' : '');
                                    
                                    // 2. Logic Data Existing (Ambil dari JSON)
                                    // Asumsi di Model Letter ada cast: protected $casts = ['data' => 'array'];
                                    $existingValue = $letter->data[$key] ?? null; 
                                @endphp

                                <div class="mb-6">
                                    <label for="{{ $key }}" class="block font-medium text-sm text-gray-700 mb-2">
                                        {{ $label }}
                                    </label>
                                    
                                    {{-- TIPE FILE --}}
                                    @if($field['type'] === 'file')
                                        
                                        {{-- Tampilkan info file lama jika ada --}}
                                        @if($existingValue)
                                            <div class="mb-2 flex items-center p-2 bg-blue-50 border border-blue-100 rounded-md text-sm text-blue-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="truncate max-w-xs mr-2">File saat ini tersimpan</span>
                                                <a href="{{ asset('storage/' . $existingValue) }}" target="_blank" class="underline font-semibold hover:text-blue-900">Lihat</a>
                                            </div>
                                        @endif

                                        <input type="file" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-indigo-50 file:text-indigo-700
                                                      hover:file:bg-indigo-100
                                                      border border-gray-300 rounded-md cursor-pointer"
                                               {{-- Jika file SUDAH ADA, input tidak wajib. Jika BELUM ADA, wajib (kalau required) --}}
                                               {{ ($isRequired && !$existingValue) ? 'required' : '' }}>
                                        
                                        <p class="text-xs text-gray-500 mt-1">
                                            @if($existingValue)
                                                Biarkan kosong jika tidak ingin mengubah file.
                                            @else
                                                Format: PDF/JPG/PNG (Max 2MB)
                                            @endif
                                        </p>

                                    {{-- TIPE TEXT / INPUT BIASA --}}
                                    @else
                                        <input type="text" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               {{-- Value prioritas: 1. Input baru (validasi gagal), 2. Data DB --}}
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
                        </div>
                    @else
                        {{-- Fallback jika config rusak --}}
                        <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <p>Konfigurasi formulir tidak ditemukan.</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-end mt-8 pt-6 border-t">
                        <a href="{{ route('user.letters.index') }}" class="text-gray-600 hover:text-gray-900 mr-4 font-semibold text-sm">
                            Batal
                        </a>
                        <x-primary-button class="ml-3">
                            {{ __('Update & Kirim Ulang') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>