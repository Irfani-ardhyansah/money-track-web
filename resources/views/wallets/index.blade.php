<x-layouts.app :title="'Dompet'">

    <div class="flex items-center justify-between px-4 pt-5 pb-3">
        <h1 class="text-lg font-semibold">Dompet</h1>
        <a href="{{ route('wallets.create') }}"
           class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors min-h-[44px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah
        </a>
    </div>

    {{-- ── Filter dompet utama ───────────────────────────────── --}}
    @if($parentWallets->count() > 1)
    <div class="px-4 mb-3">
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-none snap-x snap-proximity">
            <a href="{{ route('wallets.index') }}"
               class="snap-start shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all active:scale-[0.98]
                      {{ is_null($ownerId) ? 'bg-zinc-600 text-white ring-2 ring-zinc-500/40' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200 border border-zinc-700/80' }}">
                Semua
            </a>
            @foreach($parentWallets as $pw)
                <a href="{{ route('wallets.index', ['owner_id' => $pw->id]) }}"
                   class="snap-start shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all active:scale-[0.98]
                          {{ $ownerId === $pw->id ? 'bg-indigo-600 text-white ring-2 ring-indigo-400/30' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200 border border-zinc-700/80' }}">
                    {{ $pw->name }}
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="px-4 space-y-2">
        @forelse($walletTree as $wallet)
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm">{{ $wallet->name }}</p>
                        <p class="text-xs text-zinc-500 capitalize">{{ $wallet->type }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('wallets.edit', $wallet) }}"
                               class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-zinc-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                                  onsubmit="return confirm('Hapus dompet ini?')">
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
                </div>

                @if($wallet->children->isNotEmpty())
                    <div class="border-t border-zinc-800 divide-y divide-zinc-800">
                        @foreach($wallet->children as $child)
                            <div class="px-4 py-2.5 flex items-center justify-between pl-8">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-zinc-300">{{ $child->name }}</p>
                                    <p class="text-xs text-zinc-500 capitalize">{{ $child->type }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('wallets.edit', $child) }}"
                                           class="w-7 h-7 rounded-lg bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-zinc-200 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('wallets.destroy', $child) }}"
                                              onsubmit="return confirm('Hapus dompet ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-7 h-7 rounded-lg bg-zinc-800 hover:bg-rose-900/60 flex items-center justify-center text-zinc-400 hover:text-rose-400 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-10 text-center">
                <p class="text-zinc-500 text-sm">Belum ada dompet.</p>
                <a href="{{ route('wallets.create') }}" class="text-emerald-400 text-sm mt-1 inline-block">+ Buat dompet pertama</a>
            </div>
        @endforelse
    </div>

</x-layouts.app>
