<x-layouts.app :title="'Kategori'">

    <div class="flex items-center justify-between px-4 pt-5 pb-3">
        <h1 class="text-lg font-semibold">Kategori</h1>
        <a href="{{ route('categories.create') }}"
           class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors min-h-[44px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah
        </a>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 px-4 mb-3">
        <a href="{{ route('categories.index') }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ !$type ? 'bg-emerald-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200' }}">
            Semua
        </a>
        <a href="{{ route('categories.index', ['type' => 'income']) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $type === 'income' ? 'bg-emerald-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200' }}">
            Pemasukan
        </a>
        <a href="{{ route('categories.index', ['type' => 'expense']) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $type === 'expense' ? 'bg-rose-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200' }}">
            Pengeluaran
        </a>
    </div>

    <div class="px-4 space-y-2">
        @forelse($categories as $category)
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full shrink-0
                        {{ $category->type === 'income' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                    <div>
                        <p class="text-sm font-medium">{{ $category->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('categories.edit', $category) }}"
                       class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-zinc-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                          onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-rose-900/60 flex items-center justify-center text-zinc-400 hover:text-rose-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-10 text-center">
                <p class="text-zinc-500 text-sm">Belum ada kategori.</p>
                <a href="{{ route('categories.create') }}" class="text-emerald-400 text-sm mt-1 inline-block">+ Buat kategori pertama</a>
            </div>
        @endforelse
    </div>

</x-layouts.app>
