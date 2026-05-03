<x-layouts.app :title="'Beranda'">

    {{-- Month/year selector header --}}
    <div class="flex items-center justify-between px-4 pt-5 pb-2">
        <h1 class="text-lg font-semibold">Ringkasan</h1>
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
            <input type="hidden" name="breakdown_filter" value="{{ $breakdownFilter }}">
            <select name="owner_id"
                    onchange="this.form.submit()"
                    class="bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Semua</option>
                @foreach($rootWallets as $rw)
                    <option value="{{ $rw->id }}" @selected($rw->id === $ownerId)>{{ $rw->name }}</option>
                @endforeach
            </select>
            <select name="month"
                    onchange="this.form.submit()"
                    class="bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" @selected($m === $month)>
                        {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                    </option>
                @endforeach
            </select>
            <select name="year"
                    onchange="this.form.submit()"
                    class="bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach(range(now()->year - 3, now()->year + 1) as $y)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-2 px-4 mb-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Pemasukan</span>
            <span class="text-sm font-bold text-emerald-400">{{ 'Rp '.number_format($summary['income'], 0, ',', '.') }}</span>

            {{-- Semua filter: income breakdown per owner --}}
            @if(!$ownerId && count($summary['income_detail']) > 1)
                <div class="relative" id="incomeDetailWrap">
                    <button type="button" onclick="toggleIncomeDetail()"
                            class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1 w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                        Rincian
                    </button>
                    <div id="incomeDetailTooltip"
                         class="hidden absolute bottom-full left-0 mb-1.5 bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 shadow-xl z-20 min-w-max">
                        <p class="text-[10px] text-zinc-500 mb-1">Pemasukan per pemilik</p>
                        @foreach($summary['income_detail'] as $d)
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs text-zinc-300">{{ $d['name'] }}</span>
                                <span class="text-xs font-semibold text-emerald-400">Rp {{ number_format($d['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Owner filter: transfers received from other owners --}}
            @if($ownerId && $summary['transfer_in_others'] > 0)
                <div class="relative" id="transferInOthersWrap">
                    <button type="button" onclick="toggleTransferInOthers()"
                            class="text-[11px] font-semibold text-sky-400 flex items-center gap-1 w-full text-left">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                        {{ 'Rp '.number_format($summary['transfer_in_others'], 0, ',', '.') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div id="transferInOthersTooltip"
                         class="hidden absolute bottom-full left-0 mb-1.5 bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 shadow-xl z-20 min-w-max">
                        <p class="text-[10px] text-zinc-500 mb-1">Transfer masuk dari</p>
                        @foreach($summary['transfer_in_others_detail'] as $d)
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs text-zinc-300">← {{ $d['name'] }}</span>
                                <span class="text-xs font-semibold text-sky-400">Rp {{ number_format($d['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Pengeluaran</span>
            <span class="text-sm font-bold text-rose-400">{{ 'Rp '.number_format($summary['expense'], 0, ',', '.') }}</span>
            @if($summary['transfer_savings'] > 0)
                <span class="text-[11px] font-semibold text-violet-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500 shrink-0"></span>
                    {{ 'Rp '.number_format($summary['transfer_savings'], 0, ',', '.') }}
                </span>
            @endif
            @if($summary['transfer_others'] > 0)
                <div class="relative" id="transferOthersWrap">
                    <button type="button"
                            onclick="toggleTransferOthers()"
                            class="text-[11px] font-semibold text-amber-400 flex items-center gap-1 w-full text-left">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                        {{ 'Rp '.number_format($summary['transfer_others'], 0, ',', '.') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div id="transferOthersTooltip"
                         class="hidden absolute bottom-full left-0 mb-1.5 bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 shadow-xl z-20 min-w-max">
                        <p class="text-[10px] text-zinc-500 mb-1">Transfer ke</p>
                        @foreach($summary['transfer_others_detail'] as $detail)
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs text-zinc-300">→ {{ $detail['name'] }}</span>
                                <span class="text-xs font-semibold text-amber-400">Rp {{ number_format($detail['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-3 flex flex-col gap-1">
            <span class="text-xs text-zinc-500">Selisih</span>
            <span class="text-sm font-bold {{ $summary['net'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ ($summary['net'] < 0 ? '-' : '') . 'Rp '.number_format(abs($summary['net']), 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Wallet balances — spendable wallets (monthly activity) --}}
    <div class="px-4 mb-2">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-sm font-semibold text-zinc-400">Dompet Aktif</h2>
                <p class="text-[10px] text-zinc-600 mt-0.5">Aktivitas {{ \Carbon\Carbon::create($year, $month)->locale('id')->isoFormat('MMMM YYYY') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('wallets.index') }}" class="text-xs text-emerald-400">Lihat semua</a>
                <button type="button" onclick="toggleSection('sec_dompet_aktif', this)"
                        class="w-6 h-6 flex items-center justify-center rounded-lg bg-zinc-800 text-zinc-400 hover:bg-zinc-700 transition-colors">
                    <svg class="sec-chev w-3.5 h-3.5 transition-transform duration-200" style="transform:rotate(180deg)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="sec_dompet_aktif" class="space-y-2">
            @forelse($walletTreeMonth as $wallet)
                @php
                $savingTypes      = ['savings', 'investment'];
                $typeLabels       = ['cash' => 'Tunai', 'bank' => 'Bank', 'e-wallet' => 'Dompet Digital', 'other' => 'Lainnya', 'general' => 'Umum'];
                $spendableKids    = $wallet->children->filter(fn($c) => ! in_array($c->type, $savingTypes))->values();
                $savingsKids      = $wallet->children->filter(fn($c) => in_array($c->type, $savingTypes))->values();
                $spendableBalance = $wallet->balance - $savingsKids->sum('balance');
                $hasMixed         = $spendableKids->isNotEmpty() && $savingsKids->isNotEmpty();
                @endphp
                @php
                $savingsTotal = $savingsKids->sum('balance');
                @endphp
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">

                    {{-- Header: name + spendable balance only --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">{{ $wallet->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $typeLabels[$wallet->type] ?? ucfirst($wallet->type) }}</p>
                        </div>
                        <span class="text-sm font-semibold {{ $spendableBalance >= 0 ? 'text-zinc-100' : 'text-rose-400' }}">
                            {{ ($spendableBalance < 0 ? '-' : '') }}Rp {{ number_format(abs($spendableBalance), 0, ',', '.') }}
                        </span>
                    </div>

                    @if($wallet->children->isNotEmpty())
                        <div class="mt-2 space-y-1 pl-3 border-l border-zinc-700">

                            {{-- Spendable children --}}
                            @foreach($spendableKids as $child)
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-zinc-400">{{ $child->name }}</p>
                                    <span class="text-xs {{ $child->balance >= 0 ? 'text-zinc-300' : 'text-rose-400' }}">
                                        {{ ($child->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($child->balance), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Savings / investment toggle — hint shows savings total even when collapsed --}}
                            @if($savingsKids->isNotEmpty())
                                @php $toggleId = 'savings_kids_' . $wallet->id; @endphp
                                <button type="button"
                                        onclick="(function(btn){var el=document.getElementById('{{ $toggleId }}');var icon=btn.querySelector('.chev');var hidden=el.classList.toggle('hidden');icon.style.transform=hidden?'':'rotate(180deg)';})(this)"
                                        class="flex items-center gap-2 w-full pt-1.5 pb-0.5 group">
                                    <div class="flex-1 h-px bg-zinc-800"></div>
                                    <span class="text-[10px] font-semibold text-emerald-700 group-hover:text-emerald-600 transition-colors flex items-center gap-1 shrink-0">
                                        <svg class="chev w-3 h-3 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                        </svg>
                                        Tabungan
                                        <span class="text-emerald-600">Rp {{ number_format(abs($savingsTotal), 0, ',', '.') }}</span>
                                    </span>
                                    <div class="flex-1 h-px bg-zinc-800"></div>
                                </button>

                                <div id="{{ $toggleId }}" class="hidden space-y-1">
                                    @foreach($savingsKids as $child)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1 h-1 rounded-full bg-emerald-600 shrink-0"></span>
                                                <p class="text-xs text-zinc-500">{{ $child->name }}</p>
                                            </div>
                                            <span class="text-xs {{ $child->balance >= 0 ? 'text-emerald-500/80' : 'text-rose-400' }}">
                                                {{ ($child->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($child->balance), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach

                                    {{-- Total (spendable + savings) — only shown when expanded --}}
                                    <div class="flex items-center justify-between pt-1.5 mt-0.5 border-t border-zinc-800">
                                        <span class="text-[10px] uppercase tracking-wide text-zinc-600">Total</span>
                                        <span class="text-xs font-semibold text-zinc-500">
                                            {{ ($wallet->balance < 0 ? '-' : '') }}Rp {{ number_format(abs($wallet->balance), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-6 text-center">
                    <p class="text-sm text-zinc-500">Belum ada dompet aktif.</p>
                    <a href="{{ route('wallets.create') }}" class="text-sm text-emerald-400 mt-1 inline-block">+ Tambah dompet</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Savings / Investment wallets (cumulative saldo) --}}
    @if($walletTreeSavings->isNotEmpty())
    <div class="px-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-sm font-semibold text-zinc-400">Tabungan & Investasi</h2>
                <p class="text-[10px] text-zinc-600 mt-0.5">Total saldo kumulatif</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('savings.index') }}" class="text-xs text-emerald-400">Detail</a>
                <button type="button" onclick="toggleSection('sec_tabungan', this)"
                        class="w-6 h-6 flex items-center justify-center rounded-lg bg-zinc-800 text-zinc-400 hover:bg-zinc-700 transition-colors">
                    <svg class="sec-chev w-3.5 h-3.5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="sec_tabungan" class="hidden space-y-2">
            @foreach($walletTreeSavings as $wallet)
                @php
                $savingsTypeLabels = ['savings' => 'Tabungan', 'investment' => 'Investasi'];
                @endphp
                <div class="bg-zinc-900 border border-emerald-900/40 rounded-2xl px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <div>
                                <p class="text-sm font-medium">{{ $wallet->name }}</p>
                                <p class="text-xs text-emerald-700">{{ $savingsTypeLabels[$wallet->type] ?? ucfirst($wallet->type) }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold {{ $wallet->balance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ ($wallet->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($wallet->balance), 0, ',', '.') }}
                        </span>
                    </div>
                    @if($wallet->children->isNotEmpty())
                        <div class="mt-2 space-y-1 pl-5 border-l border-emerald-900/50">
                            @foreach($wallet->children as $child)
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-zinc-400">{{ $child->name }}</p>
                                    <span class="text-xs {{ $child->balance >= 0 ? 'text-emerald-400/70' : 'text-rose-400' }}">
                                        {{ ($child->balance < 0 ? '-' : '') . 'Rp '.number_format(abs($child->balance), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Category breakdown --}}
    <div class="px-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-zinc-400">Pengeluaran per Kategori</h2>
            {{-- Filter chips + toggle --}}
            <div class="flex items-center gap-1.5">
                @php
                    $bfBase = ['month' => $month, 'year' => $year, 'owner_id' => $ownerId ?: ''];
                    $filters = [
                        'all'     => 'Semua',
                        'regular' => 'Aktif',
                        'savings' => 'Tabungan',
                    ];
                @endphp
                @foreach($filters as $fKey => $fLabel)
                    @php $isActive = $breakdownFilter === $fKey; @endphp
                    <a href="{{ route('dashboard', array_merge($bfBase, ['breakdown_filter' => $fKey])) }}"
                       class="px-2 py-0.5 rounded-full text-[11px] font-medium transition-colors
                              {{ $isActive
                                 ? ($fKey === 'savings' ? 'bg-violet-600 text-white' : 'bg-rose-600 text-white')
                                 : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700' }}">
                        {{ $fLabel }}
                    </a>
                @endforeach
                <button type="button" onclick="toggleSection('sec_breakdown', this)"
                        class="w-6 h-6 flex items-center justify-center rounded-lg bg-zinc-800 text-zinc-400 hover:bg-zinc-700 transition-colors ml-0.5">
                    <svg class="sec-chev w-3.5 h-3.5 transition-transform duration-200" style="transform:rotate(180deg)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="sec_breakdown">
        @if($breakdownFilter === 'all' && $breakdown->where('entry_type', 'transfer_savings')->isNotEmpty() && $breakdown->where('entry_type', 'expense')->isNotEmpty())
        <div class="flex items-center gap-3 mb-2">
            <span class="flex items-center gap-1 text-[10px] text-zinc-500">
                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span> Pengeluaran
            </span>
            <span class="flex items-center gap-1 text-[10px] text-zinc-500">
                <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0"></span> Transfer ke Tabungan
            </span>
        </div>
        @endif

        @if($breakdown->isNotEmpty())
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 space-y-3">
            @foreach($breakdown as $item)
                @php
                    $isSavingsTransfer = $item->entry_type === 'transfer_savings';
                    $barColor = $isSavingsTransfer ? 'bg-violet-500' : 'bg-rose-500';
                    $dotColor = $isSavingsTransfer ? 'bg-violet-500' : 'bg-rose-500';
                @endphp
                @if($isSavingsTransfer)
                {{-- Transfer-to-savings row: not clickable --}}
                <div class="w-full">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-zinc-300 flex items-center gap-1.5 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0"></span>
                            <span class="truncate">{{ $item->category_name }}</span>
                            @if(!empty($item->source_parent_name))
                                <span class="text-[10px] text-zinc-500 shrink-0">dari</span>
                                <span class="text-[10px] font-medium text-violet-400 shrink-0">{{ $item->source_parent_name }}</span>
                            @endif
                            <span class="text-[10px] text-violet-500 bg-violet-950/60 px-1.5 py-0.5 rounded-full shrink-0">Transfer</span>
                        </span>
                        <span class="text-xs text-zinc-400 shrink-0 ml-2">{{ $item->percentage }}%
                            <span class="text-zinc-500 ml-1">{{ 'Rp '.number_format($item->amount, 0, ',', '.') }}</span>
                        </span>
                    </div>
                    <div class="h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $item->percentage }}%"></div>
                    </div>
                </div>
                @else
                {{-- Expense row: clickable, opens category modal --}}
                <button type="button"
                        class="w-full text-left active:opacity-70 transition-opacity"
                        onclick="openCatModal({{ $item->category_id }}, '{{ addslashes($item->category_name) }}', '{{ number_format($item->amount, 0, ',', '.') }}')">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-zinc-300 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0"></span>
                            {{ $item->category_name }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                            </svg>
                        </span>
                        <span class="text-xs text-zinc-400">{{ $item->percentage }}%
                            <span class="text-zinc-500 ml-1">{{ 'Rp '.number_format($item->amount, 0, ',', '.') }}</span>
                        </span>
                    </div>
                    <div class="h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $item->percentage }}%"></div>
                    </div>
                </button>
                @endif
            @endforeach
        </div>
        @else
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-6 text-center">
            <p class="text-sm text-zinc-500">Tidak ada pengeluaran bulan ini.</p>
        </div>
        @endif
        </div>{{-- end sec_breakdown --}}
    </div>
    <div id="catModal" class="fixed inset-0 z-[60] hidden" aria-modal="true" role="dialog">
        <div id="catModalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div id="catModalSheet"
             class="absolute bottom-0 left-0 right-0 bg-zinc-900 rounded-t-2xl flex flex-col max-h-[85vh] translate-y-full transition-transform duration-300 ease-out">

            {{-- Handle --}}
            <div class="flex justify-center pt-3 pb-1 shrink-0">
                <div class="w-10 h-1 rounded-full bg-zinc-700"></div>
            </div>

            {{-- Header --}}
            <div class="flex items-start justify-between px-4 py-3 border-b border-zinc-800 shrink-0">
                <div>
                    <h3 id="catModalTitle" class="text-base font-semibold text-zinc-100"></h3>
                    <p id="catModalSubtitle" class="text-xs text-zinc-500 mt-0.5"></p>
                </div>
                <button onclick="closeCatModal()"
                        class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 shrink-0 ml-3 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div id="catModalBody" class="overflow-y-auto flex-1 px-4 py-3 space-y-2">
                <div id="catModalLoading" class="flex justify-center py-8">
                    <svg class="w-5 h-5 text-zinc-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent transactions --}}
    <div class="px-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-zinc-400">Transaksi Terakhir</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.index') }}" class="text-xs text-emerald-400">Lihat semua</a>
                <button type="button" onclick="toggleSection('sec_recent', this)"
                        class="w-6 h-6 flex items-center justify-center rounded-lg bg-zinc-800 text-zinc-400 hover:bg-zinc-700 transition-colors">
                    <svg class="sec-chev w-3.5 h-3.5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="sec_recent" class="hidden space-y-2">
            @forelse($recent as $tx)
                <a href="{{ route('transactions.show', $tx) }}"
                   class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 flex items-center gap-3 active:bg-zinc-800 transition-colors block">
                    {{-- Type icon --}}
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
                            {{ $tx->type === 'expense' ? '-' : '+' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-zinc-500">{{ $tx->occurred_at->format('d M') }}</p>
                    </div>
                </a>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-6 text-center">
                    <p class="text-sm text-zinc-500">Belum ada transaksi.</p>
                    <a href="{{ route('transactions.create') }}" class="text-sm text-emerald-400 mt-1 inline-block">+ Catat transaksi</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- FAB --}}
    <a href="{{ route('transactions.create') }}"
       class="fixed bottom-20 right-4 z-40 w-14 h-14 rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-900/50 flex items-center justify-center hover:bg-emerald-400 active:bg-emerald-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
    </a>

    <script>
        const _catApiUrl = '{{ route('dashboard.category.transactions') }}';
        const _catMonth  = {{ $month }};
        const _catYear   = {{ $year }};

        const catModal    = document.getElementById('catModal');
        const catSheet    = document.getElementById('catModalSheet');
        const catBackdrop = document.getElementById('catModalBackdrop');
        const catTitle    = document.getElementById('catModalTitle');
        const catSubtitle = document.getElementById('catModalSubtitle');
        const catBody     = document.getElementById('catModalBody');
        const catLoading  = document.getElementById('catModalLoading');

        function toggleSection(id, btn) {
            var el   = document.getElementById(id);
            var chev = btn.querySelector('.sec-chev');
            var hidden = el.classList.toggle('hidden');
            if (chev) chev.style.transform = hidden ? '' : 'rotate(180deg)';
        }

        function openCatModal(categoryId, categoryName, totalFormatted) {
            catTitle.textContent    = categoryName;
            catSubtitle.textContent = 'Total: Rp\u00A0' + totalFormatted;
            catBody.innerHTML       = catLoading.outerHTML;

            catModal.classList.remove('hidden');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    catSheet.classList.remove('translate-y-full');
                });
            });

            fetch(_catApiUrl + '?category_id=' + categoryId + '&month=' + _catMonth + '&year=' + _catYear)
                .then(function(r) { return r.json(); })
                .then(function(items) {
                    if (!items.length) {
                        catBody.innerHTML = '<p class="text-sm text-zinc-500 text-center py-8">Tidak ada transaksi.</p>';
                        return;
                    }
                    catBody.innerHTML = items.map(function(tx) {
                        return '<a href="/transactions/' + tx.id + '"'
                            + ' class="flex items-center justify-between bg-zinc-800/60 border border-zinc-700/50 rounded-xl px-3 py-2.5 active:bg-zinc-700 transition-colors">'
                            + '<div class="min-w-0">'
                            + '<p class="text-xs text-zinc-500">' + tx.date + ' &middot; ' + tx.wallet + '</p>'
                            + (tx.notes ? '<p class="text-sm text-zinc-300 truncate mt-0.5">' + tx.notes + '</p>' : '<p class="text-sm text-zinc-500 italic mt-0.5">Tanpa catatan</p>')
                            + '</div>'
                            + '<span class="text-sm font-semibold text-rose-400 shrink-0 ml-3">-Rp\u00A0' + Number(tx.amount).toLocaleString('id-ID') + '</span>'
                            + '</a>';
                    }).join('');
                });
        }

        function closeCatModal() {
            catSheet.classList.add('translate-y-full');
            setTimeout(function() { catModal.classList.add('hidden'); }, 300);
        }

        catBackdrop.addEventListener('click', closeCatModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeCatModal();
        });

        // Tooltip toggles
        function makeTooltipToggle(wrapperId, tooltipId) {
            return function() {
                const tip = document.getElementById(tooltipId);
                if (!tip) return;
                tip.classList.toggle('hidden');
            };
        }

        var toggleTransferOthers   = makeTooltipToggle('transferOthersWrap',   'transferOthersTooltip');
        var toggleIncomeDetail     = makeTooltipToggle('incomeDetailWrap',      'incomeDetailTooltip');
        var toggleTransferInOthers = makeTooltipToggle('transferInOthersWrap',  'transferInOthersTooltip');

        var _tooltips = [
            ['transferOthersWrap',   'transferOthersTooltip'],
            ['incomeDetailWrap',     'incomeDetailTooltip'],
            ['transferInOthersWrap', 'transferInOthersTooltip'],
        ];

        document.addEventListener('click', function(e) {
            _tooltips.forEach(function(pair) {
                var wrap = document.getElementById(pair[0]);
                var tip  = document.getElementById(pair[1]);
                if (wrap && tip && !wrap.contains(e.target)) {
                    tip.classList.add('hidden');
                }
            });
        });
    </script>

</x-layouts.app>
