/**
 * Starts the site for the browser tests (playwright.config.ts runs it):
 * PHP's development server on its own port, with its own SQLite database,
 * storage folder and caches in the system's temporary folder, filled with
 * the local demo content and accounts (DevelopmentSeeder). The development
 * database, storage and caches are neither read nor changed, and no mail
 * is sent.
 *
 * The front end must be built first (`npm run build`), or Vite's
 * development server running (`npm run dev`).
 *
 *   BROWSER_TESTS_PORT  port of the server (8123)
 *   PHP_BINARY          PHP executable (php)
 */
import { spawn, spawnSync } from 'node:child_process';
import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../..', import.meta.url));
const port = process.env.BROWSER_TESTS_PORT ?? '8123';
const php = process.env.PHP_BINARY ?? 'php';
const windows = process.platform === 'win32';
const workspace = path.join(os.tmpdir(), `wasmsoft-browser-tests-${port}`);
const storage = path.join(workspace, 'storage');

if (!fs.existsSync(path.join(root, 'public/build/manifest.json')) && !fs.existsSync(path.join(root, 'public/hot'))) {
    console.error('The front end is not built: run `npm run build` (or keep `npm run dev` running) first.');
    process.exit(1);
}

// A new workspace on every run; nothing but this script writes to it.
fs.rmSync(workspace, { recursive: true, force: true });

for (const folder of ['app/public', 'app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs']) {
    fs.mkdirSync(path.join(storage, folder), { recursive: true });
}

const database = path.join(workspace, 'database.sqlite');
fs.writeFileSync(database, '');

/** Environment of every PHP process; it takes precedence over the project's .env file. */
const env = {
    ...process.env,
    APP_ENV: 'local',
    APP_DEBUG: 'false',
    APP_KEY: `base64:${crypto.randomBytes(32).toString('base64')}`,
    APP_URL: `http://127.0.0.1:${port}`,
    // A cached configuration, route list or event list of the development install is ignored.
    APP_CONFIG_CACHE: path.join(workspace, 'config.php'),
    APP_ROUTES_CACHE: path.join(workspace, 'routes.php'),
    APP_EVENTS_CACHE: path.join(workspace, 'events.php'),
    LARAVEL_STORAGE_PATH: storage,
    DB_CONNECTION: 'sqlite',
    DB_URL: '',
    DB_DATABASE: database,
    CACHE_STORE: 'file',
    SESSION_DRIVER: 'file',
    QUEUE_CONNECTION: 'sync',
    BROADCAST_CONNECTION: 'null',
    MAIL_MAILER: 'array',
    LOG_CHANNEL: 'single',
    MEDIA_DISK: 'public',
    INERTIA_SSR_ENABLED: 'false',
    // The contact form test sends messages; the hourly limit has its own PHP tests.
    CONTACT_MAX_PER_HOUR: '1000',
    // Password of the demo accounts (one per role) the dashboard tests sign in with.
    DEV_SEED_PASSWORD: 'Wasmsoft-Dev-2026',
    PULSE_ENABLED: 'false',
    TELESCOPE_ENABLED: 'false',
    NIGHTWATCH_ENABLED: 'false',
};

// PHP reads the environment into $_ENV only with "E" in variables_order (php.ini often leaves it out).
const phpOptions = ['-d', 'variables_order=EGPCS'];

// On Windows PHP is started through the command interpreter (it may be a .bat shim), which needs paths with spaces quoted.
const quoted = (value) => (windows && /[\s&()^]/.test(value) ? `"${value}"` : value);
const executable = quoted(php);
const phpArguments = (...values) => [...phpOptions, ...values].map(quoted);

const migration = spawnSync(executable, phpArguments('artisan', 'migrate', '--seed', '--force', '--no-interaction'), {
    cwd: root,
    env,
    stdio: 'inherit',
    shell: windows,
});

if (migration.status !== 0) {
    console.error(`Preparing the database failed; see ${path.join(storage, 'logs')}.`);
    process.exit(migration.status ?? 1);
}

const server = spawn(executable, phpArguments('-S', `127.0.0.1:${port}`, path.join(root, 'tests/Browser/router.php')), {
    cwd: path.join(root, 'public'),
    // Several PHP workers answer requests in parallel (not available on Windows).
    env: windows ? env : { ...env, PHP_CLI_SERVER_WORKERS: '4' },
    stdio: ['ignore', 'ignore', 'pipe'],
    shell: windows,
});

// The server's own messages are passed on; its log of every request is not.
const requestLog = /^(\[\d+\] )?\[[^\]]+\] \S+:\d+ (Accepted|Closing|\[\d{3}\]: )|Development Server \(.*\) started/;
let pending = '';

server.stderr.on('data', (chunk) => {
    const lines = (pending + chunk.toString()).split(/\r?\n/);
    pending = lines.pop() ?? '';

    for (const line of lines) {
        if (line.trim() !== '' && !requestLog.test(line)) {
            process.stderr.write(`${line}\n`);
        }
    }
});

console.log(`Browser tests: site at http://127.0.0.1:${port}, its log in ${path.join(storage, 'logs')}`);

server.on('exit', (code) => process.exit(code ?? 0));

for (const signal of ['SIGINT', 'SIGTERM']) {
    process.on(signal, () => server.kill(signal));
}
