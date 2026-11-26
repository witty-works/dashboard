// Checks the built analytics bundle (run after `npm run build`). Vite can
// resolve 'moment' to a second copy, and the locales then land on the copy
// the page does not use; only the built output shows that.
import {readFileSync} from 'node:fs';
import {fileURLToPath, pathToFileURL} from 'node:url';
import {Window} from 'happy-dom';

const root = fileURLToPath(new URL('../../public/build/', import.meta.url));
const manifest = JSON.parse(readFileSync(root + 'manifest.json', 'utf8'));
const file = root + manifest['resources/js/analytics.js'].file;

const window = new Window();
window.document.documentElement.lang = 'de';
globalThis.window = window;
globalThis.document = window.document;
await import(pathToFileURL(file).href);

const failures = [];
const expect = (what, actual, expected) => {
    if (actual !== expected) failures.push(`${what}: expected ${expected}, got ${actual}`);
};
expect('Chart', typeof window.Chart, 'function');
expect('data labels plugin', window.Chart.plugins.getAll().some((p) => p.id === 'datalabels'), true);
expect('moment locale', window.moment.locale(), 'de');
expect('German month', window.moment('2026-01-15').format('MMMM'), 'Januar');
expect('French month', window.moment('2026-01-15').locale('fr').format('MMMM'), 'janvier');
expect('copies of moment', (readFileSync(file, 'utf8').match(/\.version=`\d+\.\d+\.\d+`/g) || []).length, 1);

if (failures.length) {
    console.error('analytics bundle:\n  ' + failures.join('\n  '));
    process.exit(1);
}
console.log('analytics bundle: ok');
