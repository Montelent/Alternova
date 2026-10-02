<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<script>
(function () {
    if (window.tinymce || window.__alternovaTinymceLoading) return;
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
/* ==========================================================================
   Alternova Admin — responsive navigation & layout (all devices / browsers)
   ========================================================================== */

html {
    -webkit-text-size-adjust: 100%;
    text-size-adjust: 100%;
}

/* Safe areas (notch / home indicator) */
.fi-body,
.fi-layout,
.fi-sidebar,
.fi-topbar,
.fi-main {
    padding-left: env(safe-area-inset-left, 0);
    padding-right: env(safe-area-inset-right, 0);
}
.fi-topbar {
    padding-top: env(safe-area-inset-top, 0);
}
.fi-sidebar-nav {
    padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0));
}

/* Prevent horizontal page scroll from wide tables / long nav labels */
.fi-body,
.fi-main,
.fi-main-ctn,
.fi-page {
    max-width: 100vw;
    overflow-x: clip;
}

/* ---------- Top bar ---------- */
.fi-topbar {
    min-height: 3.5rem;
}
.fi-topbar .fi-topbar-open-sidebar-btn,
.fi-topbar .fi-topbar-close-sidebar-btn,
.fi-topbar .fi-icon-btn {
    min-width: 2.75rem;
    min-height: 2.75rem;
}
.fi-topbar-start,
.fi-topbar-end {
    gap: 0.25rem;
}

/* Brand: truncate on narrow screens */
.fi-topbar .fi-logo,
.fi-sidebar-header .fi-logo,
.fi-sidebar-header a {
    max-width: min(100%, 11rem);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ---------- Sidebar (desktop + mobile drawer) ---------- */
.fi-sidebar {
    max-width: 100vw;
}
.fi-sidebar-nav {
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}
.fi-sidebar-nav-item-button,
.fi-sidebar-group-button,
.fi-sidebar-item-button {
    min-height: 2.5rem;
    border-radius: 0.5rem;
}
.fi-sidebar-nav-item-label,
.fi-sidebar-item-label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}
.fi-sidebar-group-label {
    letter-spacing: 0.02em;
}

/* Mobile / tablet: larger touch targets, full-height drawer feel */
@media (max-width: 1023px) {
    .fi-sidebar {
        width: min(20rem, 88vw) !important;
        max-width: 88vw;
    }
    .fi-sidebar-nav-item-button,
    .fi-sidebar-group-button,
    .fi-sidebar-item-button,
    .fi-topbar-item-button,
    .fi-btn {
        min-height: 2.85rem;
    }
    .fi-sidebar-nav {
        padding-inline: 0.65rem;
    }
    /* Darker overlay so drawer is obvious on light and dark */
    .fi-sidebar-close-overlay {
        background-color: rgb(15 23 42 / 0.55) !important;
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
    }
    .fi-header-heading {
        font-size: 1.2rem !important;
        line-height: 1.35 !important;
        word-break: break-word;
    }
    .fi-header-subheading {
        font-size: 0.8125rem !important;
    }
    .fi-page > section,
    .fi-page > div {
        padding-inline: max(0.5rem, env(safe-area-inset-left));
    }
    .fi-ta-actions,
    .fi-ac-actions,
    .fi-section-header-actions {
        flex-wrap: wrap !important;
        gap: 0.35rem !important;
    }
    .fi-ta-content,
    .fi-ta-table-ctn {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    /* Stack page header actions under title on phones */
    .fi-header {
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .fi-header-actions {
        width: 100%;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .fi-header-actions .fi-btn {
        flex: 1 1 auto;
        justify-content: center;
    }
}

/* Phones only */
@media (max-width: 640px) {
    .fi-sidebar {
        width: min(18.5rem, 92vw) !important;
        max-width: 92vw;
    }
    .fi-topbar .fi-global-search-field {
        max-width: 100%;
    }
    /* Hide less-critical topbar clutter if present */
    .fi-topbar .fi-global-search {
        max-width: 9rem;
    }
    .fi-section-content-ctn,
    .fi-fo-component-ctn {
        max-width: 100%;
    }
    .fi-fo-tabs-tab,
    .fi-tabs-item {
        min-height: 2.5rem;
        white-space: nowrap;
    }
    .fi-tabs,
    .fi-fo-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        flex-wrap: nowrap;
    }
}

/* Tablets in portrait */
@media (min-width: 641px) and (max-width: 1023px) {
    .fi-sidebar {
        width: min(19rem, 80vw) !important;
    }
}

/* Desktop: keep collapsed icon rail comfortable */
@media (min-width: 1024px) {
    .fi-sidebar-nav-item-button {
        min-height: 2.35rem;
    }
}

/* Very wide screens: content still readable */
@media (min-width: 1536px) {
    .fi-main-ctn {
        max-width: 100%;
    }
}

/* ---------- Forms / uploads / editor ---------- */
.filepond--root { font-size: 0.875rem; }
.filepond--panel-root { border-radius: 0.75rem; }
.filepond--drop-label { min-height: 5.5rem; }
img.fi-fo-file-upload-image-preview,
.filepond--image-preview img {
    max-width: 100%;
    height: auto;
}
.tox-tinymce { max-width: 100% !important; }
.tox-editor-header { flex-wrap: wrap; }

/* Widgets / stats grids collapse cleanly */
.fi-wi-stats-overview-stats-ctn,
.fi-wi-widget {
    max-width: 100%;
}

/* Reduce double-tap zoom delay on interactive controls (mobile browsers) */
.fi-sidebar-nav-item-button,
.fi-btn,
.fi-icon-btn,
.fi-topbar-open-sidebar-btn {
    touch-action: manipulation;
}
</style>
