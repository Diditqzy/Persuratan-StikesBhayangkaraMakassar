<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Surat Saya') }}
            </h2>
            <a href="{{ route('user.letters.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-md transition duration-150 ease-in-out">
                + Buat Pengajuan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Jenis / Perihal
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($letters as $letter)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">
                                        {{ $letter->created_at->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 font-bold">{{ $letter->type->name }}</p>
                                    <p class="text-gray-600 text-xs">{{ Str::limit($letter->subject, 40) }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    @php
                                        $colors = [
                                            'submitted' => 'bg-blue-200 text-blue-800',
                                            'draft' => 'bg-gray-200 text-gray-800',
                                            'pending_approval' => 'bg-yellow-200 text-yellow-800',
                                            'approved' => 'bg-green-200 text-green-800',
                                            'rejected' => 'bg-red-200 text-red-800',
                                            'revision_needed' => 'bg-red-200 text-red-800',
                                            'completed' => 'bg-blue-600 text-white',
                                        ];
                                        $labels = [
                                            'submitted' => 'Sedang Diverifikasi',
                                            'draft' => 'Sedang Diproses',
                                            'pending_approval' => 'Menunggu TTD',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'revision_needed' => 'Revisi',
                                            'completed' => 'Selesai',
                                        ];
                                    @endphp
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full text-xs {{ $colors[$letter->status] ?? 'bg-gray-200' }}">
                                        {{ $labels[$letter->status] ?? $letter->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    
                                    {{-- TOMBOL DOWNLOAD (Jika Selesai) --}}
                                    @if($letter->status == 'completed' && $letter->final_file_path)
                                        <a href="{{ Storage::url($letter->final_file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                            Unduh
                                        </a>
                                    
                                    {{-- TOMBOL AKSI (Edit & Delete) - Hanya jika Submitted / Rejected --}}
                                    @elseif(in_array($letter->status, ['submitted', 'rejected']))
                                        <div class="flex gap-2">
                                            {{-- Edit --}}
                                            <a href="{{ route('user.letters.edit', $letter->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                                                Edit / Cek
                                            </a>

                                            {{-- Delete --}}
                                            <form action="{{ route('user.letters.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Sedang Diproses Admin</span>
                                    @endif

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                    Belum ada pengajuan surat. Silakan buat baru.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $letters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>