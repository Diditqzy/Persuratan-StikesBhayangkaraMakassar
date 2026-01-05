<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pengajuan: {{ $type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('user.letters.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <input type="hidden" name="type_id" value="{{ $type->id }}">

                    @if(!empty($formConfig))
                        <div class="border-t pt-4 mt-2">
                            <h3 class="text-lg font-semibold mb-6 text-gray-800 border-b pb-2">
                                Lengkapi Data Berikut
                            </h3>
                            
                            @foreach($formConfig as $field)
                                @php
                                    // Slugify label untuk name & id
                                    $key = \Illuminate\Support\Str::slug($field['label']);
                                    $isRequired = $field['required'] ?? false; // Default false jika key tidak ada
                                    $label = $field['label'] . ($isRequired ? ' *' : '');
                                @endphp

                                <div class="mb-6">
                                    <label for="{{ $key }}" class="block font-medium text-sm text-gray-700 mb-2">
                                        {{ $label }}
                                    </label>
                                    
                                    @if($field['type'] === 'file')
                                        <input type="file" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-indigo-50 file:text-indigo-700
                                                      hover:file:bg-indigo-100
                                                      border border-gray-300 rounded-md cursor-pointer"
                                               {{ $isRequired ? 'required' : '' }}>
                                        <p class="text-xs text-gray-500 mt-1">Format: PDF/JPG/PNG (Max 2MB)</p>

                                    @else
                                        <input type="text" 
                                               name="{{ $key }}" 
                                               id="{{ $key }}" 
                                               value="{{ old($key) }}"
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"
                                               placeholder="Isi jawaban Anda..."
                                               {{ $isRequired ? 'required' : '' }}>
                                    @endif

                                    @error($key)
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <p>Admin belum mengatur formulir untuk jenis surat ini.</p>
                            <p class="text-sm mt-1">Silakan hubungi admin atau pilih jenis surat lain.</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between mt-8 pt-6 border-t">
                        <a href="{{ route('user.letters.create') }}" class="text-gray-600 hover:text-gray-900 font-semibold underline decoration-2 underline-offset-4 text-sm">
                            &larr; Pilih Jenis Lain
                        </a>
                        
                        @if(!empty($formConfig))
                            <x-primary-button class="ml-3 px-6 py-2 text-base">
                                {{ __('Kirim Pengajuan') }}
                            </x-primary-button>
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout> 