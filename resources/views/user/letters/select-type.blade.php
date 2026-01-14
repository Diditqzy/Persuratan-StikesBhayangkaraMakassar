<x-app-layout>
    {{-- HEADER: JUDUL & TOMBOL KEMBALI --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-indigo-700 leading-tight tracking-tight">
                    {{ __('Pilih Jenis Surat') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Silakan pilih surat yang ingin Anda ajukan.</p>
            </div>

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('user.letters.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-indigo-600 rounded-xl hover:bg-indigo-700 hover:scale-105 hover:shadow-xl shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- GRID CARD --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 b">
                @foreach($types as $type)
                <a href="{{ route('user.letters.create', ['type_id' => $type->id]) }}" 
                   class="group relative block bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-indigo-500 hover:bg-indigo-50 hover:shadow-xl hover:shadow-indigo-100 hover:border-indigo-700 transition-all duration-300 ease-in-out transform hover:-translate-y-1">

                    {{-- ICON UTAMA --}}
                    <div class="w-14 h-14  rounded-2xl flex items-center justify-center mb-5 bg-indigo-600 text-white transition-colors duration-300 shadow-sm group-hover:shadow-indigo-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>

                    {{-- TEXT CONTENT --}}
                    <h3 class="font-bold text-lg text-gray-900 group-hover:text-indigo-700 transition-colors duration-200">
                        {{ $type->name }}
                    </h3>
                    

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