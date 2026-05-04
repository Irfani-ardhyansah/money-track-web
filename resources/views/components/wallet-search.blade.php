@props([
    'name',
    'initialId'      => null,
    'initialLabel'   => '',
    'initialBalance' => null,
    'color'          => 'emerald',
    'placeholder'    => 'Cari dompet...',
    'filterType'     => '',   // comma-separated types, e.g. "savings,investment"
    'ownerId'        => null, // optional: restrict to root wallet + children
])

@php
$wrapperId     = 'wrapper_' . $name;
$badgeId       = $name . '_balance_badge';
$valueId       = $name . '_balance_value';
$focusRingCls  = $color === 'blue'
    ? 'focus-within:ring-blue-500'
    : 'focus-within:ring-emerald-500';
@endphp

<div class="relative" id="{{ $wrapperId }}">
    {{-- Input row --}}
    <div class="flex items-center bg-zinc-800 border border-zinc-700 rounded-xl
                focus-within:ring-2 {{ $focusRingCls }} focus-within:border-transparent transition-shadow">
        <input type="text"
               id="search_{{ $name }}"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               value="{{ $initialLabel }}"
               class="flex-1 min-w-0 bg-transparent text-zinc-100 text-base px-4 py-3 outline-none placeholder:text-zinc-600">

        <button type="button"
                id="clear_{{ $name }}"
                tabindex="-1"
                class="{{ $initialId || $initialLabel ? '' : 'hidden' }} shrink-0 mr-2 w-5 h-5 rounded-full bg-zinc-600 hover:bg-zinc-500 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

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
    const clearBtn = document.getElementById(cfg.clearId);
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

    function syncClearBtn() {
        if (input.value.trim()) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    function clearAll() {
        input.value  = '';
        hidden.value = '';
        showBadge(null);
        clearBtn.classList.add('hidden');
        dropdown.classList.add('hidden');
    }

    function pick(item) {
        input.value  = item.label;
        hidden.value = item.id;
        showBadge(item.balance);
        dropdown.classList.add('hidden');
        clearBtn.classList.remove('hidden');
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
        let url = cfg.searchUrl + '?q=' + encodeURIComponent(q);
        if (cfg.filterType) url += '&filter_type=' + encodeURIComponent(cfg.filterType);
        if (cfg.ownerId) url += '&owner_id=' + encodeURIComponent(cfg.ownerId);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(render);
    }

    clearBtn.addEventListener('mousedown', function(e) {
        e.preventDefault();
        clearAll();
        input.focus();
    });

    input.addEventListener('input', function() {
        hidden.value = '';
        showBadge(null);
        syncClearBtn();
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
                clearBtn.classList.add('hidden');
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
    clearId:    'clear_{{ $name }}',
    searchUrl:  '{{ route('wallets.search') }}',
    filterType: '{{ $filterType }}',
    ownerId:    '{{ $ownerId ?? '' }}',
});
</script>
