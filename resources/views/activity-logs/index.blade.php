<x-layouts.app :title="'Log Aktivitas'">

    <div class="flex items-center justify-between px-4 pt-5 pb-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('savings.index') }}"
               class="p-2 -ml-1 rounded-xl text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold">Log Aktivitas</h1>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('activity-logs.index') }}" class="px-4 mb-3 flex gap-2 flex-wrap">
        <select name="subject_type" onchange="this.form.submit()"
                class="bg-zinc-800 border border-zinc-700 text-zinc-300 text-sm rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            <option value="">Semua</option>
            <option value="transaction" @selected(request('subject_type') === 'transaction')>Transaksi</option>
            <option value="savings_adjustment" @selected(request('subject_type') === 'savings_adjustment')>Tabungan</option>
        </select>
        <select name="action" onchange="this.form.submit()"
                class="bg-zinc-800 border border-zinc-700 text-zinc-300 text-sm rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            <option value="">Semua Aksi</option>
            <option value="created" @selected(request('action') === 'created')>Tambah</option>
            <option value="updated" @selected(request('action') === 'updated')>Edit</option>
            <option value="deleted" @selected(request('action') === 'deleted')>Hapus</option>
        </select>
        @if(request()->hasAny(['subject_type', 'action']))
            <a href="{{ route('activity-logs.index') }}" class="text-xs text-zinc-500 hover:text-zinc-300 flex items-center px-2">✕ Reset</a>
        @endif
    </form>

    {{-- Log list --}}
    <div class="px-4 space-y-2">
        @forelse($logs as $log)
            @php
                $actionColor = match($log->action) {
                    'created' => 'bg-emerald-900/60 text-emerald-400',
                    'updated' => 'bg-blue-900/60 text-blue-400',
                    'deleted' => 'bg-rose-900/60 text-rose-400',
                    default   => 'bg-zinc-800 text-zinc-400',
                };
                $actionLabel = match($log->action) {
                    'created' => 'Tambah',
                    'updated' => 'Edit',
                    'deleted' => 'Hapus',
                    default   => $log->action,
                };
                $subjectLabel = match($log->subject_type) {
                    'transaction'        => 'Transaksi',
                    'savings_adjustment' => 'Tabungan',
                    default              => $log->subject_type,
                };
            @endphp
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 flex items-start gap-3">
                <div class="shrink-0 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $actionColor }}">
                        {{ $actionLabel }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-zinc-200 leading-snug">{{ $log->description }}</p>
                    <p class="text-xs text-zinc-500 mt-1">
                        {{ $subjectLabel }} &middot; {{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-10 text-center">
                <p class="text-sm text-zinc-500">Belum ada aktivitas tercatat.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="px-4 mt-4">
            {{ $logs->links('vendor.pagination.tailwind') }}
        </div>
    @endif

</x-layouts.app>
