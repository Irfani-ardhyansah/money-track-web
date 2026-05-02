<x-layouts.app :title="'Tambah Transaksi'">

    <div class="flex items-center gap-3 px-4 pt-5 pb-4">
        <a href="{{ route('transactions.index') }}"
           class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Catat Transaksi</h1>
    </div>

    @php
    $initWalletId     = old('wallet_id');
    $initWalletObj    = $initWalletId ? $wallets->firstWhere('id', $initWalletId) : null;
    $initWalletLabel  = $initWalletObj
        ? ($initWalletObj->parent ? $initWalletObj->parent->name . ' › ' . $initWalletObj->name : $initWalletObj->name)
        : '';
    $initWalletBal    = $initWalletObj ? (float) $balances->get($initWalletObj->id, 0) : null;

    $initToWalletId    = old('to_wallet_id');
    $initToWalletObj   = $initToWalletId ? $wallets->firstWhere('id', $initToWalletId) : null;
    $initToWalletLabel = $initToWalletObj
        ? ($initToWalletObj->parent ? $initToWalletObj->parent->name . ' › ' . $initToWalletObj->name : $initToWalletObj->name)
        : '';
    $initToWalletBal   = $initToWalletObj ? (float) $balances->get($initToWalletObj->id, 0) : null;
    @endphp

    <form method="POST" action="{{ route('transactions.store') }}" id="txForm" class="px-4 space-y-4">
        @csrf

        {{-- 1. Wallet (top) --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5" id="walletLabel">Dompet</label>
            <x-wallet-search
                name="wallet_id"
                :initialId="$initWalletId"
                :initialLabel="$initWalletLabel"
                :initialBalance="$initWalletBal"
                placeholder="Cari dompet..."
            />
            @error('wallet_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 2. To wallet (transfer only) --}}
        <div id="toWalletField" class="{{ old('type', 'expense') !== 'transfer' ? 'hidden' : '' }}">
            <label class="block text-sm text-zinc-400 mb-1.5">Ke Dompet</label>
            <x-wallet-search
                name="to_wallet_id"
                :initialId="$initToWalletId"
                :initialLabel="$initToWalletLabel"
                :initialBalance="$initToWalletBal"
                color="blue"
                placeholder="Cari dompet tujuan..."
            />
            @error('to_wallet_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 3. Type --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-2">Jenis</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['expense' => ['Pengeluaran', 'rose'], 'income' => ['Pemasukan', 'emerald'], 'transfer' => ['Transfer', 'blue']] as $val => [$label, $color])
                    <label class="type-label flex flex-col items-center gap-1.5 bg-zinc-800 border rounded-xl px-2 py-3 cursor-pointer transition-colors
                                  {{ old('type', 'expense') === $val ? "border-{$color}-500 bg-{$color}-900/30" : 'border-zinc-700' }}"
                           data-color="{{ $color }}">
                        <input type="radio" name="type" value="{{ $val }}" class="hidden" {{ old('type', 'expense') === $val ? 'checked' : '' }}>
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $color }}-400"></span>
                        <span class="text-xs font-medium">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('type')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 4. Amount + quick-add chips --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Jumlah</label>
            <div class="relative mb-2">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-base font-medium pointer-events-none">Rp</span>
                <input type="text" id="amount_display"
                       inputmode="numeric"
                       placeholder="0"
                       autocomplete="off"
                       class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600">
                <input type="hidden" name="amount" id="amount_hidden" value="{{ old('amount') }}">
            </div>
            {{-- Quick-add chips --}}
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
                @foreach([10000 => '10rb', 50000 => '50rb', 100000 => '100rb', 200000 => '200rb', 500000 => '500rb', 1000000 => '1jt', 2000000 => '2jt', 5000000 => '5jt'] as $value => $label)
                    <button type="button"
                            data-quick="{{ $value }}"
                            class="quick-add shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 hover:border-emerald-600 hover:text-emerald-400 active:bg-emerald-900/30 transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @error('amount')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 5. Date --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Tanggal</label>
            <input type="date" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d')) }}"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
            @error('occurred_at')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 6. Category --}}
        @php
        $initCatId  = old('category_id');
        $initCatObj = $initCatId ? $categories->firstWhere('id', $initCatId) : null;
        @endphp
        <div id="categoryField" class="{{ old('type', 'expense') === 'transfer' ? 'hidden' : '' }}">
            <label class="block text-sm text-zinc-400 mb-1.5">Kategori</label>
            <x-category-search
                :initialId="$initCatId"
                :initialLabel="$initCatObj?->name ?? ''"
                placeholder="Cari kategori..."
            />
            @error('category_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 7. Notes --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Catatan (opsional)</label>
            <textarea name="notes" rows="2"
                      placeholder="Catatan tambahan..."
                      class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600 resize-none">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-base rounded-xl py-3.5 transition-colors min-h-[44px] mt-2">
            Simpan Transaksi
        </button>
    </form>

    <script>
        // ── Amount formatter ──────────────────────────────────────────
        const amountDisplay = document.getElementById('amount_display');
        const amountHidden  = document.getElementById('amount_hidden');

        function toRupiah(raw) {
            const num = parseInt(String(raw).replace(/\D/g, ''), 10);
            return isNaN(num) ? '' : num.toLocaleString('id-ID');
        }

        function setAmount(num) {
            amountHidden.value  = num;
            amountDisplay.value = num ? num.toLocaleString('id-ID') : '';
        }

        amountDisplay.addEventListener('input', function () {
            const raw      = this.value.replace(/\D/g, '');
            const prevLen  = this.value.length;
            const cursor   = this.selectionStart;
            this.value     = raw ? toRupiah(raw) : '';
            amountHidden.value = raw;
            const diff = this.value.length - prevLen;
            this.setSelectionRange(cursor + diff, cursor + diff);
        });

        if (amountHidden.value) {
            amountDisplay.value = toRupiah(amountHidden.value);
        }

        // ── Quick-add chips ───────────────────────────────────────────
        document.querySelectorAll('.quick-add').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const add = parseInt(this.dataset.quick, 10);
                const cur = parseInt(amountHidden.value || '0', 10);
                setAmount(cur + add);
            });
        });

        // ── Type selector ─────────────────────────────────────────────
        const labels        = document.querySelectorAll('.type-label');
        const toWalletField = document.getElementById('toWalletField');
        const categoryField = document.getElementById('categoryField');

        function updateTypeUI() {
            const selected = document.querySelector('input[name="type"]:checked');
            if (!selected) return;
            const val = selected.value;

            toWalletField.classList.toggle('hidden', val !== 'transfer');
            categoryField.classList.toggle('hidden', val === 'transfer');

            labels.forEach(label => {
                const input = label.querySelector('input[name="type"]');
                const color = label.dataset.color;
                label.classList.remove(
                    'border-emerald-500','bg-emerald-900/30',
                    'border-rose-500','bg-rose-900/30',
                    'border-blue-500','bg-blue-900/30',
                    'border-zinc-700'
                );
                if (input.checked) {
                    label.classList.add(`border-${color}-500`, `bg-${color}-900/30`);
                } else {
                    label.classList.add('border-zinc-700');
                }
            });
        }

        labels.forEach(label => {
            label.addEventListener('click', () => {
                label.querySelector('input[name="type"]').checked = true;
                updateTypeUI();
            });
        });
    </script>

</x-layouts.app>
