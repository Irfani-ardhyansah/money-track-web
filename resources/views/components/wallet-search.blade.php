@props([
    'name',
    'initialId'      => null,
    'initialLabel'   => '',
    'initialBalance' => null,
    'color'          => 'emerald',
    'placeholder'    => 'Cari dompet...',
])

@php
$wrapperId = 'wrapper_' . $name;
$badgeId   = $name . '_balance_badge';
$valueId   = $name . '_balance_value';
$ringClass = $color === 'blue' ? 'focus:ring-blue-500' : 'focus:ring-emerald-500';
@endphp

<div class="relative" id="{{ $wrapperId }}">
    <input type="text"
           id="search_{{ $name }}"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           value="{{ $initialLabel }}"
           class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 text-base rounded-xl px-4 py-3 focus:ring-2 {{ $ringClass }} focus:border-transparent outline-none placeholder:text-zinc-600">
    <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ $initialId }}">

    <div id="dropdown_{{ $name }}"
         class="hidden absolute z-50 w-full mt-1 bg-zinc-800 border border-zinc-700 rounded-xl overflow-hidden shadow-xl max-h-56 overflow-y-auto">
    </div>
</div>

<div id="{{ $badgeId }}"
     class="{{ $initialId ? '' : 'hidden' }} mt-1.5 flex items-center justify-between px-3 py-2 rounded-xl bg-zinc-800/60 border border-zinc-700">
    <span class="text-xs text-zinc-500">Saldo saat ini</span>
    <span id="{{ $valueId }}"
          class="text-xs font-semibold {{ ($initialBalance ?? 0) < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
        @if($initialId && $initialBalance !== null)
            {{ $initialBalance < 0 ? '-' : '' }}Rp {{ number_format(abs($initialBalance), 0, ',', '.') }}
        @endif
    </span>
</div>

@once
<script>
function _wsEscape(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function initWalletSearch(cfg) {
    const input    = document.getElementById(cfg.inputId);
    const hidden   = document.getElementById(cfg.hiddenId);
    const dropdown = document.getElementById(cfg.dropdownId);
    const badge    = document.getElementById(cfg.badgeId);
    const valueEl  = document.getElementById(cfg.valueId);
    const wrapper  = document.getElementById(cfg.wrapperId);
    let timer;

    function showBadge(balance) {
        if (balance === null || balance === undefined || balance === '') {
            badge.classList.add('hidden');
            return;
        }
        badge.classList.remove('hidden');
        const b = parseFloat(balance);
        valueEl.textContent = (b < 0 ? '-' : '') + 'Rp\u00A0' + Math.abs(b).toLocaleString('id-ID');
        valueEl.className = 'text-xs font-semibold ' + (b < 0 ? 'text-rose-400' : 'text-emerald-400');
    }

    function pick(item) {
        input.value  = item.label;
        hidden.value = item.id;
        showBadge(item.balance);
        dropdown.classList.add('hidden');
    }

    function render(items) {
        if (!items.length) {
            dropdown.innerHTML = '<p class="px-4 py-3 text-sm text-zinc-500">Tidak ada dompet ditemukan</p>';
        } else {
            dropdown.innerHTML = items.map(function(w) {
                return '<div class="wallet-item px-4 py-2.5 hover:bg-zinc-700 cursor-pointer transition-colors border-b border-zinc-700/50 last:border-0"'
                    + ' data-id="' + w.id + '"'
                    + ' data-label="' + _wsEscape(w.label) + '"'
                    + ' data-balance="' + w.balance + '">'
                    + '<span class="text-sm text-zinc-100">' + _wsEscape(w.label) + '</span>'
                    + '</div>';
            }).join('');

            dropdown.querySelectorAll('.wallet-item').forEach(function(el) {
                el.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    pick({ id: el.dataset.id, label: el.dataset.label, balance: el.dataset.balance });
                });
            });
        }
        dropdown.classList.remove('hidden');
    }

    function doSearch(q) {
        fetch(cfg.searchUrl + '?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(render);
    }

    input.addEventListener('input', function() {
        hidden.value = '';
        showBadge(null);
        clearTimeout(timer);
        const q = this.value.trim();
        timer = setTimeout(function() { doSearch(q); }, 200);
    });

    input.addEventListener('focus', function() {
        doSearch(this.value.trim());
    });

    input.addEventListener('blur', function() {
        const self = this;
        setTimeout(function() {
            dropdown.classList.add('hidden');
            if (!hidden.value && self.value.trim()) {
                self.value = '';
            }
        }, 160);
    });

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) dropdown.classList.add('hidden');
    });
}
</script>
@endonce

<script>
initWalletSearch({
    wrapperId:  '{{ $wrapperId }}',
    inputId:    'search_{{ $name }}',
    hiddenId:   '{{ $name }}',
    dropdownId: 'dropdown_{{ $name }}',
    badgeId:    '{{ $badgeId }}',
    valueId:    '{{ $valueId }}',
    searchUrl:  '{{ route('wallets.search') }}',
});
</script>
