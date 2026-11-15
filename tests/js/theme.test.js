import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import test from 'node:test';
import vm from 'node:vm';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const layout = readFileSync(path.join(root, 'resources/views/layouts/app.blade.php'), 'utf8');
const match = layout.match(/<script>\s*(function themeApp\(\) \{[\s\S]*?\n\s*\})\s*<\/script>\s*@livewireScripts/);

assert.ok(match, 'themeApp must be defined in a classic script before Livewire scripts');

function createTheme({ stored = null, systemDark = false, storageThrows = false } = {}) {
    let saved = stored;
    const classes = new Set();
    const storage = {
        getItem() {
            if (storageThrows) throw new Error('storage unavailable');
            return saved;
        },
        setItem(_key, value) {
            if (storageThrows) throw new Error('storage unavailable');
            saved = value;
        },
    };
    const sandbox = {
        localStorage: storage,
        window: {
            matchMedia: () => ({ matches: systemDark }),
        },
        document: {
            documentElement: {
                classList: {
                    toggle(name, force) {
                        if (force) classes.add(name);
                        else classes.delete(name);
                    },
                },
            },
        },
    };

    vm.runInNewContext(match[1], sandbox);

    return {
        app: sandbox.themeApp(),
        saved: () => saved,
        hasDarkClass: () => classes.has('dark'),
    };
}

test('saved theme preference overrides the operating-system preference', () => {
    const dark = createTheme({ stored: 'dark', systemDark: false });
    dark.app.init();
    assert.equal(dark.app.dark, true);
    assert.equal(dark.hasDarkClass(), true);

    const light = createTheme({ stored: 'light', systemDark: true });
    light.app.init();
    assert.equal(light.app.dark, false);
    assert.equal(light.hasDarkClass(), false);
});

test('system dark preference is used when no saved preference exists', () => {
    const theme = createTheme({ stored: null, systemDark: true });
    theme.app.init();
    assert.equal(theme.app.dark, true);
    assert.equal(theme.hasDarkClass(), true);
});

test('toggle persists the selected theme and synchronizes the document class', () => {
    const theme = createTheme({ stored: 'light' });
    theme.app.init();

    theme.app.toggle();
    assert.equal(theme.app.dark, true);
    assert.equal(theme.saved(), 'dark');
    assert.equal(theme.hasDarkClass(), true);

    theme.app.toggle();
    assert.equal(theme.app.dark, false);
    assert.equal(theme.saved(), 'light');
    assert.equal(theme.hasDarkClass(), false);
});

test('theme remains usable when browser storage is unavailable', () => {
    const theme = createTheme({ storageThrows: true, systemDark: true });
    theme.app.init();
    assert.equal(theme.app.dark, false);

    theme.app.toggle();
    assert.equal(theme.app.dark, true);
    assert.equal(theme.hasDarkClass(), true);
});
