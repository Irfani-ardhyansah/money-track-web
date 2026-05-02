<x-layouts.app :title="'Tabungan'">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 pt-5 pb-3">
        <h1 class="text-lg font-semibold">Tabungan</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('activity-logs.index') }}"
               class="flex items-center gap-1.5 text-zinc-400 hover:text-zinc-200 text-sm px-3 py-2 rounded-xl bg-zinc-800 border border-zinc-700 transition-colors min-h-[44px]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                </svg>
                Log
            </a>
            <a href="{{ route('savings.create') }}"
               class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors min-h-[44px]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah
            </a>
        </div>
    </div>

    {{-- Grand total card --}}
    <div class="px-4 mb-4">
        <div class="bg-gradient-to-br from-emerald-900/50 to-zinc-900 border border-emerald-800/50 rounded-2xl px-5 py-5">
            <p class="text-xs text-emerald-400/80 mb-1">Total Tabungan</p>
            <p class="text-3xl font-bold {{ $grandTotal >= 0 ? 'text-emerald-300' : 'text-rose-400' }}">
                {{ ($grandTotal < 0 ? '-' : '') . 'Rp ' . number_format(abs($grandTotal), 0, ',', '.') }}
            </p>
            <div class="mt-3 flex gap-4 text-xs text-zinc-400">
                <span>
                    Dari transaksi:
                    <span class="{{ $totalTracked >= 0 ? 'text-zinc-200' : 'text-rose-400' }} font-medium">
                        {{ ($totalTracked < 0 ? '-' : '') . 'Rp ' . number_format(abs($totalTracked), 0, ',', '.') }}
                    </span>
                </span>
                <span>
                    Penyesuaian:
                    <span class="{{ $totalManual >= 0 ? 'text-zinc-200' : 'text-rose-400' }} font-medium">
                        {{ ($totalManual >= 0 ? '+' : '-') . 'Rp ' . number_format(abs($totalManual), 0, ',', '.') }}
                    </span>
                </span>
            </div>
        </div>
    </div>

    {{-- Wallet breakdown with per-wallet adjustments --}}
    <div class="px-4 mb-4">
        <h2 class="text-sm font-semibold text-zinc-400 mb-2">Rincian per Dompet</h2>

        @forelse($walletTree as $wallet)
            @php
                $walletItems = collect([$wallet])->merge($wallet->children);
            @endphp
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl mb-2 overflow-hidden">

                {{-- Parent wallet row --}}
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold">{{ $wallet->name }}</p>
                        <p class="text-xs text-zinc-500 capitalize">{{ $wallet->type }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold {{ $wallet->effectiveBalance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ ($wallet->effectiveBalance < 0 ? '-' : '') . 'Rp ' . number_format(abs($wallet->effectiveBalance), 0, ',', '.') }}
                        </p>
                        @if($wallet->adjustmentTotal != 0)
                            <p class="text-[10px] text-zinc-500">
                                Transaksi {{ ($wallet->balance < 0 ? '-' : '') }}Rp {{ number_format(abs($wallet->balance), 0, ',', '.') }}
                                + Sesuaian {{ ($wallet->adjustmentTotal >= 0 ? '+' : '-') }}Rp {{ number_format(abs($wallet->adjustmentTotal), 0, ',', '.') }}
                            </p>
                        @else
                            <p class="text-[10px] text-zinc-500">Dari transaksi</p>
                        @endif
                    </div>
                </div>

                {{-- Parent's own adjustments --}}
                @if($wallet->adjustments->isNotEmpty())
                    <div class="border-t border-zinc-800 px-4 py-2 space-y-1.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-600 mb-1">Penyesuaian – {{ $wallet->name }}</p>
                        @foreach($wallet->adjustments as $adj)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-zinc-300 truncate">{{ $adj->notes ?: ($adj->amount >= 0 ? 'Penambahan' : 'Pengurangan') }}</p>
                                    <p class="text-[10px] text-zinc-600">{{ $adj->occurred_at->translatedFormat('d M Y') }}</p>
                                </div>
                                <span class="text-xs font-semibold {{ $adj->amount >= 0 ? 'text-emerald-400' : 'text-rose-400' }} shrink-0">
                                    {{ $adj->amount >= 0 ? '+' : '-' }}Rp {{ number_format(abs($adj->amount), 0, ',', '.') }}
                                </span>
                                <div class="flex gap-0.5 shrink-0">
                                    <a href="{{ route('savings.edit', $adj) }}" class="p-1.5 text-zinc-600 hover:text-zinc-300 rounded-lg hover:bg-zinc-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('savings.destroy', $adj) }}" onsubmit="return confirm('Hapus penyesuaian ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-zinc-600 hover:text-rose-400 rounded-lg hover:bg-zinc-800 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Child wallets --}}
                @foreach($wallet->children as $child)
                    <div class="border-t border-zinc-800/60">
                        <div class="px-4 py-2.5 flex items-center justify-between pl-7">
                            <div>
                                <p class="text-xs text-zinc-300">{{ $child->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold {{ $child->effectiveBalance >= 0 ? 'text-zinc-200' : 'text-rose-400' }}">
                                    {{ ($child->effectiveBalance < 0 ? '-' : '') . 'Rp ' . number_format(abs($child->effectiveBalance), 0, ',', '.') }}
                                </p>
                                @if($child->adjustmentTotal != 0)
                                    <p class="text-[10px] text-zinc-600">
                                        Sesuaian {{ ($child->adjustmentTotal >= 0 ? '+' : '-') }}Rp {{ number_format(abs($child->adjustmentTotal), 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Child adjustments --}}
                        @if($child->adjustments->isNotEmpty())
                            <div class="px-4 pb-2 space-y-1.5 pl-7">
                                @foreach($child->adjustments as $adj)
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-zinc-400 truncate">{{ $adj->notes ?: ($adj->amount >= 0 ? 'Penambahan' : 'Pengurangan') }}</p>
                                            <p class="text-[10px] text-zinc-600">{{ $adj->occurred_at->translatedFormat('d M Y') }}</p>
                                        </div>
                                        <span class="text-xs font-semibold {{ $adj->amount >= 0 ? 'text-emerald-400' : 'text-rose-400' }} shrink-0">
                                            {{ $adj->amount >= 0 ? '+' : '-' }}Rp {{ number_format(abs($adj->amount), 0, ',', '.') }}
                                        </span>
                                        <div class="flex gap-0.5 shrink-0">
                                            <a href="{{ route('savings.edit', $adj) }}" class="p-1.5 text-zinc-600 hover:text-zinc-300 rounded-lg hover:bg-zinc-800 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('savings.destroy', $adj) }}" onsubmit="return confirm('Hapus penyesuaian ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-zinc-600 hover:text-rose-400 rounded-lg hover:bg-zinc-800 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

            </div>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-8 text-center">
                <p class="text-sm text-zinc-500">Belum ada dompet.</p>
                <a href="{{ route('wallets.create') }}" class="text-sm text-emerald-400 mt-1 inline-block">+ Tambah dompet</a>
            </div>
        @endforelse
    </div>

</x-layouts.app>
