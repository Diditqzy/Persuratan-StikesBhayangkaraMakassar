<nav x-data="{ open: false }" class="bg-[#000275] border-b py-1 border-blue-900 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('user.letters.index') }}">
                        <img src="{{ asset('images/logo-dashboard.png') }}" alt="Logo" class="sm:h-[80px] h-[72px] w-auto  ">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex h-full items-center">
                    <a :href="route('user.letters.index')" :active="request()->routeIs('user.letters.*')" 
                        class="text-yellow-400 active:text-yellow-400 focus:text-yellow-400 border-transparent hover:border-yellow-400 focus:border-yellow-400 transition duration-150 ease-in-out h-full inline-flex items-center  pt-1 border-b-2 text-sm font-medium leading-5">
                        {{ __('Surat Saya') }}
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-6">
                
                <div class="flex items-center gap-3 text-white">
                    <div class="text-right">
                        <div class="text-sm font-bold leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-yellow-400 tracking-wider font-medium uppercase">{{ Auth::user()->identity_number }}</div> </div>
                    <div class="bg-blue-800 p-2 rounded-full border border-blue-700">
                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                </div>

                <div class="h-8 w-px bg-blue-800"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="group flex items-center gap-2 p-2 rounded-lg text-white bg-red-600/80 transition-all duration-200 focus:outline-none"
                            title="Keluar / Logout">
                        
                        <span class="text-sm font-medium hidden lg:block group-hover:text-white">Keluar</span>
                        
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white bg-blue-900 focus:outline-none focus:bg-blue-900 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-blue-900 border-t border-blue-800">


        <div class="pt-4 pb-1 ">
            <div class="pt-2 pb-3 space-y-1 px-4 ">
                <div class="flex items-center gap-3 text-blue-200">
                    <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2" ry="2"/>
                        <polyline points="3 7 12 13 21 7"/>
                    </svg>

                    <a :href="route('user.letters.index')" :active="request()->routeIs('user.letters.*')" class="text-blue-200 font-medium">
                        {{ __('Surat Saya') }}
                    </a>
                </div>
            </div>
            <div class="px-4 flex items-center gap-3 my-4">
                <div class="bg-blue-700 p-2 rounded-full text-white">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <div>
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-yellow-400">{{ Auth::user()->identity_number }}</div>
                </div>
            </div>

            <div class="space-y-1 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="w-fill flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium text-white  bg-red-600 transition-colors">
                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>

                        {{ __('Keluar') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>