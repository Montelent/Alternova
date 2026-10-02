<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{{-- TinyMCE always available in admin (avoids race on Livewire SPA navigations) --}}
<script>
(function () {
    if (window.__alternovaTinymceLoading || window.tinymce) return;
    window.__alternovaTinymceLoading = true;
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js';
    s.referrerPolicy = 'origin';
    s.onload = function () { window.__alternovaTinymceLoading = false; };
    s.onerror = function () { window.__alternovaTinymceLoading = false; };
    document.head.appendChild(s);
})();
</script>
<style>
/* Alternova admin — mobile-first polish */
@media (max-width: 768px) {
    .fi-sidebar-nav-item-button,
    .fi-topbar-item-button,
    .fi-btn {
        min-height: 2.75rem;
    }
    .fi-ta-actions,
    .fi-ac-actions {
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .fi-ta-content {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .fi-header-heading {
        font-size: 1.25rem !important;
        line-height: 1.35 !important;
    }
    .fi-page > section {
        padding-inline: 0.5rem;
    }
    .filepond--drop-label {
        min-height: 5.5rem;
    }
    .fi-fo-component-ctn {
        max-width: 100%;
    }
}
.filepond--root { font-size: 0.875rem; }
.filepond--panel-root { border-radius: 0.75rem; }
img.fi-fo-file-upload-image-preview,
.filepond--image-preview img {
    max-width: 100%;
    height: auto;
}
/* TinyMCE on narrow screens */
.tox-tinymce { max-width: 100% !important; }
.tox-editor-header { flex-wrap: wrap; }
</style>
