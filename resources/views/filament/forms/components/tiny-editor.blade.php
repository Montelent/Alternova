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
        <style>
            /* Sticky TinyMCE toolbar over Filament forms */
            .tox-tinymce .tox-editor-header {
                z-index: 30 !important;
            }
            .tox-tinymce-aux {
                z-index: 50 !important;
            }
            /* Ensure editor body is selectable / pasteable on mobile */
            .tox-edit-area__iframe {
                -webkit-user-select: text !important;
                user-select: text !important;
            }
        </style>
    @endonce

    @once
        <script>
            (function () {
                /** Flush every TinyMCE instance into its Alpine/Livewire state before any request. */
                function flushAllTinyEditors() {
                    if (! window.tinymce) {
                        return;
                    }
                    try {
                        window.tinymce.triggerSave();
                    } catch (e) {}

                    window.tinymce.get().forEach(function (editor) {
                        try {
                            if (typeof editor.__alternovaSync === 'function') {
                                editor.__alternovaSync(true);
                            }
                        } catch (e) {}
                    });
                }

                document.addEventListener('livewire:init', function () {
                    if (window.__alternovaTinyFlushHooked) {
                        return;
                    }
                    window.__alternovaTinyFlushHooked = true;

                    Livewire.hook('commit', function ({ component, commit, respond, succeed, fail }) {
                        flushAllTinyEditors();
                    });
                });

                document.addEventListener('click', function (e) {
                    var btn = e.target && e.target.closest
                        ? e.target.closest('button, [type="submit"], .fi-btn')
                        : null;
                    if (! btn) {
                        return;
                    }
                    var label = (btn.textContent || '').toLowerCase();
                    var isSave = btn.type === 'submit'
                        || label.indexOf('save') !== -1
                        || label.indexOf('create') !== -1
                        || label.indexOf('publish') !== -1
                        || btn.classList.contains('fi-btn-color-primary');
                    if (isSave) {
                        flushAllTinyEditors();
                    }
                }, true);

                window.tinyEditorComponent = function (config) {
                    return {
                        state: null,
                        editor: null,
                        booting: false,
                        config,
                        _syncing: false,

                        init() {
                            this.state = this.$wire.$entangle(config.statePath);

                            if ((! this.state || this.state === '') && config.initial) {
                                this.state = config.initial;
                            }

                            this.$nextTick(() => this.boot());

                            this._onNavigated = () => {
                                setTimeout(() => this.boot(), 50);
                            };
                            document.addEventListener('livewire:navigated', this._onNavigated);

                            this.$watch('state', (value) => {
                                if (this._syncing || ! this.editor) {
                                    return;
                                }
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
                                    license_key: 'gpl',
                                    base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1',
                                    suffix: '.min',
                                    plugins: 'lists link image code table autoresize wordcount',
                                    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code removeformat',
                                    // Keep toolbar visible while scrolling
                                    toolbar_sticky: true,
                                    toolbar_sticky_offset: 64,
                                    toolbar_mode: 'wrap',
                                    // Native OS context menu so mobile long-press shows Copy/Paste/Cut
                                    contextmenu: false,
                                    contextmenu_never_use_native: false,
                                    browser_spellcheck: true,
                                    paste_data_images: true,
                                    mobile: {
                                        menubar: true,
                                        toolbar_mode: 'wrap',
                                        toolbar_sticky: true,
                                    },
                                    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.6; -webkit-user-select: text; user-select: text; }',
                                    relative_urls: false,
                                    convert_urls: false,
                                    setup: (editor) => {
                                        self.editor = editor;

                                        const sync = (forceWire) => {
                                            try {
                                                const html = editor.getContent();
                                                self._syncing = true;
                                                self.state = html;
                                                el.value = html;
                                                if (forceWire && self.$wire && config.statePath) {
                                                    self.$wire.set(config.statePath, html, false);
                                                }
                                            } catch (e) {
                                            } finally {
                                                self._syncing = false;
                                            }
                                        };

                                        editor.__alternovaSync = sync;

                                        editor.on('init', () => {
                                            editor.setContent(content);
                                            self.booting = false;
                                        });

                                        editor.on('change keyup SetContent Undo Redo', () => sync(false));
                                        editor.on('blur', () => sync(true));
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
            })();
        </script>
    @endonce
</x-dynamic-component>
