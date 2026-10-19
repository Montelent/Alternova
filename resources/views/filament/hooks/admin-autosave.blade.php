{{-- Offline-first drafts for Filament create/edit (localStorage + rare server sync) --}}
<script>
(function () {
    if (window.__alternovaAutosave) return;

    var state = {
        cfg: null,
        localTimer: null,
        serverTimer: null,
        lastLocalJson: '',
        lastServerJson: '',
        lastServerAt: 0,
        statusEl: null,
        online: typeof navigator !== 'undefined' ? navigator.onLine : true,
        _restoredKeys: {}
    };

    function findWire() {
        try {
            var el = document.querySelector('.fi-resource-create-record-page [wire\\:id], .fi-resource-edit-record-page [wire\\:id], .fi-page [wire\\:id]');
            if (!el) return null;
            var id = el.getAttribute('wire:id');
            return id && window.Livewire ? window.Livewire.find(id) : null;
        } catch (e) {
            return null;
        }
    }

    function getFormData(wire) {
        if (!wire) return null;
        try {
            var data = wire.get('data');
            if (data && typeof data === 'object') return JSON.parse(JSON.stringify(data));
        } catch (e) {}
        return null;
    }

    function setStatus(msg, kind) {
        if (!state.statusEl) {
            var host = document.querySelector('.fi-header-heading, .fi-page-header, h1.fi-header-heading');
            if (!host) return;
            state.statusEl = document.createElement('div');
            state.statusEl.id = 'alternova-autosave-status';
            state.statusEl.setAttribute('aria-live', 'polite');
            state.statusEl.style.cssText = 'font-size:12px;font-weight:500;margin-top:6px;opacity:.85;';
            host.parentNode && host.parentNode.insertBefore(state.statusEl, host.nextSibling);
        }
        if (!state.statusEl) return;
        state.statusEl.textContent = msg || '';
        state.statusEl.style.color = kind === 'error' ? '#dc2626' : (kind === 'ok' ? '#059669' : '#64748b');
    }

    function saveLocal(data) {
        if (!state.cfg || !state.cfg.key || !data) return;
        try {
            var payload = { t: Date.now(), resource: state.cfg.resource, recordId: state.cfg.recordId, data: data };
            var json = JSON.stringify(payload);
            if (json === state.lastLocalJson) return;
            if (json.length > 1500000) {
                setStatus('Draft too large for offline cache', 'error');
                return;
            }
            localStorage.setItem(state.cfg.key, json);
            state.lastLocalJson = json;
            setStatus('Draft saved offline · ' + new Date().toLocaleTimeString(), 'ok');
        } catch (e) {
            setStatus('Could not save offline draft', 'error');
        }
    }

    function readLocal() {
        if (!state.cfg || !state.cfg.key) return null;
        try {
            var raw = localStorage.getItem(state.cfg.key);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function clearLocal() {
        if (!state.cfg || !state.cfg.key) return;
        try { localStorage.removeItem(state.cfg.key); } catch (e) {}
        state.lastLocalJson = '';
        setStatus('', null);
    }

    function syncServer(data, force) {
        if (!state.cfg || state.cfg.demo) return;
        if (!state.online && !force) return;
        var wire = findWire();
        if (!wire || !data) return;
        var json = JSON.stringify(data);
        var now = Date.now();
        if (!force && json === state.lastServerJson) return;
        if (!force && (now - state.lastServerAt) < (state.cfg.serverMs || 60000)) return;
        state.lastServerAt = now;
        state.lastServerJson = json;
        try {
            wire.call('persistAutoSaveDraft', data);
            setStatus('Draft synced · ' + new Date().toLocaleTimeString(), 'ok');
        } catch (e) {
            setStatus('Offline — draft kept on this device', 'error');
        }
    }

    function tickLocal() {
        var data = getFormData(findWire());
        if (data) saveLocal(data);
    }

    function tickServer() {
        if (!state.online) return;
        var data = getFormData(findWire());
        if (data) syncServer(data, false);
    }

    function schedule() {
        clearInterval(state.localTimer);
        clearInterval(state.serverTimer);
        if (!state.cfg || !state.cfg.enabled) return;
        state.localTimer = setInterval(tickLocal, state.cfg.localMs || 2500);
        state.serverTimer = setInterval(tickServer, state.cfg.serverMs || 60000);
    }

    function offerRestore() {
        var draft = readLocal();
        if (!draft || !draft.data) return;
        if (draft.t && (Date.now() - draft.t) > 7 * 24 * 3600 * 1000) {
            clearLocal();
            return;
        }
        var wire = findWire();
        if (!wire) return;
        var when = draft.t ? new Date(draft.t).toLocaleString() : 'earlier';
        try {
            wire.call('restoreAutoSaveDraft', draft.data);
            setStatus('Restored offline draft from ' + when, 'ok');
        } catch (e) {}
    }

    function onFormActivity() {
        clearTimeout(state._debounce);
        state._debounce = setTimeout(tickLocal, 800);
    }

    function bindActivity() {
        document.addEventListener('input', function (e) {
            if (!state.cfg) return;
            if (e.target && e.target.closest && e.target.closest('.fi-fo-field-wrp, .fi-form, form')) {
                onFormActivity();
            }
        }, true);
        document.addEventListener('change', function (e) {
            if (!state.cfg) return;
            if (e.target && e.target.closest && e.target.closest('.fi-fo-field-wrp, .fi-form, form')) {
                onFormActivity();
            }
        }, true);
        window.addEventListener('online', function () {
            state.online = true;
            setStatus('Back online — syncing draft…', 'ok');
            tickLocal();
            syncServer(getFormData(findWire()), true);
        });
        window.addEventListener('offline', function () {
            state.online = false;
            setStatus('Offline — drafts stay on this device', 'error');
            tickLocal();
        });
        window.addEventListener('beforeunload', function () { tickLocal(); });
    }

    window.__alternovaAutosave = {
        init: function (cfg) {
            if (!cfg || !cfg.enabled) return;
            var page = document.querySelector('.fi-resource-create-record-page, .fi-resource-edit-record-page');
            if (!page) return;
            var same = state.cfg && state.cfg.key === cfg.key;
            state.cfg = cfg;
            if (!same) state.statusEl = null;
            schedule();
            if (!same && !state._restoredKeys[cfg.key]) {
                state._restoredKeys[cfg.key] = true;
                setTimeout(offerRestore, 700);
            }
            if (!same) setStatus(cfg.demo ? 'Demo mode — offline drafts only' : 'Auto-save on', null);
        },
        clear: function () {
            clearLocal();
            state.lastServerJson = '';
        }
    };

    bindActivity();

    document.addEventListener('livewire:init', function () {
        Livewire.on('autosave-clear', function () {
            window.__alternovaAutosave.clear();
        });
    });
    document.addEventListener('livewire:navigated', function () {
        state.statusEl = null;
    });
})();
</script>
