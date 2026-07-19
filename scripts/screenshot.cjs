const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = 'http://kos.test';
const SCREEN_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens');

const EMAIL = 'admin@kos.test';
const PASSWORD = 'password';

const PAGES = [
    { name: '01-landing-home', url: '/', desc: 'Landing Page — Beranda' },
    { name: '02-admin-login', url: '/admin/login', desc: 'Admin Login — Two Column' },
    { name: '03-admin-dashboard', url: '/admin', desc: 'Admin Dashboard', admin: true },
    { name: '04-property-list', url: '/admin/properties', desc: 'Property List', admin: true },
    { name: '05-room-list', url: '/admin/rooms', desc: 'Room List', admin: true },
    { name: '06-occupant-list', url: '/admin/occupants', desc: 'Occupant List', admin: true },
    { name: '07-lease-list', url: '/admin/leases', desc: 'Lease List', admin: true },
    { name: '08-invoice-list', url: '/admin/invoices', desc: 'Invoice List', admin: true },
    { name: '09-financial-report', url: '/admin/financial-report', desc: 'Financial Report', admin: true },
    { name: '10-blog-list', url: '/admin/blog-posts', desc: 'Blog Post List', admin: true },
    { name: '11-docs-page', url: '/docs', desc: 'Documentation Page' },
    { name: '12-blog-public', url: '/blog', desc: 'Public Blog Listing' },
];

(async () => {
    if (!fs.existsSync(SCREEN_DIR)) fs.mkdirSync(SCREEN_DIR, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 2,
    });

    const page = await context.newPage();

    // Login — needed for admin pages
    console.log('Logging in with:', EMAIL);
    try {
        await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1500);
        await page.type('input[type="email"]', EMAIL, { delay: 50 });
        await page.type('input[type="password"]', PASSWORD, { delay: 50 });
        await page.waitForTimeout(500);
        await page.click('button[type="submit"]');
        await page.waitForTimeout(5000);
        const url = page.url();
        console.log('After login:', url);
        console.log(url.includes('/login') ? 'WARNING: could not login' : 'Login OK');
    } catch (e) {
        console.log('Login attempt finished with:', e.message);
    }

    // Take all screenshots
    for (const { name, url, desc, admin } of PAGES) {
        const fullUrl = BASE + url;
        console.log(`Capturing: ${desc} (${name})`);

        try {
            await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: 20000 });
            await page.waitForTimeout(1500);
            await page.screenshot({
                path: path.join(SCREEN_DIR, `${name}.png`),
                fullPage: false,
            });
            console.log(`  -> OK (${page.url().substring(0, 60)})`);
        } catch (e) {
            console.log(`  -> Failed: ${e.message}`);
        }
    }

    // Mobile screenshots
    console.log('\nCapturing mobile screenshots...');
    const mobileDir = path.join(__dirname, '..', 'public', 'marketing', 'screens-mobile');
    if (!fs.existsSync(mobileDir)) fs.mkdirSync(mobileDir, { recursive: true });

    await page.setViewportSize({ width: 414, height: 896 });
    const mobilePages = ['03-dashboard-mobile', '04-property-mobile', '11-docs-mobile'];
    const mobileUrls = ['/admin', '/admin/properties', '/docs'];

    for (let i = 0; i < mobilePages.length; i++) {
        console.log(`Mobile: ${mobilePages[i]}`);
        try {
            await page.goto(BASE + mobileUrls[i], { waitUntil: 'networkidle', timeout: 20000 });
            await page.waitForTimeout(1500);
            await page.screenshot({
                path: path.join(mobileDir, `${mobilePages[i]}.png`),
                fullPage: false,
            });
            console.log(`  -> OK`);
        } catch (e) {
            console.log(`  -> Failed: ${e.message}`);
        }
    }

    await browser.close();
    console.log('\nDone! All screenshots saved.');
})();
