<x-layouts.app :title="'Edit Kategori'">

    <div class="flex items-center gap-3 px-4 pt-5 pb-4">
        <a href="{{ route('categories.index') }}"
           class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Edit Kategori</h1>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}" class="px-4 space-y-4">
        @csrf @method('PATCH')

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Nama Kategori</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}"
                   placeholder="Contoh: Makan, Gaji, Transport"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600">
            @error('name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Jenis</label>
            <div class="grid grid-cols-2 gap-2">
                @php $currentType = old('type', $category->type); @endphp
                <label class="flex items-center gap-3 bg-zinc-800 border rounded-xl px-4 py-3 cursor-pointer transition-colors
                              {{ $currentType === 'income' ? 'border-emerald-500 bg-emerald-900/30' : 'border-zinc-700' }}">
                    <input type="radio" name="type" value="income" class="hidden" {{ $currentType === 'income' ? 'checked' : '' }}>
                    <span class="w-3 h-3 rounded-full bg-emerald-400 shrink-0"></span>
                    <span class="text-sm font-medium">Pemasukan</span>
                </label>
                <label class="flex items-center gap-3 bg-zinc-800 border rounded-xl px-4 py-3 cursor-pointer transition-colors
                              {{ $currentType === 'expense' ? 'border-rose-500 bg-rose-900/30' : 'border-zinc-700' }}">
                    <input type="radio" name="type" value="expense" class="hidden" {{ $currentType === 'expense' ? 'checked' : '' }}>
                    <span class="w-3 h-3 rounded-full bg-rose-400 shrink-0"></span>
                    <span class="text-sm font-medium">Pengeluaran</span>
                </label>
            </div>
            @error('type')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-base rounded-xl py-3.5 transition-colors min-h-[44px] mt-2">
            Perbarui Kategori
        </button>
    </form>

    <script>
        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('label:has(input[name="type"])').forEach(label => {
                    const input = label.querySelector('input[name="type"]');
                    label.classList.remove('border-emerald-500', 'bg-emerald-900/30', 'border-rose-500', 'bg-rose-900/30');
                    label.classList.add('border-zinc-700');
                    if (input.checked) {
                        const color = input.value === 'income' ? 'emerald' : 'rose';
                        label.classList.remove('border-zinc-700');
                        label.classList.add(`border-${color}-500`, `bg-${color}-900/30`);
                    }
                });
            });
        });
    </script>

</x-layouts.app>
