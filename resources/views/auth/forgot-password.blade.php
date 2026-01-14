<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Sistem Informasi Persuratan</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
        }
        .bg-pattern {
            background-image: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 20%),
                              radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 20%);
        }
    </style>
</head>
<body class="bg-[#000275] bg-pattern min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-6xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">

            <div class="hidden lg:flex flex-col justify-center text-white px-8">
                <div class="w-20 h-2 bg-yellow-400 rounded-full mb-8"></div>
                
                <h1 class="text-5xl font-extrabold leading-tight tracking-tight mb-6 drop-shadow-lg">
                    SISTEM INFORMASI<br>
                    <span class="text-blue-300">PERSURATAN</span>
                </h1>

                <p class="text-lg text-white leading-relaxed max-w-lg mb-10 font-medium">
                    Layanan administrasi persuratan STIKES Bhayangkara Makassar yang aman, tertib, digital, dan terintegrasi penuh.
                </p>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3 bg-white/10 p-3 rounded-lg backdrop-blur-sm border hover:border-blue-300/70 hover:bg-blue-300/30 transition-all duration-500 border-white/10 w-fit">
                            <div class="bg-yellow-400 p-1.5 rounded-full text-[#000275]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold">Pemulihan Password Aman dan Terintegrasi</span>
                        </div>
                    </div>
            </div>

            <div class="w-full flex justify-center lg:justify-end">
                <div class="w-full max-w-[450px] bg-white rounded-3xl shadow-2xl overflow-hidden relative">

                    <div class="p-8 md:p-10">
                        <div class="text-center mb-8 flex justify-center items-center flex-col">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo STIKES" class="h-[120px] mb-6 drop-shadow-md hover:scale-105 transition-transform duration-300">
                            
                            <h2 class="text-2xl font-black text-[#000275] uppercase tracking-wide">Lupa Password?</h2>
                        </div>

                        <x-auth-session-status class="mb-5" :status="session('status')" />

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                            @csrf

                            <div class="space-y-1">
                                <label for="email" class="sr-only">Email</label>
                                <div class="flex gap-3 group">
                                    <div class="flex-none w-12 flex items-center justify-center text-[#FF0000] bg-gray-50 border-2 border-[#000275] rounded-xl transition-colors group-focus-within:bg-blue-50">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                            <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                                        </svg>
                                    </div>
                                    <input 
                                        id="email"    
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email') }}" 
                                        required 
                                        class="flex-1 w-full px-4 py-3 bg-gray-50 border-2 border-[#000275] rounded-xl text-black text-sm placeholder-gray-400 transition-all duration-200 focus:bg-blue-100/50 focus:outline-none focus:ring-0 focus:border-[#000275] focus-visible:outline-none focus-visible:ring-0  @error('email') border-red-500 bg-red-50 text-red-900 @enderror"
                                        placeholder="Masukkan Email Terdaftar"
                                    >
                                </div>
                                @error('email')
                                    <p class="text-xs text-red-600 font-medium ml-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-[#050C7A] hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#050C7A] transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95 tracking-wide">
                                KIRIM 
                            </button>

                            <div class="text-center pt-2">
                                <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-[#000275] transition-colors gap-2 group">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali ke Login
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="bg-white px-8 py-4 text-center ">
                        <p class="text-xs text-gray-400 font-medium">&copy; {{ date('Y') }} STIKES Bhayangkara Makassar</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>