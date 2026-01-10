<x-app-layout>
    {{-- HEADER: JUDUL & TOMBOL KEMBALI --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-indigo-700 leading-tight tracking-tight">
                    {{ __('Pilih Jenis Surat') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Silakan pilih template surat yang ingin Anda ajukan.</p>
            </div>

            {{-- TOMBOL KEMBALI (Style Secondary) --}}
            <a href="{{ route('user.letters.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    {{-- KONTEN UTAMA DENGAN BACKGROUND GRADIENT --}}
    <div class="py-12 bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- GRID CARD --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($types as $type)
                <a href="{{ route('user.letters.create', ['type_id' => $type->id]) }}" 
                   class="group relative block bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-indigo-50 hover:shadow-xl hover:shadow-indigo-100 hover:border-indigo-200 transition-all duration-300 ease-in-out transform hover:-translate-y-1">
                    
                    {{-- DEKORASI BACKGROUND ICON (ABSAR & PUDAR) --}}
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>

                    {{-- ICON UTAMA --}}
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm group-hover:shadow-indigo-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>

                    {{-- TEXT CONTENT --}}
                    <h3 class="font-bold text-lg text-gray-900 group-hover:text-indigo-700 transition-colors duration-200">
                        {{ $type->name }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-2 font-medium">
                        Kode: <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ $type->code }}</span>
                    </p>

                    {{-- TOMBOL ACTION SEMU --}}
                    <div class="mt-6 flex items-center text-indigo-600 text-sm font-bold group-hover:underline decoration-2 underline-offset-4">
                        Buat Pengajuan
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>