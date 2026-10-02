@php
    $id = $getId();
    $statePath = $getStatePath();
    $height = (int) $getHeight();
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
            booting: false,
            bootAttempts: 0,
            init() {
                this.$nextTick(() => this.boot());

                // Soft navigations / form morphs
                this._onNavigated = () => this.boot();
                document.addEventListener('livewire:navigated', this._onNavigated);

                this.$watch('state', (value) => {
                    if (! this.editor) return;
                    const html = value || '';
                    if (this.editor.getContent() !== html) {
                        this.editor.setContent(html);
                    }
                });
            },
            destroy() {
                if (this._onNavigated) {
                    document.removeEventListener('livewire:navigated', this._onNavigated);
                }
                this.teardown();
            },
            teardown() {
                const el = this.$refs.editor;
                if (window.tinymce && el && el.id) {
                    const existing = window.tinymce.get(el.id);
                    if (existing) {
                        try { existing.remove(); } catch (e) {}
                    }
                }
                if (this.editor) {
                    try { this.editor.remove(); } catch (e) {}
                    this.editor = null;
                }
                if (el) {
                    delete el.dataset.tinymceReady;
                }
            },
            waitForTinymce() {
                return new Promise((resolve) => {
                    if (window.tinymce) {
                        resolve();
                        return;
                    }
                    let tries = 0;
                    const tick = () => {
                        if (window.tinymce) {
                            resolve();
                            return;
                        }
                        tries++;
                        if (tries > 80) {
                            // ~4s — inject script again as fallback
                            if (! window.__alternovaTinymceLoading) {
                                window.__alternovaTinymceLoading = true;
                                const s = document.createElement('script');
                                s.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js';
                                s.referrerPolicy = 'origin';
                                s.onload = () => { window.__alternovaTinymceLoading = false; resolve(); };
                                s.onerror = () => { window.__alternovaTinymceLoading = false; resolve(); };
                                document.head.appendChild(s);
                                return;
                            }
                            setTimeout(tick, 50);
                            return;
                        }
                        setTimeout(tick, 50);
                    };
                    tick();
                });
            },
            async boot() {
                if (this.booting) return;
                this.booting = true;
                this.bootAttempts++;

                try {
                    await this.waitForTinymce();
                    if (! window.tinymce) {
                        this.booting = false;
                        return;
                    }

                    const el = this.$refs.editor;
                    if (! el) {
                        this.booting = false;
                        return;
                    }

                    // Always remove prior instance before re-init (SPA / wire:ignore)
                    this.teardown();

                    // Ensure unique id for tinymce.get()
                    if (! el.id) {
                        el.id = 'tinymce-{{ $id }}-' + Math.random().toString(36).slice(2, 8);
                    }

                    el.dataset.tinymceReady = '1';

                    const self = this;
                    window.tinymce.init({
                        target: el,
                        height: {{ $height }},
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
                            self.editor = editor;
                            editor.on('init', () => {
                                editor.setContent(self.state || '');
                            });
                            const sync = () => {
                                self.state = editor.getContent();
                            };
                            editor.on('change keyup blur SetContent Undo Redo', sync);
                        },
                        init_instance_callback: () => {
                            self.booting = false;
                        },
                    });

                    // Safety: if init_instance never fires
                    setTimeout(() => { this.booting = false; }, 2000);
                } catch (e) {
                    this.booting = false;
                    console.warn('TinyMCE boot failed', e);
                }
            },
        }"
        class="w-full"
    >
        <textarea
            x-ref="editor"
            id="{{ $id }}"
            class="w-full min-h-[12rem] rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 p-3"
            placeholder="Loading editor…"
        ></textarea>
    </div>
</x-dynamic-component>
