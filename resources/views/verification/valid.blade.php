<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen - STIKES Bhayangkara</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    {{-- Font Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-stikes { background-color: #000275; }
        .text-stikes { color: #000275; }
        .border-stikes { border-color: #000275; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10 px-4">

    {{-- WRAPPER UTAMA --}}
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden relative">
        
        {{-- 1. HEADER --}}
        <div class="bg-stikes pt-10 pb-16 px-6 text-center relative z-10">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
                <div class="absolute top-20 -left-10 w-32 h-32 bg-blue-400 rounded-full blur-2xl"></div>
            </div>

            <div class="relative z-20">
                <div class="inline-block p-2 bg-white rounded-xl shadow-lg mb-4">
                    <img class="h-16 w-auto" src="{{ asset('images/logo.png') }}" alt="Logo STIKES">
                </div>
                <h1 class="text-xl font-bold text-white tracking-wide">STIKES BHAYANGKARA</h1>
                <p class="text-blue-200 text-xs font-medium uppercase tracking-widest mt-1">Verifikasi Dokumen Resmi</p>
            </div>
        </div>

        {{-- 2. KARTU KONTEN --}}
        <div class="relative z-20 -mt-10 px-6 pb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                
                {{-- Status Badge --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-full border border-green-100 shadow-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="font-bold text-sm tracking-wide">DOKUMEN VALID</span>
                    </div>
                </div>

                {{-- Detail Informasi --}}
                <div class="space-y-5">
                    
                    {{-- Nomor Surat --}}
                    <div class="border-b border-gray-100 pb-4">
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Nomor Surat</p>
                        <p class="text-base font-bold text-gray-800 font-mono">{{ $letter->letter_number ?? '-' }}</p>
                    </div>

                    {{-- Perihal --}}
                    <div class="border-b border-gray-100 pb-4">
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Perihal</p>
                        <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ $letter->subject }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Tanggal --}}
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Tanggal</p>
                            <p class="text-sm font-semibold text-stikes">
                                {{ $letter->letter_date->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        
                        {{-- Penanda Tangan --}}
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Penanda Tangan</p>
                            <p class="text-sm font-bold text-gray-800">{{ $letter->signer->user->name }}</p>
                            <p class="text-[10px] text-gray-500 truncate">{{ $letter->signer->position }}</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer Teknis --}}
            <div class="mt-6 text-center">
             
                <div class="flex items-center justify-center gap-1 mt-1 text-[10px] text-blue-600 font-medium bg-blue-50 py-1 px-3 rounded-full inline-flex">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Terverifikasi oleh Sistem Digital
                </div>
            </div>

        </div>

    </div>

    {{-- Footer Copyright --}}
    <div class="mt-8 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} STIKES Bhayangkara Makassar
    </div>

</body>
</html>