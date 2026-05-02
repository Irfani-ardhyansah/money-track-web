<x-layouts.app :title="'Tambah Penyesuaian Tabungan'">

    <div class="flex items-center gap-3 px-4 pt-5 pb-3">
        <a href="{{ route('savings.index') }}"
           class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Penyesuaian Tabungan</h1>
    </div>

    <form method="POST" action="{{ route('savings.store') }}" class="px-4 space-y-5">
        @csrf

        {{-- Wallet selector (savings & investment only) --}}
        @php
        $initWalletId    = old('wallet_id');
        $initWalletObj   = $initWalletId ? \App\Models\Wallet::with('parent')->find($initWalletId) : null;
        $initWalletLabel = $initWalletObj
            ? ($initWalletObj->parent ? $initWalletObj->parent->name . ' › ' . $initWalletObj->name : $initWalletObj->name)
            : '';
        $initWalletBal   = $initWalletObj ? (float) $balances->get($initWalletObj->id, 0) : null;
        @endphp
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Dompet Tabungan</label>
            <x-wallet-search
                name="wallet_id"
                :initialId="$initWalletId"
                :initialLabel="$initWalletLabel"
                :initialBalance="$initWalletBal"
                filterType="savings,investment"
                placeholder="Cari dompet tabungan..."
            />
            @error('wallet_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Direction toggle --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Jenis</label>
            <div class="flex rounded-xl overflow-hidden border border-zinc-700">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="direction" value="add" class="sr-only peer"
                           @checked(old('direction', 'add') === 'add')>
                    <span class="block text-center text-sm font-medium py-3 transition-colors
                                 peer-checked:bg-emerald-600 peer-checked:text-white
                                 bg-zinc-800 text-zinc-400">
                        + Tambah
                    </span>
                </label>
                <label class="flex-1 cursor-pointer border-l border-zinc-700">
                    <input type="radio" name="direction" value="subtract" class="sr-only peer"
                           @checked(old('direction') === 'subtract')>
                    <span class="block text-center text-sm font-medium py-3 transition-colors
                                 peer-checked:bg-rose-600 peer-checked:text-white
                                 bg-zinc-800 text-zinc-400">
                        − Kurangi
                    </span>
                </label>
            </div>
            @error('direction')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Jumlah</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-base font-medium pointer-events-none">Rp</span>
                <input type="text" id="amount_display"
                       inputmode="numeric"
                       placeholder="0"
                       autocomplete="off"
                       class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600">
                <input type="hidden" name="amount" id="amount_hidden" value="{{ old('amount') }}">
            </div>
            @error('amount')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Date --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Tanggal</label>
            <input type="date" name="occurred_at" value="{{ old('occurred_at', now()->toDateString()) }}"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
            @error('occurred_at')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Catatan <span class="text-zinc-600">(opsional)</span></label>
            <textarea name="notes" rows="2" placeholder="mis. Saldo awal sebelum pakai app, Deposito di bank lain…"
                      class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600 resize-none">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-base rounded-xl py-3.5 transition-colors min-h-[44px]">
            Simpan
        </button>
    </form>

    <script>
    (function () {
        const display = document.getElementById('amount_display');
        const hidden  = document.getElementById('amount_hidden');

        function toRupiah(raw) {
            const num = parseInt(raw.replace(/\D/g, ''), 10);
            return isNaN(num) ? '' : num.toLocaleString('id-ID');
        }

        display.addEventListener('input', function () {
            const raw    = this.value.replace(/\D/g, '');
            const prev   = this.value.length;
            const cursor = this.selectionStart;
            this.value   = raw ? toRupiah(raw) : '';
            hidden.value = raw;
            const diff = this.value.length - prev;
            this.setSelectionRange(cursor + diff, cursor + diff);
        });

        if (hidden.value) display.value = toRupiah(hidden.value);
    })();
    </script>

</x-layouts.app>
