<x-layouts.app :title="'Transaksi'">

    <div class="flex items-center justify-between px-4 pt-5 pb-3">
        <h1 class="text-lg font-semibold">Transaksi</h1>
        <a href="{{ route('transactions.create') }}"
           class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors min-h-[44px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah
        </a>
    </div>

    {{-- Filter panel --}}
    <form method="GET" action="{{ route('transactions.index') }}" class="px-4 mb-3 space-y-2">
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
            @foreach(['' => 'Semua', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran', 'transfer' => 'Transfer'] as $val => $label)
                <button type="submit" name="type" value="{{ $val }}"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                               {{ request('type', '') === $val
                                  ? ($val === 'expense' ? 'bg-rose-600 text-white' : ($val === 'income' ? 'bg-emerald-600 text-white' : 'bg-zinc-600 text-white'))
                                  : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="flex gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="flex-1 bg-zinc-800 border border-zinc-700 text-zinc-300 text-sm rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
            <span class="text-zinc-600 flex items-center text-sm">—</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="flex-1 bg-zinc-800 border border-zinc-700 text-zinc-300 text-sm rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
            <button type="submit"
                    class="bg-zinc-700 hover:bg-zinc-600 text-zinc-300 text-sm px-3 py-2 rounded-xl transition-colors">
                Filter
            </button>
        </div>
        @if(request()->hasAny(['type', 'date_from', 'date_to', 'category_id']))
            <a href="{{ route('transactions.index') }}" class="text-xs text-zinc-500 hover:text-zinc-300">✕ Reset filter</a>
        @endif
    </form>

    {{-- Transaction list --}}
    <div class="px-4 space-y-2">
        @forelse($transactions as $tx)
            <a href="{{ route('transactions.show', $tx) }}"
               class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 flex items-center gap-3 active:bg-zinc-800 transition-colors block">
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
                        {{ $tx->type === 'income' ? '+' : ($tx->type === 'expense' ? '-' : '↔') }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-zinc-500">{{ $tx->occurred_at->format('d M Y') }}</p>
                </div>
            </a>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-10 text-center">
                <p class="text-zinc-500 text-sm">Tidak ada transaksi ditemukan.</p>
                <a href="{{ route('transactions.create') }}" class="text-emerald-400 text-sm mt-1 inline-block">+ Catat transaksi</a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
        <div class="px-4 mt-4">
            {{ $transactions->links('vendor.pagination.tailwind') }}
        </div>
    @endif

</x-layouts.app>
