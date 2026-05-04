<x-layouts.app :title="'Transaksi'">

@php
$advancedFilterCount = collect(request()->only(['type','wallet_id','category_id','date_from','date_to']))->filter()->count();
$showAdvanced  = $advancedFilterCount > 0;
$hasAnyFilter  = $showAdvanced || collect(request()->only(['owner_id','search']))->filter()->isNotEmpty();

$filterWallet   = $wallets->firstWhere('id', request('wallet_id'));
$filterCategory = $categories->firstWhere('id', request('category_id'));

$filterWalletLabel = '';
$filterWalletBal   = null;
if ($filterWallet) {
    $filterWalletLabel = $filterWallet->parent
        ? $filterWallet->parent->name . ' › ' . $filterWallet->name
        : $filterWallet->name;
    $filterWalletBal = (float) ($balances->get($filterWallet->id) ?? 0);
}

$idMonths = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$grouped  = $transactions->getCollection()->groupBy(fn($tx) => $tx->occurred_at->format('Y-m-d'));
@endphp

{{-- ─── Header ─────────────────────────────────────────── --}}
<div class="flex items-center justify-between px-4 pt-5 pb-3">
    <h1 class="text-lg font-semibold">Transaksi</h1>
    <a href="{{ route('transactions.create') }}"
       class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors min-h-[44px]">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Tambah
    </a>
</div>

