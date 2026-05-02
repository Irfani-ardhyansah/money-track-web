<x-layouts.app :title="'Edit Transaksi'">

    <div class="flex items-center justify-between px-4 pt-5 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('transactions.show', $transaction) }}"
               class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold">Edit Transaksi</h1>
        </div>
        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
              onsubmit="return confirm('Hapus transaksi ini?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-rose-900/60 flex items-center justify-center text-zinc-400 hover:text-rose-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('transactions.update', $transaction) }}" id="txForm" class="px-4 space-y-4">
        @csrf @method('PATCH')

        {{-- Type selector --}}
        <div>
            <label class="block text-sm text-zinc-400 mb-2">Jenis</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['expense' => ['Pengeluaran', 'rose'], 'income' => ['Pemasukan', 'emerald'], 'transfer' => ['Transfer', 'blue']] as $val => [$label, $color])
                    @php $selected = old('type', $transaction->type) === $val; @endphp
                    <label class="type-label flex flex-col items-center gap-1.5 bg-zinc-800 border rounded-xl px-2 py-3 cursor-pointer transition-colors
                                  {{ $selected ? "border-{$color}-500 bg-{$color}-900/30" : 'border-zinc-700' }}"
                           data-color="{{ $color }}">
                        <input type="radio" name="type" value="{{ $val }}" class="hidden" {{ $selected ? 'checked' : '' }}>
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $color }}-400"></span>
                        <span class="text-xs font-medium">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('type')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Jumlah (Rp)</label>
            <input type="number" name="amount" value="{{ old('amount', $transaction->amount) }}"
                   min="1" step="any"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
            @error('amount')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Tanggal</label>
            <input type="date" name="occurred_at" value="{{ old('occurred_at', $transaction->occurred_at->format('Y-m-d')) }}"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
            @error('occurred_at')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Dompet</label>
            <select name="wallet_id"
                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                @foreach($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected(old('wallet_id', $transaction->wallet_id) == $wallet->id)>{{ $wallet->name }}</option>
                @endforeach
            </select>
            @error('wallet_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="toWalletField" class="{{ old('type', $transaction->type) !== 'transfer' ? 'hidden' : '' }}">
            <label class="block text-sm text-zinc-400 mb-1.5">Ke Dompet</label>
            <select name="to_wallet_id"
                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">— Pilih dompet tujuan —</option>
                @foreach($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected(old('to_wallet_id', $transaction->to_wallet_id) == $wallet->id)>{{ $wallet->name }}</option>
                @endforeach
            </select>
            @error('to_wallet_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="categoryField" class="{{ old('type', $transaction->type) === 'transfer' ? 'hidden' : '' }}">
            <label class="block text-sm text-zinc-400 mb-1.5">Kategori</label>
            <select name="category_id"
                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                <option value="">— Pilih kategori —</option>
                @foreach($categories->groupBy('type') as $type => $group)
                    <optgroup label="{{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
                        @foreach($group as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $transaction->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('category_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Catatan (opsional)</label>
            <textarea name="notes" rows="2"
                      class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none resize-none">{{ old('notes', $transaction->notes) }}</textarea>
            @error('notes')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-base rounded-xl py-3.5 transition-colors min-h-[44px] mt-2">
            Perbarui Transaksi
        </button>
    </form>

    <script>
        const labels = document.querySelectorAll('.type-label');
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
