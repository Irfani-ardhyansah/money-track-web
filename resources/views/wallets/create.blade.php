<x-layouts.app :title="'Tambah Dompet'">

    <div class="flex items-center gap-3 px-4 pt-5 pb-4">
        <a href="{{ route('wallets.index') }}"
           class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Tambah Dompet</h1>
    </div>

    <form method="POST" action="{{ route('wallets.store') }}" class="px-4 space-y-4">
        @csrf

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Nama Dompet</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   placeholder="Contoh: BCA, Dompet Cash"
                   class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none placeholder:text-zinc-600">
            @error('name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Jenis</label>
            <select name="type"
                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                @foreach(['cash' => 'Cash / Tunai', 'bank' => 'Rekening Bank', 'e-wallet' => 'Dompet Digital', 'savings' => 'Tabungan', 'investment' => 'Investasi', 'other' => 'Lainnya'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('type') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1.5">Sub-kantong dari (opsional)</label>
            <select name="parent_id"
                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                <option value="">— Tidak ada (dompet utama) —</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-base rounded-xl py-3.5 transition-colors min-h-[44px] mt-2">
            Simpan Dompet
        </button>
    </form>

</x-layouts.app>
