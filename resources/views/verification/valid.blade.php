<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Surat - STIKES Bhayangkara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden border-t-4 border-green-500">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-2">DOKUMEN VALID</h2>
            <p class="text-sm text-gray-500 mb-6">Surat ini terdaftar resmi di sistem STIKES Bhayangkara Makassar.</p>

            <div class="text-left bg-gray-50 p-4 rounded-lg text-sm space-y-3">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Nomor Surat</span>
                    <span class="block font-medium text-gray-800">{{ $letter->letter_number ?? '(Belum Finalisasi)' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Perihal</span>
                    <span class="block font-medium text-gray-800">{{ $letter->subject }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Tanggal Surat</span>
                    <span class="block font-medium text-gray-800">{{ $letter->letter_date->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Penanda Tangan</span>
                    <span class="block font-medium text-gray-800">{{ $letter->signer->user->name }}</span>
                    <span class="text-xs text-gray-500">({{ $letter->signer->position }})</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        VERIFIED / ASLI
                    </span>
                </div>
            </div>
        </div>
        <div class="bg-gray-100 px-6 py-3 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} Sistem Persuratan STIKES
        </div>
    </div>
</body>
</html>