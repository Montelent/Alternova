@php
    $get = $getStateUsing ?? null;
    // Filament ViewField exposes $get via closure in some versions; use Livewire form data when available.
    $name = '';
    $metaTitle = '';
    $metaDesc = '';
    $slug = '';
    try {
        if (isset($this) && method_exists($this, 'get')) {
            // not available
        }
    } catch (\Throwable) {}
@endphp

<div
    x-data="{
        title: '',
        desc: '',
        slug: '',
        name: '',
        init() {
            const root = this.$el.closest('form') || document;
            const read = () => {
                const t = root.querySelector('[id*=meta_title], input[wire\\:model*=meta_title], input[name*=meta_title]');
                const d = root.querySelector('textarea[wire\\:model*=meta_description], textarea[name*=meta_description]');
                const s = root.querySelector('input[wire\\:model$=.slug], input[wire\\:model*=\"slug\"]');
                const n = root.querySelector('input[wire\\:model$=.name], input[wire\\:model*=\"name\"]');
                this.title = (t && t.value) || (n && n.value) || 'SEO title preview';
                this.desc = (d && d.value) || 'Meta description will appear here. Aim for a clear benefit in 120–155 characters.';
                this.slug = (s && s.value) || 'your-slug';
                this.name = (n && n.value) || '';
                if (!t || !t.value) {
                    this.title = (this.name || 'Page name') + ' | Alternova';
                }
            };
            read();
            root.addEventListener('input', read);
            setInterval(read, 1200);
        }
    }"
    class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 space-y-1"
>
    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Google-style preview (approximate)</p>
    <p class="text-xl text-[#1a0dab] dark:text-sky-400 leading-snug truncate" x-text="title.substring(0, 70)"></p>
    <p class="text-sm text-[#006621] dark:text-emerald-400 truncate">
        <span x-text="window.location.origin + '/'"></span><span x-text="slug"></span>
    </p>
    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" x-text="desc.substring(0, 160)"></p>
</div>
