<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pilih Jenis Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($types as $type)
                <a href="{{ route('user.letters.create', ['type_id' => $type->id]) }}" 
                   class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition duration-200 border-l-4 border-indigo-500">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-2">{{ $type->name }}</h3>
                        <p class="text-sm text-gray-600">Kode: {{ $type->code }}</p>
                        <div class="mt-4 text-indigo-600 text-sm font-semibold flex items-center">
                            Buat Pengajuan &rarr;
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>