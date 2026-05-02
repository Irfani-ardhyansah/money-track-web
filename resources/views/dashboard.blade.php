<x-layouts.app :title="'Beranda'">

    {{-- Month/year selector header --}}
    <div class="flex items-center justify-between px-4 pt-5 pb-2">
        <h1 class="text-lg font-semibold">Ringkasan</h1>
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
            <select name="month"
                    onchange="this.form.submit()"
                    class="bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" @selected($m === $month)>
                        {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                    </option>
                @endforeach
            </select>
            <select name="year"
                    onchange="this.form.submit()"
                    class="bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach(range(now()->year - 3, now()->year + 1) as $y)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-2 px-4 mb-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Pemasukan</span>
            <span class="text-sm font-bold text-emerald-400">{{ 'Rp '.number_format($summary['income'], 0, ',', '.') }}</span>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Pengeluaran</span>
            <span class="text-sm font-bold text-rose-400">{{ 'Rp '.number_format($summary['expense'], 0, ',', '.') }}</span>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Selisih</span>
            <span class="text-sm font-bold {{ $summary['net'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ ($summary['net'] < 0 ? '-' : '') . 'Rp '.number_format(abs($summary['net']), 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Wallet balances --}}
    <div class="px-4 mb-2">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-zinc-400">Saldo Dompet</h2>
            <a href="{{ route('wallets.index') }}" class="text-xs text-emerald-400">Lihat semua</a>
        </div>
        <div class="space-y-2">
            @forelse($walletTree as $wallet)
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ $wallet->name }}</p>
                            <p class="text-xs text-zinc-500 capitalize">{{ $wallet->type }}</p>
                        </div>
                        <span class="text-sm font-semibold {{ $wallet->balance >= 0 ? 'text-zinc-100' : 'text-rose-400' }}">
                            {{ ($wallet->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($wallet->balance), 0, ',', '.') }}
                        </span>
                    </div>
                    @if($wallet->children->isNotEmpty())
                        <div class="mt-2 space-y-1 pl-3 border-l border-zinc-700">
                            @foreach($wallet->children as $child)
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-zinc-400">{{ $child->name }}</p>
                                    <span class="text-xs {{ $child->balance >= 0 ? 'text-zinc-300' : 'text-rose-400' }}">
                                        {{ ($child->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($child->balance), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-6 text-center">
                    <p class="text-sm text-zinc-500">Belum ada dompet.</p>
                    <a href="{{ route('wallets.create') }}" class="text-sm text-emerald-400 mt-1 inline-block">+ Tambah dompet</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Category breakdown --}}
    @if($breakdown->isNotEmpty())
    <div class="px-4 mb-4">
        <h2 class="text-sm font-semibold text-zinc-400 mb-2">Pengeluaran per Kategori</h2>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 space-y-3">
            @foreach($breakdown as $item)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-zinc-300">{{ $item->category_name }}</span>
                        <span class="text-xs text-zinc-400">{{ $item->percentage }}%
                            <span class="text-zinc-500 ml-1">{{ 'Rp '.number_format($item->amount, 0, ',', '.') }}</span>
                        </span>
                    </div>
                    <div class="h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-rose-500 rounded-full" style="width: {{ $item->percentage }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent transactions --}}
    <div class="px-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-zinc-400">Transaksi Terakhir</h2>
            <a href="{{ route('transactions.index') }}" class="text-xs text-emerald-400">Lihat semua</a>
        </div>
        <div class="space-y-2">
            @forelse($recent as $tx)
                <a href="{{ route('transactions.show', $tx) }}"
                   class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 flex items-center gap-3 active:bg-zinc-800 transition-colors block">
                    {{-- Type icon --}}
                    <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                        {{ $tx->type === 'income' ? 'bg-emerald-900/60 text-emerald-400' : ($tx->type === 'expense' ? 'bg-rose-900/60 text-rose-400' : 'bg-blue-900/60 text-blue-400') }}">
                        @if($tx->type === 'income')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        @elseif($tx->type === 'expense')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">
                            {{ $tx->category?->name ?? ($tx->type === 'transfer' ? 'Transfer' : '-') }}
                        </p>
                        <p class="text-xs text-zinc-500 truncate">
                            {{ $tx->wallet->name }}
                            @if($tx->type === 'transfer') → {{ $tx->toWallet?->name }} @endif
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-semibold {{ $tx->type === 'income' ? 'text-emerald-400' : ($tx->type === 'expense' ? 'text-rose-400' : 'text-blue-400') }}">
                            {{ $tx->type === 'expense' ? '-' : '+' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-zinc-500">{{ $tx->occurred_at->format('d M') }}</p>
                    </div>
                </a>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-6 text-center">
                    <p class="text-sm text-zinc-500">Belum ada transaksi.</p>
                    <a href="{{ route('transactions.create') }}" class="text-sm text-emerald-400 mt-1 inline-block">+ Catat transaksi</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- FAB --}}
    <a href="{{ route('transactions.create') }}"
       class="fixed bottom-20 right-4 z-40 w-14 h-14 rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-900/50 flex items-center justify-center hover:bg-emerald-400 active:bg-emerald-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
    </a>

</x-layouts.app>
