<x-layouts.app :title="'Detail Transaksi'">

    <div class="flex items-center justify-between px-4 pt-5 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('transactions.index') }}"
               class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold">Detail Transaksi</h1>
        </div>
        <a href="{{ route('transactions.edit', $transaction) }}"
           class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-zinc-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
            </svg>
        </a>
    </div>

    <div class="px-4 space-y-3">
        {{-- Amount hero --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 text-center">
            <div class="inline-flex w-14 h-14 rounded-full items-center justify-center mb-3
                {{ $transaction->type === 'income' ? 'bg-emerald-900/60 text-emerald-400' : ($transaction->type === 'expense' ? 'bg-rose-900/60 text-rose-400' : 'bg-blue-900/60 text-blue-400') }}">
                @if($transaction->type === 'income')
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                @elseif($transaction->type === 'expense')
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                @endif
            </div>
            <p class="text-3xl font-bold {{ $transaction->type === 'income' ? 'text-emerald-400' : ($transaction->type === 'expense' ? 'text-rose-400' : 'text-blue-400') }}">
                {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '↔') }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
            </p>
            <p class="text-sm text-zinc-500 mt-1">
                {{ $transaction->type === 'income' ? 'Pemasukan' : ($transaction->type === 'expense' ? 'Pengeluaran' : 'Transfer') }}
            </p>
        </div>

        {{-- Details --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl divide-y divide-zinc-800">
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-zinc-500">Tanggal</span>
                <span class="text-sm font-medium">{{ $transaction->occurred_at->translatedFormat('d F Y') }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-zinc-500">Dompet</span>
                <span class="text-sm font-medium">{{ $transaction->wallet->name }}</span>
            </div>
            @if($transaction->type === 'transfer')
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-zinc-500">Ke Dompet</span>
                <span class="text-sm font-medium">{{ $transaction->toWallet?->name ?? '-' }}</span>
            </div>
            @endif
            @if($transaction->category)
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-zinc-500">Kategori</span>
                <span class="text-sm font-medium">{{ $transaction->category->name }}</span>
            </div>
            @endif
            @if($transaction->notes)
            <div class="px-4 py-3">
                <p class="text-sm text-zinc-500 mb-1">Catatan</p>
                <p class="text-sm">{{ $transaction->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Delete --}}
        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
              onsubmit="return confirm('Hapus transaksi ini?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="w-full border border-rose-800 text-rose-400 hover:bg-rose-900/30 font-medium text-sm rounded-xl py-3 transition-colors min-h-[44px]">
                Hapus Transaksi
            </button>
        </form>
    </div>

</x-layouts.app>
