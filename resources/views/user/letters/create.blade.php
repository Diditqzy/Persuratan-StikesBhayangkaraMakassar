<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pengajuan: {{ $type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('user.letters.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type_id" value="{{ $type->id }}">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-6 text-gray-800 border-b pb-2">
                            Lengkapi Data Pemohon
                        </h3>

                        {{-- SKENARIO 1: SURAT KETERANGAN AKTIF KULIAH (ID 1) --}}
                        @if($type->id == 1)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nama (Readonly) --}}
                                <div>
                                    <x-input-label for="nama" value="Nama Lengkap" />
                                    <x-text-input id="nama" class="block mt-1 w-full bg-gray-100" type="text" name="nama" :value="Auth::user()->name" required />
                                </div>
                                {{-- NIM --}}
                                <div>
                                    <x-input-label for="nim" value="NIM" />
                                    <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim')" required placeholder="Masukkan NIM" />
                                    <x-input-error :messages="$errors->get('nim')" class="mt-2" />
                                </div>
                                {{-- Prodi --}}
                                <div>
                                    <x-input-label for="prodi" value="Program Studi" />
                                    <x-text-input id="prodi" class="block mt-1 w-full" type="text" name="prodi" :value="old('prodi')" required />
                                </div>
                                {{-- Semester & Tingkat --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="semester" value="Semester" />
                                        <x-text-input id="semester" class="block mt-1 w-full" type="number" name="semester" :value="old('semester')" required />
                                    </div>
                                    <div>
                                        <x-input-label for="tingkat" value="Tingkat" />
                                        <x-text-input id="tingkat" class="block mt-1 w-full" type="text" name="tingkat" :value="old('tingkat')" required />
                                    </div>
                                </div>
                                {{-- TTL --}}
                                <div>
                                    <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                                    <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir" :value="old('tempat_lahir')" required />
                                </div>
                                <div>
                                    <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                                    <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir" :value="old('tanggal_lahir')" required />
                                </div>
                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="alamat" value="Alamat Lengkap" />
                                    <textarea id="alamat" name="alamat" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" rows="2" required placeholder="Alamat sesuai KTP/Domisili">{{ old('alamat') }}</textarea>
                                </div>
                                
                                {{-- Upload Lampiran General --}}
                                <div class="md:col-span-2 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                    <x-input-label for="lampiran" value="Lampiran Dokumen (Wajib)" class="text-base font-bold text-gray-800" />
                                    <input type="file" name="lampiran" id="lampiran" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-gray-600 file:text-white hover:file:bg-gray-700" required>
                                    <p class="text-xs text-gray-600 mt-1">Sertakan dokumen pendukung yang diperlukan (PDF/Gambar, Max 5MB).</p>
                                    <x-input-error :messages="$errors->get('lampiran')" class="mt-2" />
                                </div>
                            </div>

                        {{-- SKENARIO 2: SURAT DINAMIS (CUSTOM) --}}
                        @elseif(!empty($formConfig))
                            @foreach($formConfig as $field)
                                @php
                                    $key = \Illuminate\Support\Str::slug($field['label']);
                                    $isRequired = $field['required'] ?? false;
                                    $label = $field['label'] . ($isRequired ? ' *' : '');
                                @endphp

                                <div class="mb-6">
                                    <label for="{{ $key }}" class="block font-medium text-sm text-gray-700 mb-2">
                                        {{ $label }}
                                    </label>
                                    
                                    @if($field['type'] === 'file')
                                        <input type="file" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md cursor-pointer"
                                               {{ $isRequired ? 'required' : '' }}>
                                        <p class="text-xs text-gray-500 mt-1">Format: PDF/JPG/PNG (Max 5MB)</p>
                                    @else
                                        <input type="text" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               value="{{ old($key) }}"
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"
                                               placeholder="Jawaban Anda"
                                               {{ $isRequired ? 'required' : '' }}>
                                    @endif

                                    @error($key)
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        
                        {{-- SKENARIO 3: ERROR / KOSONG --}}
                        @else
                            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                <p>Formulir belum tersedia. Hubungi Admin.</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end mt-8 pt-6 border-t">
                        <a href="{{ route('user.letters.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold underline decoration-2 underline-offset-4 text-sm mr-4">
                            Batal
                        </a>
                        <x-primary-button class="px-6 py-2 text-base">
                            {{ __('Kirim Pengajuan') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>