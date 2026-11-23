import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.DASH_URL ?? 'http://127.0.0.1:8000';
const OUT = path.resolve('docs/screenshots');
fs.mkdirSync(OUT, { recursive: true });

const viewports = [
    { name: 'dashboard-desktop-1920x1080', width: 1920, height: 1080 },
    { name: 'dashboard-laptop-1366x768', width: 1366, height: 768 },
    { name: 'dashboard-tablet-768x1024', width: 768, height: 1024 },
    { name: 'dashboard-mobile-390x844', width: 390, height: 844 },
];

const browser = await chromium.launch();
const errors = [];

try {
    for (const vp of viewports) {
        const context = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await context.newPage();
        page.on('console', (msg) => {
            if (msg.type() === 'error') errors.push(`[${vp.name}] console: ${msg.text().slice(0, 200)}`);
        });
        page.on('pageerror', (err) => errors.push(`[${vp.name}] pageerror: ${String(err).slice(0, 200)}`));

        await page.goto(`${BASE}/admin/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('input[type="email"]').fill('admin@kos.test');
        await page.locator('input[type="password"]').fill('password');
        await page.locator('button[type="submit"]').click();
        await page.waitForURL('**/admin', { timeout: 30000 });

        // Scroll every card into view so Livewire lazy widgets + Alpine
        // x-load chart components are triggered before the screenshot.
        // The DOM changes as lazy widgets resolve, so re-query each pass.
        for (let pass = 0; pass < 3; pass++) {
            const cardCount = await page.locator('.fi-section').count();
            for (let i = 0; i < cardCount; i++) {
                try {
                    await page.locator('.fi-section').nth(i).scrollIntoViewIfNeeded({ timeout: 8000 });
                    await page.waitForTimeout(600);
                } catch {
                    // Card detached during Livewire morph; continue.
                }
            }
        }
        await page.waitForFunction(
            () => !document.querySelector('.fi-loading-section, .fi-skeleton'),
            { timeout: 90000 }
        ).catch(() => {});
        await page.waitForTimeout(8000);
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.waitForTimeout(800);

        await page.screenshot({ path: path.join(OUT, `${vp.name}.png`), fullPage: true });
        console.log(`saved ${vp.name}.png`);

        // Mobile/tablet: also capture sidebar drawer state.
        if (vp.width <= 1024) {
            const toggle = page.locator('button.fi-topbar-open-sidebar-btn, [data-testid="open-sidebar"]').first();
            const fallback = page.locator('header button').first();
            const btn = (await toggle.count()) > 0 ? toggle : fallback;
            if ((await btn.count()) > 0) {
                await btn.click().catch(() => {});
                await page.waitForTimeout(800);
                await page.screenshot({ path: path.join(OUT, `${vp.name}-sidebar.png`) });
                console.log(`saved ${vp.name}-sidebar.png`);
            }
        }

        const overflow = await page.evaluate(() => ({
            doc: document.documentElement.scrollWidth,
            win: window.innerWidth,
        }));
        console.log(`${vp.name} overflow check:`, JSON.stringify(overflow));

        await context.close();
    }
} finally {
    await browser.close();
}

const report = errors.length ? errors.join('\n') : 'no console/page errors';
fs.writeFileSync(path.join(OUT, 'console-errors.txt'), report);
console.log(report);
