@props([
    'initialId'    => null,
    'initialLabel' => '',
    'placeholder'  => 'Cari kategori...',
])

<div class="relative" id="wrapper_category_id">
    <div class="flex items-center bg-zinc-800 border border-zinc-700 rounded-xl
                focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-transparent transition-shadow">
        <input type="text"
               id="search_category_id"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               value="{{ $initialLabel }}"
               class="flex-1 min-w-0 bg-transparent text-zinc-100 text-base px-4 py-3 outline-none placeholder:text-zinc-600">

        <button type="button"
                id="clear_category_id"
                tabindex="-1"
                class="{{ $initialId || $initialLabel ? '' : 'hidden' }} shrink-0 mr-2 w-5 h-5 rounded-full bg-zinc-600 hover:bg-zinc-500 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <input type="hidden" name="category_id" id="category_id" value="{{ $initialId }}">

    <div id="dropdown_category_id"
         class="hidden absolute z-50 w-full mt-1 bg-zinc-800 border border-zinc-700 rounded-xl overflow-hidden shadow-xl max-h-56 overflow-y-auto">
    </div>
</div>

@once
<script>
function initCategorySearch(cfg) {
    const input    = document.getElementById(cfg.inputId);
    const hidden   = document.getElementById(cfg.hiddenId);
    const dropdown = document.getElementById(cfg.dropdownId);
    const wrapper  = document.getElementById(cfg.wrapperId);
    const clearBtn = document.getElementById(cfg.clearId);
    let timer;

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    const typeLabel = { income: 'Pemasukan', expense: 'Pengeluaran' };
    const typeCls   = { income: 'bg-emerald-900/50 text-emerald-400', expense: 'bg-rose-900/50 text-rose-400' };

    function syncClearBtn() {
        if (!clearBtn) return;
        if (input.value.trim() || hidden.value) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    function clearAll() {
        input.value = '';
        hidden.value = '';
        dropdown.classList.add('hidden');
        syncClearBtn();
        input.focus();
    }

    function pick(item) {
        input.value  = item.name;
        hidden.value = item.id;
        dropdown.classList.add('hidden');
        syncClearBtn();
    }

    function render(items) {
        if (!items.length) {
            dropdown.innerHTML = '<p class="px-4 py-3 text-sm text-zinc-500">Tidak ada kategori ditemukan</p>';
        } else {
            let html = '';
            let lastType = null;
            items.forEach(function(c) {
                if (c.type !== lastType) {
                    if (lastType !== null) {
                        html += '<div class="border-t border-zinc-700/60"></div>';
                    }
                    html += '<div class="px-4 pt-2.5 pb-1 text-[10px] font-semibold uppercase tracking-wider '
                          + (typeCls[c.type] || 'text-zinc-500') + '">'
                          + (typeLabel[c.type] || c.type) + '</div>';
                    lastType = c.type;
                }
                html += '<div class="cat-item px-4 py-2.5 hover:bg-zinc-700 cursor-pointer transition-colors border-b border-zinc-700/40 last:border-0"'
                      + ' data-id="' + c.id + '" data-name="' + esc(c.name) + '">'
                      + '<span class="text-sm text-zinc-100">' + esc(c.name) + '</span>'
                      + '</div>';
            });
            dropdown.innerHTML = html;

            dropdown.querySelectorAll('.cat-item').forEach(function(el) {
                el.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    pick({ id: el.dataset.id, name: el.dataset.name });
                });
            });
        }
        dropdown.classList.remove('hidden');
    }

    function activeType() {
        const radio = document.querySelector('input[name="type"]:checked');
        if (radio && (radio.value === 'income' || radio.value === 'expense')) {
            return radio.value;
        }
        const fi = document.getElementById('fi_type');
        if (fi && (fi.value === 'income' || fi.value === 'expense')) {
            return fi.value;
        }
        return '';
    }

    function doSearch(q) {
        const type = activeType();
        const url  = cfg.searchUrl + '?q=' + encodeURIComponent(q) + (type ? '&type=' + type : '');
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(render);
    }

    if (clearBtn) {
        clearBtn.addEventListener('mousedown', function(e) {
            e.preventDefault();
            clearAll();
        });
    }

    input.addEventListener('input', function() {
        hidden.value = '';
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
                syncClearBtn();
            }
        }, 160);
    });

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) dropdown.classList.add('hidden');
    });

    // Reset pilihan kategori saat jenis transaksi berubah (form tambah transaksi)
    document.querySelectorAll('input[name="type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            hidden.value = '';
            input.value  = '';
            syncClearBtn();
            dropdown.classList.add('hidden');
        });
    });
}
</script>
@endonce

<script>
initCategorySearch({
    wrapperId:  'wrapper_category_id',
    inputId:    'search_category_id',
    hiddenId:   'category_id',
    dropdownId: 'dropdown_category_id',
    clearId:    'clear_category_id',
    searchUrl:  '{{ route('categories.search') }}',
});
</script>
