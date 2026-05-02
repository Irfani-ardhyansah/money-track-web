<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#09090b">
    <title>{{ $title ?? 'Money Tracker' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen antialiased">

    {{-- Page content --}}
    <div class="max-w-lg mx-auto pb-24">
        @if(session('success'))
            <div class="mx-4 mt-4 px-4 py-3 rounded-xl bg-emerald-900/60 border border-emerald-700 text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 mt-4 px-4 py-3 rounded-xl bg-rose-900/60 border border-rose-700 text-rose-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </div>

    {{-- Bottom navigation --}}
    <nav class="fixed bottom-0 inset-x-0 z-50 bg-zinc-900 border-t border-zinc-800 pb-safe">
        <div class="max-w-lg mx-auto flex">
            <a href="{{ route('dashboard') }}"
               class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors
                      {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 12 9-9 9 9M4.5 10.5V20a.75.75 0 0 0 .75.75h5.25v-4.5h4.5v4.5H20.25A.75.75 0 0 0 21 20v-9.5"/>
                </svg>
                Beranda
            </a>
            <a href="{{ route('transactions.index') }}"
               class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors
                      {{ request()->routeIs('transactions.*') ? 'text-emerald-400' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18M15.75 17.25 12 21m0 0-3.75-3.75"/>
                </svg>
                Transaksi
            </a>
            <a href="{{ route('savings.index') }}"
               class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors
                      {{ request()->routeIs('savings.*') || request()->routeIs('activity-logs.*') ? 'text-emerald-400' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Tabungan
            </a>
            <a href="{{ route('wallets.index') }}"
               class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors
                      {{ request()->routeIs('wallets.*') ? 'text-emerald-400' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 12v6a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-6ZM3 12V8.25A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25V12"/>
                </svg>
                Dompet
            </a>
            <a href="{{ route('categories.index') }}"
               class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors
                      {{ request()->routeIs('categories.*') ? 'text-emerald-400' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                </svg>
                Kategori
            </a>
        </div>
    </nav>

</body>
</html>
