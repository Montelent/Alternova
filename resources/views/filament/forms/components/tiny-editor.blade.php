@php
    $id = $getId();
    $statePath = $getStatePath();
    $height = (int) $getHeight();
    $initial = $getState();
    if (is_array($initial)) {
        $initial = '';
    }
    $initial = (string) ($initial ?? '');
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:ignore
        x-data="tinyEditorComponent({
            statePath: @js($statePath),
            height: {{ $height }},
            editorId: @js($id),
            initial: @js($initial),
        })"
        x-init="init()"
        class="w-full"
    >
        <textarea
            x-ref="editor"
            id="{{ $id }}"
            class="w-full min-h-[12rem] rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 p-3"
        >{!! e($initial) !!}</textarea>
    </div>

    @once
        <script>
            window.tinyEditorComponent = function (config) {
                return {
                    state: null,
                    editor: null,
                    booting: false,
                    config,

                    init() {
                        // Entangle after Alpine is ready so Livewire state is available
                        this.state = this.$wire.$entangle(config.statePath);

                        // Prefer Livewire state; fall back to server-rendered HTML
                        if ((! this.state || this.state === '') && config.initial) {
                            this.state = config.initial;
                        }

                        this.$nextTick(() => this.boot());

                        this._onNavigated = () => {
                            setTimeout(() => this.boot(), 50);
                        };
                        document.addEventListener('livewire:navigated', this._onNavigated);

                        this.$watch('state', (value) => {
                            if (! this.editor || this.editor.initialized === false) return;
                            const html = value || '';
                            try {
                                if (this.editor.getContent() !== html) {
                                    this.editor.setContent(html);
                                }
                            } catch (e) {}
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
                        this.editor = null;
                    },

                    ensureScript() {
                        return new Promise((resolve) => {
                            if (window.tinymce) {
                                resolve(true);
                                return;
                            }
                            if (! window.__alternovaTinymceLoading) {
                                window.__alternovaTinymceLoading = true;
                                const s = document.createElement('script');
                                s.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js';
                                s.referrerPolicy = 'origin';
                                s.onload = () => {
                                    window.__alternovaTinymceLoading = false;
                                    resolve(true);
                                };
                                s.onerror = () => {
                                    window.__alternovaTinymceLoading = false;
                                    resolve(false);
                                };
                                document.head.appendChild(s);
                            }
                            let tries = 0;
                            const tick = () => {
                                if (window.tinymce) {
                                    resolve(true);
                                    return;
                                }
                                tries++;
                                if (tries > 100) {
                                    resolve(false);
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

                        try {
                            const ok = await this.ensureScript();
                            if (! ok || ! window.tinymce) {
                                this.booting = false;
                                console.warn('TinyMCE script failed to load');
                                return;
                            }

                            const el = this.$refs.editor;
                            if (! el) {
                                this.booting = false;
                                return;
                            }

                            this.teardown();

                            if (! el.id) {
                                el.id = 'tinymce-' + Math.random().toString(36).slice(2, 10);
                            }

                            const self = this;
                            const content = (typeof this.state === 'string' ? this.state : '') || config.initial || el.value || '';

                            window.tinymce.init({
                                target: el,
                                height: config.height || 320,
                                menubar: true,
                                branding: false,
                                promotion: false,
                                // Required for TinyMCE 7 open-source build
                                license_key: 'gpl',
                                // Critical: load skins/plugins from CDN, not relative site path
                                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1',
                                suffix: '.min',
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
                                        editor.setContent(content);
                                        self.booting = false;
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

                            setTimeout(() => { this.booting = false; }, 3000);
                        } catch (e) {
                            this.booting = false;
                            console.warn('TinyMCE boot failed', e);
                        }
                    },
                };
            };
        </script>
    @endonce
</x-dynamic-component>
