<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Tidak Valid - STIKES Bhayangkara</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    {{-- Font Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-stikes { background-color: #000275; }
        .text-stikes { color: #000275; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10 px-4">

    {{-- WRAPPER UTAMA --}}
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden relative">
        
        {{-- 1. HEADER BIRU (KONSISTEN) --}}
        <div class="bg-stikes pt-10 pb-16 px-6 text-center relative z-10">
            {{-- Aksen Background --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-32 h-32 bg-red-500 rounded-full blur-2xl"></div>
            </div>

            <div class="relative z-20">
                <div class="inline-block p-2 bg-white rounded-xl shadow-lg mb-4">
                    <img class="h-16 w-auto" src="{{ asset('images/logo.png') }}" alt="Logo STIKES">
                </div>
                <h1 class="text-xl font-bold text-white tracking-wide">STIKES BHAYANGKARA</h1>
                <p class="text-blue-200 text-xs font-medium uppercase tracking-widest mt-1">Sistem Verifikasi Digital</p>
            </div>
        </div>

        {{-- 2. KARTU PERINGATAN (OVERLAY) --}}
        <div class="relative z-20 -mt-10 px-6 pb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
                
                {{-- Icon Silang Merah Besar --}}
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-50 rounded-full mb-6 ring-8 ring-red-50/50 animate-pulse">
                    <svg class="w-10 h-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                {{-- Judul & Pesan --}}
                <h2 class="text-2xl font-bold text-gray-800 mb-3 tracking-tight">DOKUMEN TIDAK VALID</h2>
                
                <div class="bg-red-50 border border-red-100 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-800 font-medium leading-relaxed">
                        Maaf, sistem tidak dapat menemukan data dokumen dengan ID/Kode QR tersebut.
                    </p>
                </div>

                <p class="text-xs text-gray-400 leading-relaxed px-2">
                    Kemungkinan dokumen ini palsu, telah dihapus dari sistem, atau URL yang Anda akses salah. Harap hubungi bagian administrasi kampus untuk verifikasi manual.
                </p>

                {{-- Tombol Kembali (Opsional) --}}
                <div class="mt-8">
                    <a href="/" class="inline-flex items-center text-xs font-bold text-stikes hover:text-blue-700 transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Beranda
                    </a>
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