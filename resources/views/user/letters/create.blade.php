<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Pengajuan Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('user.letters.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="type_id" :value="__('Jenis Surat')" />
                        <select name="type_id" id="type_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">-- Pilih Jenis Surat --</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type_id')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="recipient" :value="__('Tujuan Surat (Kepada Yth.)')" />
                        <x-text-input id="recipient" class="block mt-1 w-full" type="text" name="recipient" :value="old('recipient')" required placeholder="Contoh: Dekan Fakultas Teknik" />
                        <x-input-error :messages="$errors->get('recipient')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="subject" :value="__('Perihal / Judul')" />
                        <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required placeholder="Contoh: Permohonan Izin Penelitian" />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="content_data" :value="__('Keterangan / Isi Ringkas')" />
                        <textarea id="content_data" name="content_data" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required placeholder="Jelaskan detail keperluan surat di sini...">{{ old('content_data') }}</textarea>
                        <x-input-error :messages="$errors->get('content_data')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('user.letters.index') }}" class="text-gray-600 hover:text-gray-900 mr-4 font-semibold">Batal</a>
                        <x-primary-button class="ml-3">
                            {{ __('Ajukan Surat') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>