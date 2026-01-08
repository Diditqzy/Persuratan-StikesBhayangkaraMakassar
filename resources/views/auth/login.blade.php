<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | Sistem Informasi Persuratan</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
        body {
                font-family: system-ui, 
                -apple-system, 
                BlinkMacSystemFont,
                "Segoe UI", 
                Arial, 
                sans-serif;
            }
        </style>
    </head>
    <body class="bg-[#000275] min-h-screen flex items-center justify-center p-4">
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
                        <div class="flex items-center gap-3 bg-white/10 p-3 rounded-lg backdrop-blur-sm border border-white/10 w-fit">
                            <div class="bg-yellow-400 p-1.5 rounded-full text-[#000275]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold">Mendukung Tata Kelola Administrasi Kampus</span>
                        </div>
                    </div>
                </div>
                <div class="w-full flex justify-center lg:justify-end">
                    <div class="w-full max-w-[450px] bg-white rounded-3xl shadow-2xl overflow-hidden relative ">
                        <div class="p-8 md:p-10">
                            <div class="text-center mb-8 flex justify-center items-center flex-col">
                                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-[160px]  mb-4 drop-shadow-md hover:scale-105 transition-transform duration-300">
                                <h2 class="text-2xl font-black text-[#000275]">SELAMAT DATANG</h2>
                                <p class="text-[#FF0000] text-base mt-1 font-semibold">Silakan Masuk ke Akun Anda</p>
                            </div>
                            @if (session('status'))
                                <div class="mb-5 p-4 rounded-lg bg-green-50 text-green-700 text-sm font-medium border border-green-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                                @csrf
                                <div class="space-y-1">
                                    <div class="flex gap-3 group">
                                        
                                        <div class="flex-none w-12 flex items-center justify-center text-[#FF0000] bg-gray-50  border-2 border-[#000275] rounded-xl">
                                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"/>
                                                <path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6"/>
                                            </svg>
                                        </div>
                                        <input 
                                            id="identity_number"    
                                            type="text" 
                                            name="identity_number" 
                                            value="{{ old('identity_number') }}" 
                                            required 
                                            class="flex-1 w-full px-4 py-3 bg-gray-50 border-2 border-[#000275] focus:bg-blue-100/55 rounded-xl text-black text-base placeholder-gray-400 transition-all duration-200 focus:outline-none focus:ring-0 focus:border-[#000275] focus-visible:outline-none focus-visible:ring-0 @error('identity_number') border-red-500 bg-red-50 text-red-900 @enderror"
                                            placeholder="Username"
                                        >
                                    </div>
                                    @error('identity_number')
                                        <p class="text-xs text-red-600 font-medium ml-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div class="space-y-1">
                                    <div class="flex gap-3 group">                                  
                                        <div class="flex-none w-12 flex items-center justify-center text-[#FF0000] bg-gray-50 border-2 border-[#000275]  rounded-xl">
                                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input 
                                            id="password" 
                                            type="password" 
                                            name="password" 
                                            required 
                                            class="flex-1 w-full px-4 py-3 bg-gray-50 border-2 border-[#000275] focus:bg-blue-100/55 rounded-xl text-black text-base placeholder-gray-400 transition-all duration-200 focus:outline-none focus:ring-0 focus:border-[#000275] focus-visible:outline-none focus-visible:ring-0 @error('password') border-red-500 bg-red-50 text-red-900 @enderror"
                                            placeholder="Password"
                                        >
                                    </div>
                                    @error('password')
                                        <p class="text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-between">
                                    <label class="flex items-center cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" name="remember" class="peer h-5 w-5 text-[#050C7A] border-2 border-[#000275] rounded-md cursor-pointer">
                                        </div>
                                        <span class=" ml-2 text-base text-[#000275] font-medium transition-colors">Ingat Saya</span>
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-base font-semibold text-[#FF0000] hover:text-[#FF0000] transition-colors hover:underline decoration-2 underline-offset-4">
                                            Lupa Password?
                                        </a>
                                    @endif
                                </div>

                                <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-[#050C7A] hover:bg-blue-900 focus:outline-none  transition-all duration-200 transform hover:-translate-y-0.5">
                                    Masuk
                                </button>
                            </form>
                        </div>
                        <div class="bg-white px-8 py-4 text-center">
                            <p class="text-xs text-gray-400 font-medium">&copy; {{ date('Y') }} STIKES Bhayangkara Makassar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>