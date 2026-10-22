/* Screenshot dokumentasi — semua halaman utama sebagai JPG.
 * Jalankan saat `php artisan serve` aktif di BASE.
 * Usage: node scripts/docs-screenshots.cjs
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = process.env.DOCS_BASE || 'http://localhost:8000';
const OUT = path.join(__dirname, '..', 'public', 'images', 'docs');

const ADMIN = { email: 'admin@kos.test', password: 'password' };
const TENANT = { phone: '081234000001', password: 'password' };

const PUBLIC_PAGES = [
    { name: 'landing-home', url: '/' },
    { name: 'landing-property', url: '/property/kos-bahagia-pusat' },
    { name: 'portal-login', url: '/portal/login' },
    { name: 'blog', url: '/blog' },
];

const PORTAL_PAGES = [
    { name: 'portal-dashboard', url: '/portal/' },
    { name: 'portal-invoices', url: '/portal/invoices' },
    { name: 'portal-maintenance', url: '/portal/maintenance' },
    { name: 'portal-profile', url: '/portal/profile' },
];

const ADMIN_PAGES = [
    { name: 'admin-login', url: '/admin/login', noAuth: true },
    { name: 'admin-dashboard', url: '/admin' },
    { name: 'admin-properties', url: '/admin/properties' },
    { name: 'admin-rooms', url: '/admin/rooms' },
    { name: 'admin-occupants', url: '/admin/occupants' },
    { name: 'admin-leases', url: '/admin/leases' },
    { name: 'admin-invoices', url: '/admin/invoices' },
    { name: 'admin-financial', url: '/admin/financial-report' },
    { name: 'admin-maintenance', url: '/admin/maintenance-requests' },
    { name: 'admin-whatsapp', url: '/admin/whatsapp-blast' },
];

async function shot(page, name) {
    await page.waitForTimeout(2500);
    const file = path.join(OUT, `${name}.jpg`);
    await page.screenshot({ path: file, type: 'jpeg', quality: 75 });
    const kb = Math.round(fs.statSync(file).size / 1024);
    console.log(`OK ${name}.jpg (${kb} KB) — ${page.url()}`);
}

(async () => {
    if (!fs.existsSync(OUT)) fs.mkdirSync(OUT, { recursive: true });
    const browser = await chromium.launch({ headless: true });

    // ── Publik ──
    {
        const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
        const page = await ctx.newPage();
        for (const p of PUBLIC_PAGES) {
            try {
                await page.goto(BASE + p.url, { waitUntil: 'networkidle', timeout: 30000 });
                await shot(page, p.name);
            } catch (e) { console.log(`FAIL ${p.name}: ${e.message}`); }
        }
        await ctx.close();
    }

    // ── Portal penyewa ──
    {
        const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
        const page = await ctx.newPage();
        try {
            await page.goto(BASE + '/portal/login', { waitUntil: 'networkidle', timeout: 30000 });
            await page.fill('input[name="phone"]', TENANT.phone);
            await page.fill('input[name="password"]', TENANT.password);
            await Promise.all([
                page.waitForURL('**/portal**', { timeout: 15000 }).catch(() => {}),
                page.click('button[type="submit"]'),
            ]);
            await page.waitForTimeout(2000);
            console.log('Portal login →', page.url());
            for (const p of PORTAL_PAGES) {
                try {
                    await page.goto(BASE + p.url, { waitUntil: 'networkidle', timeout: 30000 });
                    await shot(page, p.name);
                } catch (e) { console.log(`FAIL ${p.name}: ${e.message}`); }
            }
        } catch (e) { console.log('Portal block FAIL:', e.message); }
        await ctx.close();
    }

    // ── Admin ──
    {
        const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
        const page = await ctx.newPage();
        try {
            await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle', timeout: 30000 });
            await shot(page, 'admin-login');
            await page.fill('input[type="email"]', ADMIN.email);
            await page.fill('input[type="password"]', ADMIN.password);
            await Promise.all([
                page.waitForURL((u) => !u.pathname.includes('/login'), { timeout: 15000 }).catch(() => {}),
                page.click('button[type="submit"]'),
            ]);
            await page.waitForTimeout(3000);
            console.log('Admin login →', page.url());
            for (const p of ADMIN_PAGES.filter((x) => !x.noAuth)) {
                try {
                    await page.goto(BASE + p.url, { waitUntil: 'networkidle', timeout: 30000 });
                    await shot(page, p.name);
                } catch (e) { console.log(`FAIL ${p.name}: ${e.message}`); }
            }
        } catch (e) { console.log('Admin block FAIL:', e.message); }
        await ctx.close();
    }

    await browser.close();
    console.log('Selesai:', OUT);
})();
