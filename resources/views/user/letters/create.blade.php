<x-app-layout>
    {{-- HEADER --}}
<x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-indigo-700 leading-tight tracking-tight">
                    {{ __('Pengajuan Surat Baru') }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ $type->name }}
                    </span>
                    <span class="text-xs text-gray-500">| Silakan lengkapi form di bawah ini.</span>
                </div>
            </div>

            {{-- TOMBOL KEMBALI KE PILIH SURAT --}}
            <a href="{{ route('user.letters.create') }}" 
               class="relative inline-flex items-center justify-center px-6 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-indigo-600 rounded-xl hover:bg-indigo-700 hover:scale-105 hover:shadow-xl shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Pilih Surat Lain
            </a>
        </div>
    </x-slot>

    {{-- BACKGROUND --}}
    <div class="py-12 bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- CARD FORM --}}
            <div class="bg-white border border-indigo-200 rounded-2xl shadow-lg p-6 sm:p-8">

                <form action="{{ route('user.letters.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type_id" value="{{ $type->id }}">

                    {{-- SECTION TITLE --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-3">
                            Lengkapi Data Pemohon
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Pastikan seluruh data diisi dengan benar sebelum mengirim pengajuan.
                        </p>
                    </div>

                    {{-- SKENARIO 1 --}}
                    @if($type->id == 1)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nama --}}
                            <div>
                                <x-input-label for="nama" value="Nama Lengkap" />
                                <x-text-input
                                    id="nama"
                                    class="block mt-1 w-full bg-gray-100"
                                    type="text"
                                    name="nama"
                                    :value="Auth::user()->name"
                                    readonly
                                />
                            </div>

                            {{-- NIM --}}
                            <div>
                                <x-input-label for="nim" value="NIM" />
                                <x-text-input
                                    id="nim"
                                    class="block mt-1 w-full"
                                    type="text"
                                    name="nim"
                                    :value="old('nim')"
                                    required
                                    placeholder="Masukkan NIM"
                                />
                                <x-input-error :messages="$errors->get('nim')" class="mt-2" />
                            </div>

                            {{-- Prodi --}}
                            <div>
                                <x-input-label for="prodi" value="Program Studi" />
                                <x-text-input
                                    id="prodi"
                                    class="block mt-1 w-full"
                                    type="text"
                                    name="prodi"
                                    :value="old('prodi')"
                                    required
                                />
                            </div>

                            {{-- Semester & Tingkat --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="semester" value="Semester" />
                                    <x-text-input
                                        id="semester"
                                        class="block mt-1 w-full"
                                        type="number"
                                        name="semester"
                                        :value="old('semester')"
                                        required
                                    />
                                </div>
                                <div>
                                    <x-input-label for="tingkat" value="Tingkat" />
                                    <x-text-input
                                        id="tingkat"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="tingkat"
                                        :value="old('tingkat')"
                                        required
                                    />
                                </div>
                            </div>

                            {{-- TTL --}}
                            <div>
                                <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                                <x-text-input
                                    id="tempat_lahir"
                                    class="block mt-1 w-full"
                                    type="text"
                                    name="tempat_lahir"
                                    :value="old('tempat_lahir')"
                                    required
                                />
                            </div>
                            <div>
                                <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                                <x-text-input
                                    id="tanggal_lahir"
                                    class="block mt-1 w-full"
                                    type="date"
                                    name="tanggal_lahir"
                                    :value="old('tanggal_lahir')"
                                    required
                                />
                            </div>

                            {{-- Alamat --}}
                            <div class="md:col-span-2">
                                <x-input-label for="alamat" value="Alamat Lengkap" />
                                <textarea
                                    id="alamat"
                                    name="alamat"
                                    rows="3"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    placeholder="Alamat sesuai KTP / Domisili"
                                >{{ old('alamat') }}</textarea>
                            </div>

                            {{-- Lampiran --}}
                            <div class="md:col-span-2 p-5 bg-indigo-50 border border-indigo-200 rounded-xl">
                                <x-input-label
                                    for="lampiran"
                                    value="Lampiran Dokumen (Wajib)"
                                    class="font-bold text-gray-800"
                                />
                                <input
                                    type="file"
                                    name="lampiran"
                                    id="lampiran"
                                    required
                                    class="mt-2 block w-full text-sm text-gray-600
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:bg-indigo-600 file:text-white
                                    hover:file:bg-indigo-700"
                                >
                                <p class="text-xs text-gray-600 mt-2">
                                    Format PDF/JPG/PNG, maksimal 5MB.
                                </p>
                                <x-input-error :messages="$errors->get('lampiran')" class="mt-2" />
                            </div>
                        </div>

                    {{-- SKENARIO 2 --}}
                    @elseif(!empty($formConfig))
                        @foreach($formConfig as $field)
                            @php
                                $key = \Illuminate\Support\Str::slug($field['label']);
                                $required = $field['required'] ?? false;
                            @endphp

                            <div class="mb-6">
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    {{ $field['label'] }} @if($required)<span class="text-red-500">*</span>@endif
                                </label>
                                

                                @if($field['type'] === 'file')
                                    <input
                                        type="file"
                                        name="{{ $key }}"
                                        class="block w-full text-sm text-gray-600
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-lg file:border-0
                                        file:bg-indigo-100 file:text-indigo-700
                                        hover:file:bg-indigo-200
                                        border border-gray-300 rounded-lg"
                                        {{ $required ? 'required' : '' }}
                                    >
                                @else
                                    <input
                                        type="text"
                                        name="{{ $key }}"
                                        value="{{ old($key) }}"
                                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Masukkan jawaban"
                                        {{ $required ? 'required' : '' }}
                                    >
                                @endif
                            </div>
                        @endforeach

                    {{-- SKENARIO 3 --}}
                    @else
                        <div class="text-center font-bold py-10 bg-indigo-600 rounded-xl border-2 border-blue-500 border-dashed text-white">
                            Formulir belum tersedia. Silakan hubungi admin.
                        </div>
                    @endif

                    {{-- ACTION --}}
                        <div class="flex items-center justify-end pt-6 border-t border-gray-100 gap-4">
                            <a href="{{ route('user.letters.index') }}" class="text-gray-500 hover:text-gray-700 font-semibold text-sm transition-colors duration-200">
                                Batal
                            </a>
                            
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                {{ __('Kirim Pengajuan') }}
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