{{-- ─── Filter form ─────────────────────────────────────── --}}
<form method="GET" action="{{ route('transactions.index') }}" id="filterForm" class="px-4 mb-4">
    {{-- Hidden state for chip values --}}
    <input type="hidden" name="type"     id="fi_type"  value="{{ request('type', '') }}">
    <input type="hidden" name="owner_id" id="fi_owner" value="{{ $ownerId ?? '' }}">

    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 shadow-lg shadow-black/20 p-3 space-y-3">

        {{-- ── Row 1: Search + Filter toggle ──────────────────── --}}
        <div class="flex gap-2 items-stretch">
            {{-- Search input --}}
            <div class="flex-1 flex items-center gap-2 min-h-[44px] bg-zinc-800/90 border border-zinc-700 rounded-xl px-3
                        focus-within:ring-2 focus-within:ring-emerald-500/80 focus-within:border-emerald-600/40 transition-shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Cari catatan, kategori, dompet…"
                       autocomplete="off"
                       class="flex-1 min-w-0 bg-transparent text-zinc-100 text-sm py-2 outline-none placeholder:text-zinc-600">
                <button type="button" id="searchClear"
                        onclick="clearSearch()"
                        class="{{ request('search') ? 'flex' : 'hidden' }} shrink-0 w-8 h-8 items-center justify-center rounded-lg bg-zinc-700/80 hover:bg-zinc-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Filter toggle --}}
            <button type="button" id="filterToggle" onclick="toggleAdvanced()"
                    aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}"
                    aria-controls="advancedFilter"
                    title="Filter lanjutan"
                    class="relative shrink-0 min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl border transition-colors
                           {{ $showAdvanced
                              ? 'bg-emerald-600 border-emerald-500 text-white shadow-md shadow-emerald-900/30'
                              : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:text-zinc-100 hover:border-zinc-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
                </svg>
                @if($advancedFilterCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none ring-2 ring-zinc-900">
                        {{ $advancedFilterCount }}
                    </span>
                @endif
            </button>
        </div>

        {{-- ── Row 2: Owner chips ───────────────────────────────── --}}
        @if($rootWallets->isNotEmpty())
        <div class="space-y-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5">Grup dompet</p>
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-none snap-x snap-proximity">
                <button type="button"
                        onclick="setChip('fi_owner', '', [['[name=wallet_id]', '']])"
                        class="snap-start shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all active:scale-[0.98]
                               {{ !$ownerId ? 'bg-zinc-600 text-white ring-2 ring-zinc-500/40' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200 border border-zinc-700/80' }}">
                    Semua
                </button>
                @foreach($rootWallets as $rw)
                    <button type="button"
                            onclick="setChip('fi_owner', '{{ $rw->id }}', [['[name=wallet_id]', '']])"
                            class="snap-start shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all active:scale-[0.98]
                                   {{ $ownerId === $rw->id ? 'bg-indigo-600 text-white ring-2 ring-indigo-400/30' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200 border border-zinc-700/80' }}">
                        {{ $rw->name }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Active filter pills ─────────────────────────────── --}}
        @if($advancedFilterCount > 0)
        <div class="space-y-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5">Filter aktif</p>
            <div class="flex gap-2 flex-wrap">
                @if(request('type'))
                    @php
                    $typeLabels = ['income' => 'Pemasukan', 'expense' => 'Pengeluaran', 'transfer' => 'Transfer'];
                    $typeColors = ['income' => 'bg-emerald-950/80 text-emerald-300 border-emerald-700/80',
                                   'expense'=> 'bg-rose-950/80 text-rose-300 border-rose-700/80',
                                   'transfer'=>'bg-blue-950/80 text-blue-300 border-blue-700/80'];
                    @endphp
                    <a href="{{ route('transactions.index', request()->except('type')) }}"
                       class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full border text-xs font-medium transition-colors hover:brightness-110
                              {{ $typeColors[request('type')] ?? 'bg-zinc-800 text-zinc-300 border-zinc-700' }}">
                        {{ $typeLabels[request('type')] ?? request('type') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
                @if($filterWallet)
                    <a href="{{ route('transactions.index', request()->except('wallet_id')) }}"
                       class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full border border-zinc-600 bg-zinc-800/90 text-zinc-200 text-xs font-medium transition-colors hover:bg-zinc-700 hover:border-zinc-500">
                        {{ $filterWallet->name }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
                @if($filterCategory)
                    <a href="{{ route('transactions.index', request()->except('category_id')) }}"
                       class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full border border-zinc-600 bg-zinc-800/90 text-zinc-200 text-xs font-medium transition-colors hover:bg-zinc-700 hover:border-zinc-500">
                        {{ $filterCategory->name }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
                @if(request('date_from') || request('date_to'))
                    @php
                    $dfLabel = request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->day . ' ' . $idMonths[\Carbon\Carbon::parse(request('date_from'))->month - 1] : '';
                    $dtLabel = request('date_to')   ? \Carbon\Carbon::parse(request('date_to'))->day   . ' ' . $idMonths[\Carbon\Carbon::parse(request('date_to'))->month   - 1] : '';
                    $dateRangeLabel = $dfLabel && $dtLabel ? "$dfLabel – $dtLabel" : ($dfLabel ? "Dari $dfLabel" : "S/d $dtLabel");
                    @endphp
                    <a href="{{ route('transactions.index', request()->except(['date_from','date_to'])) }}"
                       class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full border border-zinc-600 bg-zinc-800/90 text-zinc-200 text-xs font-medium transition-colors hover:bg-zinc-700 hover:border-zinc-500">
                        {{ $dateRangeLabel }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Collapsible advanced filter panel ───────────────── --}}
        <div id="advancedFilter"
             role="region"
             aria-label="Filter lanjutan"
             class="{{ $showAdvanced ? 'space-y-4 pt-3 mt-0.5 border-t border-zinc-800/90' : 'hidden' }}">

            {{-- Type chips --}}
            <div class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5">Jenis transaksi</p>
                <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-none snap-x snap-proximity">
                    @foreach(['' => 'Semua', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran', 'transfer' => 'Transfer'] as $val => $label)
                        @php
                        $isActive = request('type', '') === $val;
                        $activeCls = match($val) {
                            'expense'  => 'bg-rose-600 text-white ring-2 ring-rose-400/25 shadow-md shadow-rose-950/40',
                            'income'   => 'bg-emerald-600 text-white ring-2 ring-emerald-400/25 shadow-md shadow-emerald-950/40',
                            'transfer' => 'bg-blue-600 text-white ring-2 ring-blue-400/25 shadow-md shadow-blue-950/40',
                            default    => 'bg-zinc-600 text-white ring-2 ring-zinc-400/20',
                        };
                        @endphp
                        <button type="button" onclick="setChip('fi_type', '{{ $val }}')"
                                class="snap-start shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all active:scale-[0.98]
                                       {{ $isActive ? $activeCls : 'bg-zinc-800 text-zinc-400 hover:text-zinc-100 border border-zinc-700/80' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Wallet + Category search (same pattern as create) --}}
            <div class="space-y-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5 mb-1.5">Dompet</p>
                    <x-wallet-search
                        name="wallet_id"
                        :initialId="request('wallet_id')"
                        :initialLabel="$filterWalletLabel"
                        :initialBalance="$filterWalletBal"
                        :ownerId="$ownerId"
                        placeholder="Cari dompet..."
                    />
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5 mb-1.5">Kategori</p>
                    <x-category-search
                        :initialId="request('category_id')"
                        :initialLabel="$filterCategory?->name ?? ''"
                        placeholder="Cari kategori..."
                    />
                </div>
            </div>

            {{-- Date range (single row) --}}
            <div class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 px-0.5">Periode</p>
                <div class="flex items-center gap-2 min-w-0">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="flex-1 min-w-0 min-h-[44px] bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-xl px-2 sm:px-3 py-2 focus:ring-2 focus:ring-emerald-500/80 focus:border-emerald-600/50 outline-none">
                    <span class="text-zinc-600 text-sm shrink-0 select-none">—</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="flex-1 min-w-0 min-h-[44px] bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm rounded-xl px-2 sm:px-3 py-2 focus:ring-2 focus:ring-emerald-500/80 focus:border-emerald-600/50 outline-none">
                </div>
            </div>

            {{-- Apply + Reset row --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 pt-1">
                @if($hasAnyFilter)
                    <a href="{{ route('transactions.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 min-h-[44px] sm:min-h-0 text-xs font-medium text-zinc-500 hover:text-rose-400 transition-colors rounded-xl sm:justify-start px-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        Reset semua filter
                    </a>
                @else
                    <span class="hidden sm:block"></span>
                @endif
                <button type="submit"
                        class="w-full sm:w-auto shrink-0 min-h-[44px] bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-semibold px-6 rounded-xl transition-colors shadow-md shadow-emerald-950/30">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</form>

{{-- ─── Separator ───────────────────────────────────────── --}}
<div class="mx-4 border-t border-zinc-800 mb-3"></div>

{{-- ─── Transaction count label ─────────────────────────── --}}
<div class="px-4 mb-2 flex items-center justify-between">
    <span class="text-xs text-zinc-500">
        {{ number_format($transactions->total(), 0, ',', '.') }} transaksi
        @if($hasAnyFilter)<span class="text-zinc-600">· difilter</span>@endif
    </span>
    @if($transactions->hasPages())
        <span class="text-xs text-zinc-600">Hal. {{ $transactions->currentPage() }}/{{ $transactions->lastPage() }}</span>
    @endif
</div>

{{-- ─── Transaction list (grouped by date) ─────────────── --}}
<div class="px-4 space-y-4 pb-4">
    @forelse($grouped as $date => $group)
        @php
        $carbon      = \Carbon\Carbon::parse($date);
        $isToday     = $carbon->isToday();
        $isYesterday = $carbon->isYesterday();
        $dateLabel   = $isToday
            ? 'Hari ini'
            : ($isYesterday
                ? 'Kemarin'
                : ($carbon->year === now()->year
                    ? ($carbon->day . ' ' . $idMonths[$carbon->month - 1])
                    : ($carbon->day . ' ' . $idMonths[$carbon->month - 1] . ' ' . $carbon->year)));
        $dayIncome   = $group->where('type', 'income')->sum('amount');
        $dayExpense  = $group->where('type', 'expense')->sum('amount');
        @endphp

        <div>
            {{-- Date group header --}}
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-semibold text-zinc-400 tracking-wide">{{ $dateLabel }}</span>
                <div class="flex items-center gap-2 text-xs">
                    @if($dayIncome > 0)
                        <span class="text-emerald-500 font-medium">+{{ number_format($dayIncome, 0, ',', '.') }}</span>
                    @endif
                    @if($dayExpense > 0)
                        <span class="text-rose-500 font-medium">-{{ number_format($dayExpense, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>

            {{-- Transactions for this date --}}
            <div class="space-y-1.5">
                @foreach($group as $tx)
                    @php
                    $walletLabel   = $tx->wallet->parent
                        ? $tx->wallet->parent->name . ' › ' . $tx->wallet->name
                        : $tx->wallet->name;
                    $toWalletLabel = $tx->toWallet
                        ? ($tx->toWallet->parent
                            ? $tx->toWallet->parent->name . ' › ' . $tx->toWallet->name
                            : $tx->toWallet->name)
                        : null;
                    @endphp
                    <a href="{{ route('transactions.show', $tx) }}"
                       class="bg-zinc-900 border border-zinc-800 rounded-2xl px-3 py-3 flex items-center gap-3 active:bg-zinc-800 transition-colors block">

                        {{-- Type icon --}}
                        <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                            {{ $tx->type === 'income'
                                ? 'bg-emerald-900/60 text-emerald-400'
                                : ($tx->type === 'expense'
                                    ? 'bg-rose-900/60 text-rose-400'
                                    : 'bg-blue-900/60 text-blue-400') }}">
                            @if($tx->type === 'income')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
                                </svg>
                            @elseif($tx->type === 'expense')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-100 truncate">
                                {{ $tx->category?->name ?? ($tx->type === 'transfer' ? 'Transfer' : '—') }}
                            </p>
                            <p class="text-xs text-zinc-500 truncate mt-0.5">
                                {{ $walletLabel }}
                                @if($tx->type === 'transfer' && $toWalletLabel)
                                    <span class="text-zinc-700 mx-0.5">→</span>{{ $toWalletLabel }}
                                @endif
                            </p>
                            @if($tx->notes)
                                <p class="text-xs text-zinc-600 truncate mt-0.5 italic">{{ $tx->notes }}</p>
                            @endif
                        </div>

                        {{-- Amount --}}
                        <p class="shrink-0 text-sm font-semibold tabular-nums
                            {{ $tx->type === 'income'
                                ? 'text-emerald-400'
                                : ($tx->type === 'expense'
                                    ? 'text-rose-400'
                                    : 'text-blue-400') }}">
                            {{ $tx->type === 'income' ? '+' : ($tx->type === 'expense' ? '-' : '') }}Rp&nbsp;{{ number_format($tx->amount, 0, ',', '.') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <p class="text-zinc-500 text-sm font-medium">Tidak ada transaksi</p>
            @if($hasAnyFilter)
                <p class="text-zinc-600 text-xs mt-1">Coba ubah atau hapus filter</p>
            @else
                <a href="{{ route('transactions.create') }}" class="text-emerald-400 text-sm mt-2 inline-block">+ Catat transaksi</a>
            @endif
        </div>
    @endforelse
</div>

{{-- ─── Pagination ──────────────────────────────────────── --}}
@if($transactions->hasPages())
    <div class="px-4 mt-2 mb-4">
        {{ $transactions->links('vendor.pagination.tailwind') }}
    </div>
@endif

<script>
    const advancedFilter = document.getElementById('advancedFilter');
    const filterToggle   = document.getElementById('filterToggle');
    const searchInput    = document.getElementById('searchInput');
    const searchClear    = document.getElementById('searchClear');

    function toggleAdvanced() {
        const isHidden = advancedFilter.classList.toggle('hidden');
        filterToggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
        if (!isHidden) {
            advancedFilter.classList.add('space-y-4', 'pt-3', 'mt-0.5', 'border-t', 'border-zinc-800/90');
        } else {
            advancedFilter.classList.remove('space-y-4', 'pt-3', 'mt-0.5', 'border-t', 'border-zinc-800/90');
        }
        filterToggle.classList.toggle('bg-emerald-600',   !isHidden);
        filterToggle.classList.toggle('border-emerald-500', !isHidden);
        filterToggle.classList.toggle('text-white',         !isHidden);
        filterToggle.classList.toggle('shadow-md',          !isHidden);
        filterToggle.classList.toggle('shadow-emerald-900/30', !isHidden);
        filterToggle.classList.toggle('bg-zinc-800',        isHidden);
        filterToggle.classList.toggle('border-zinc-700',    isHidden);
        filterToggle.classList.toggle('text-zinc-400',      isHidden);
        filterToggle.classList.toggle('hover:text-zinc-100', isHidden);
        filterToggle.classList.toggle('hover:border-zinc-600', isHidden);
    }

    function setChip(hiddenId, value, clearSelectors) {
        document.getElementById(hiddenId).value = value;
        if (clearSelectors) {
            clearSelectors.forEach(function(pair) {
                var el = document.querySelector(pair[0]);
                if (el) el.value = pair[1] || '';
            });
        }
        document.getElementById('filterForm').submit();
    }

    function clearSearch() {
        searchInput.value = '';
        searchClear.classList.add('hidden');
        document.getElementById('filterForm').submit();
    }

    // Show/hide clear button as user types
    searchInput.addEventListener('input', function() {
        searchClear.classList.toggle('hidden', !this.value);
        searchClear.classList.toggle('flex',    !!this.value);
    });
</script>

</x-layouts.app>
