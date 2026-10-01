@php
    $id = $getId();
    $statePath = $getStatePath();
    $height = $getHeight();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:ignore
        x-data="{
            state: $wire.$entangle('{{ $statePath }}'),
            editor: null,
            init() {
                const load = () => {
                    if (! window.tinymce) {
                        setTimeout(load, 50);
                        return;
                    }
                    const el = this.$refs.editor;
                    if (! el || el.dataset.tinymceReady) return;
                    el.dataset.tinymceReady = '1';
                    window.tinymce.init({
                        target: el,
                        height: {{ (int) $height }},
                        menubar: true,
                        branding: false,
                        promotion: false,
                        plugins: 'lists link image code table autoresize wordcount',
                        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code removeformat',
                        toolbar_mode: 'sliding',
                        mobile: {
                            menubar: true,
                            toolbar_mode: 'scrolling',
                        },
                        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.6; }',
                        relative_urls: false,
                        convert_urls: false,
                        setup: (editor) => {
                            this.editor = editor;
                            editor.on('init', () => {
                                editor.setContent(this.state || '');
                            });
                            const sync = () => { this.state = editor.getContent(); };
                            editor.on('change keyup blur SetContent', sync);
                        },
                    });
                };
                load();
            },
            destroy() {
                if (this.editor) {
                    try { window.tinymce.remove(this.editor); } catch (e) {}
                }
            }
        }"
        x-init="init()"
        class="w-full"
    >
        <textarea
            x-ref="editor"
            id="{{ $id }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 text-sm"
        >{{ $getState() }}</textarea>
    </div>

    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js" referrerpolicy="origin"></script>
        @endpush
        {{-- Filament may not stack scripts; load inline once --}}
        <script>
            (function () {
                if (window.__alternovaTinymceLoaded) return;
                window.__alternovaTinymceLoaded = true;
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js';
                s.referrerPolicy = 'origin';
                document.head.appendChild(s);
            })();
        </script>
    @endonce
</x-dynamic-component>
