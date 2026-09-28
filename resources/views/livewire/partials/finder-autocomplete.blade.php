{{-- Drop into finder search UI: Alpine-powered suggestions --}}
<div class="relative" x-data="finderSuggest()" x-init="init()">
    <input type="search"
        wire:model.live.debounce.300ms="search"
        @input.debounce.250ms="fetch($event.target.value)"
        @keydown.escape="open = false"
        @keydown.down.prevent="highlight(1)"
        @keydown.up.prevent="highlight(-1)"
        @keydown.enter.prevent="go()"
        placeholder="Search alternatives, languages…"
        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm"
        autocomplete="off">

    <div x-show="open && items.length" x-cloak
        class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg overflow-hidden">
        <template x-for="(item, i) in items" :key="item.url">
            <a :href="item.url"
                class="flex items-center justify-between gap-3 px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
                :class="i === active ? 'bg-slate-50 dark:bg-slate-800' : ''"
                @mouseenter="active = i">
                <span class="font-medium text-slate-900 dark:text-white" x-text="item.name"></span>
                <span class="text-xs text-slate-400" x-text="item.meta"></span>
            </a>
        </template>
    </div>
</div>
<script>
function finderSuggest() {
    return {
        items: [],
        open: false,
        active: 0,
        init() {},
        async fetch(q) {
            if (!q || q.length < 2) { this.items = []; this.open = false; return; }
            try {
                const res = await fetch('{{ url('/api/suggest') }}?q=' + encodeURIComponent(q));
                const json = await res.json();
                this.items = json.data || [];
                this.open = this.items.length > 0;
                this.active = 0;
            } catch (e) {
                this.items = [];
                this.open = false;
            }
        },
        highlight(delta) {
            if (!this.items.length) return;
            this.active = (this.active + delta + this.items.length) % this.items.length;
        },
        go() {
            if (this.items[this.active]) window.location = this.items[this.active].url;
        }
    }
}
</script>
