// 操作手冊截圖工具:用系統 Chrome(puppeteer-core)登入本地後台,逐頁截圖到 docs/。
// 用法:BASE_URL=http://127.0.0.1:8123 DOCS=/abs/path/to/docs node capture.js
// 需先:1) 本地 serve 啟動  2) php artisan db:seed --class=DemoSeeder  3) /dev-login 可用(local env)
const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8123';
const DOCS = process.env.DOCS || path.resolve(__dirname, '../../../docs');
const CHROME = process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

// 由呼叫端帶入示範資料 id(避免硬編);預設 1。
const IDS = JSON.parse(process.env.IDS || '{"vehicle":1,"order":1,"customer":1,"plate":"G6Y"}');

const out = (p) => {
    const full = path.join(DOCS, p);
    fs.mkdirSync(path.dirname(full), { recursive: true });
    return full;
};
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function shot(page, url, file, { action, wait = 1200, fullPage = true } = {}) {
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 }).catch(() => {});
    if (action) await action(page);
    await sleep(wait); // 等 Livewire / relation manager 非同步渲染
    await page.screenshot({ path: out(file), fullPage });
    console.log('✓', file);
}

(async () => {
    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: 'new',
        args: ['--no-sandbox', '--hide-scrollbars', '--force-color-profile=srgb'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 960, deviceScaleFactor: 2 });
    // 手冊用淺色主題(列印友善)
    await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'light' }]);

    // 先到後台 origin 把 Filament 主題鎖為淺色
    await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle0' }).catch(() => {});
    await page.evaluate(() => localStorage.setItem('theme', 'light')).catch(() => {});

    // 登入頁(尚未登入時截)
    await shot(page, `${BASE}/admin/login`, '01-getting-started/images/login.png', { fullPage: false });

    // 自動登入(local /dev-login)
    await page.goto(`${BASE}/dev-login`, { waitUntil: 'networkidle0' });

    // 01 入門
    await shot(page, `${BASE}/admin`, '01-getting-started/images/dashboard.png', { fullPage: false });
    await shot(page, `${BASE}/admin`, '01-getting-started/images/search-results.png', {
        fullPage: false,
        action: async (p) => {
            await p.waitForSelector('form input[type="text"]', { timeout: 8000 });
            await p.type('form input[type="text"]', IDS.plate);
            await p.keyboard.press('Enter');
            await p.waitForSelector('a[href*="/profile"]', { timeout: 8000 }).catch(() => {});
        },
    });

    // 02 維修單
    await shot(page, `${BASE}/admin/repair-orders`, '02-repair-orders/images/list.png');
    await shot(page, `${BASE}/admin/repair-orders/create`, '02-repair-orders/images/create.png');
    await shot(page, `${BASE}/admin/repair-orders/${IDS.order}`, '02-repair-orders/images/view.png');

    // 03 估價單
    await shot(page, `${BASE}/repair-orders/${IDS.order}/quote`, '03-quotes/images/print.png');

    // 04 客戶與車輛
    await shot(page, `${BASE}/admin/customers`, '04-customers-vehicles/images/customers.png');
    await shot(page, `${BASE}/admin/customers/${IDS.customer}`, '04-customers-vehicles/images/customer-view.png');
    await shot(page, `${BASE}/admin/vehicles`, '04-customers-vehicles/images/vehicles.png');
    await shot(page, `${BASE}/admin/vehicles/${IDS.vehicle}/profile`, '04-customers-vehicles/images/vehicle-profile.png');

    // 05 零件
    await shot(page, `${BASE}/admin/parts`, '05-parts/images/parts.png');

    // 06 基礎資料
    await shot(page, `${BASE}/admin/brands`, '06-settings/images/brands.png');
    await shot(page, `${BASE}/admin/stores`, '06-settings/images/stores.png');
    await shot(page, `${BASE}/admin/store-expenses`, '06-settings/images/expenses.png');
    await shot(page, `${BASE}/admin/users`, '06-settings/images/users.png');

    // 07 前台
    await shot(page, `${BASE}/`, '07-front-site/images/home.png', { fullPage: true });

    await browser.close();
    console.log('done');
})().catch((e) => { console.error(e); process.exit(1); });
