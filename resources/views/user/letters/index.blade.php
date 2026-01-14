<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-indigo-700 leading-tight tracking-tight">
                    {{ __('Surat Saya') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Kelola riwayat dan status pengajuan surat Anda.</p>
            </div>
            
            {{-- TOMBOL CREATE --}}
            <a href="{{ route('user.letters.create') }}" 
               class="group relative inline-flex items-center justify-center px-6 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-indigo-600 rounded-xl hover:bg-indigo-700 hover:scale-105 hover:shadow-xl shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                <span class="relative flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Pengajuan Baru
                </span>
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-8 lg:px-9">
            
            {{-- ALERT SUCCESS --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition 
                     class="mb-6 bg-white border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-lg shadow-emerald-100 flex items-start justify-between relative overflow-hidden">
                    <div class="absolute inset-0 bg-emerald-50 opacity-50"></div> {{-- Background Tint --}}
                    <div class="relative flex items-start z-10">
                        <div class="bg-emerald-100 p-2 rounded-full text-emerald-600 mr-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <strong class="font-bold text-emerald-900 text-lg">Berhasil!</strong>
                            <p class="text-emerald-700">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="relative z-10 text-emerald-400 hover:text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            {{-- MAIN CARD --}}
            <div class="bg-white overflow-hidden shadow-xl shadow-indigo-100/50 sm:rounded-3xl border border-indigo-50 relative">
                
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Tanggal Buat
                                </th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Detail Surat
                                </th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($letters as $letter)
                            <tr class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                                
                                {{-- KOLOM TANGGAL --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col items-center justify-center min-w-[60px] p-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200">
                                            <span class="text-[10px] font-bold uppercase tracking-wider">
                                                {{ $letter->created_at->timezone('Asia/Makassar')->format('M') }}
                                            </span>
                                            <span class="text-2xl font-black leading-none">
                                                {{ $letter->created_at->timezone('Asia/Makassar')->format('d') }}
                                            </span>
                                            <span class="text-[10px] font-medium opacity-80">
                                                {{ $letter->created_at->timezone('Asia/Makassar')->format('Y') }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-400 font-medium">
                                            {{ $letter->created_at->timezone('Asia/Makassar')->format('H:i') }} WITA
                                        </div>
                                    </div>
                                </td>

                                {{-- KOLOM JENIS & PERIHAL --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="inline-flex w-fit items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-100 text-blue-800 mb-1 border border-blue-200">
                                            {{ $letter->type->name  }}
                                        </span>
                                      
                                    </div>
                                </td>

                                {{-- KOLOM STATUS --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        // Konfigurasi Warna Badge yang Lebih Cerah
                                        $statusConfig = [
                                            'submitted' => [
                                                'classes' => 'bg-sky-100 text-sky-700 border-sky-200 ring-sky-200', 
                                                'label' => 'Verifikasi',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
                                            ],
                                            'draft' => [
                                                'classes' => 'bg-gray-100 text-gray-700 border-gray-200 ring-gray-200', 
                                                'label' => 'Draft',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />'
                                            ],
                                            'pending_approval' => [
                                                'classes' => 'bg-amber-100 text-amber-700 border-amber-200 ring-amber-200', 
                                                'label' => 'Menunggu TTD',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
                                            ],
                                            'approved' => [
                                                'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200 ring-emerald-200', 
                                                'label' => 'Disetujui',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                                            ],
                                            'rejected' => [
                                                'classes' => 'bg-rose-100 text-rose-700 border-rose-200 ring-rose-200', 
                                                'label' => 'Ditolak',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                                            ],
                                            'revision_needed' => [
                                                'classes' => 'bg-orange-100 text-orange-700 border-orange-200 ring-orange-200', 
                                                'label' => 'Revisi',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />'
                                            ],
                                            'completed' => [
                                                'classes' => 'bg-violet-100 text-violet-700 border-violet-200 ring-violet-200', 
                                                'label' => 'Selesai',
                                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />'
                                            ],
                                        ];
                                        
                                        $cfg = $statusConfig[$letter->status] ?? $statusConfig['draft'];
                                    @endphp
                                    
                                    <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border ring-1 ring-inset {{ $cfg['classes'] }}">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $cfg['icon'] !!}
                                        </svg>
                                        {{ $cfg['label'] }}
                                    </div>
                                </td>

                                {{-- KOLOM AKSI --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    
                                    @if(in_array($letter->status, ['completed']))
                                        <a href="{{ route('letters.print', $letter->id) }}" target="_blank" 
                                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg shadow-md shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5">
                                            <svg class="w-4 h-4 mr-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Unduh PDF
                                        </a>
                                    
                                    @elseif(in_array($letter->status, ['submitted', 'rejected', 'revision_needed']))
                                        <div class="flex items-center gap-2">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('user.letters.edit', $letter->id) }}" 
                                               class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition-colors tooltip" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('user.letters.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition-colors" title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>

                                    {{-- LAINNYA (TERKUNCI) --}}
                                    @else
                                        <div class="flex items-center text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-not-allowed">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span class="text-xs font-medium">Proses</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        {{-- Ilustrasi Kosong Colorful --}}
                                        <div class="relative w-24 h-24 mb-4">
                                            <div class="absolute inset-0 bg-blue-100 rounded-full animate-pulse"></div>
                                            <div class="absolute inset-2 bg-indigo-100 rounded-full"></div>
                                            <svg class="relative z-10 w-12 h-12 text-indigo-500 top-6 left-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        
                                        <h3 class="text-xl font-bold text-gray-900">Belum Ada Pengajuan</h3>
                                        <p class="text-gray-500 mt-2 max-w-sm mx-auto">Anda belum pernah membuat surat. Yuk, buat pengajuan surat pertamamu sekarang!</p>
                                        
                                        <a href="{{ route('user.letters.create') }}" class="mt-6 inline-flex items-center text-indigo-600 hover:text-indigo-800 font-bold border-b-2 border-indigo-200 hover:border-indigo-600 transition-all pb-0.5">
                                            + Buat Pengajuan Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if($letters->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-gray-200">
                        {{ $letters->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>