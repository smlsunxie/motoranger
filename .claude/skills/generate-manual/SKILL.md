---
name: generate-manual
description: 重新生成 Moto Ranger 操作手冊(docs/):用無頭 Chrome 自動截圖各後台/前台頁面、更新各章 Markdown、再編成 manual.html 與 manual.pdf。觸發詞:「生成操作手冊」「更新操作手冊」「重新產生手冊」「regenerate manual」「更新說明書」。
---

# 生成操作手冊

把目前系統功能整理成 `docs/` 操作手冊,並輸出 `docs/manual.html` 與 `docs/manual.pdf`。
截圖一律用**無頭 Chrome(puppeteer-core + 系統 Chrome)**對**本地 serve** 抓圖,並寫成檔案。

> 為何不用 MCP(claude-in-chrome)截圖:MCP 的 `save_to_disk` 不會回傳可存取的檔案路徑,無法嵌入手冊。用本技能的 headless 流程直接把 PNG 寫進 `docs/.../images/`。

## 前置需求

- `pandoc`、`weasyprint`(`which pandoc weasyprint` 應有)
- 系統 Chrome:`/Applications/Google Chrome.app/Contents/MacOS/Google Chrome`(可用 `CHROME_PATH` 覆寫)
- `node`,以及 `puppeteer-core`(裝到暫存目錄,不進 repo)
- 本地 `.env` 為 local 環境(才有 `/dev-login` 自動登入路由)

## 步驟

1. **啟動本地 serve**(若未啟動;埠號自訂,以下用 8123):
   ```bash
   php artisan serve --port=8123 >/tmp/serve.log 2>&1 &
   ```

2. **灌入展示資料**(乾淨、無真實個資;若本地已有足夠資料可略過):
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=DemoSeeder   # database/seeders/DemoSeeder.php
   ```
   確保 `/dev-login` 用的 `admin@motoranger.test` 存在(DemoSeeder 會建立)。

3. **取得示範資料 id**(供截圖頁面用):
   ```bash
   php artisan tinker --execute='$v=\App\Models\Vehicle::has("repairOrders")->first();$o=$v->repairOrders()->first();$c=\App\Models\Customer::has("vehicles")->first();echo json_encode(["vehicle"=>$v->id,"order"=>$o->id,"customer"=>$c->id,"plate"=>substr($v->plate_no,0,3)]);'
   ```

4. **安裝 puppeteer-core(暫存)並截圖**(淺色主題、1440×2x):
   ```bash
   mkdir -p /tmp/cap && (cd /tmp/cap && npm init -y >/dev/null 2>&1 && npm i puppeteer-core >/dev/null 2>&1)
   BASE_URL=http://127.0.0.1:8123 \
   DOCS="$(pwd)/docs" \
   IDS='<上一步的 json>' \
   NODE_PATH=/tmp/cap/node_modules \
   node .claude/skills/generate-manual/capture.cjs
   ```
   產出 17 張 PNG 到 `docs/<章>/images/`(login、dashboard、search-results、維修單 list/create/view、估價單 print、客戶 customers/customer-view、車輛 vehicles/vehicle-profile、parts、brands/stores/expenses/users、前台 home)。

5. **更新各章 Markdown**:依目前功能調整 `docs/README.md` 與 `docs/0?-*/README.md`。圖片用相對路徑 `![](images/xxx.png)`。新增/移除截圖時,記得同步 `capture.cjs` 的截圖清單與 Markdown 引用,並刪除不再引用的舊圖。

6. **編譯手冊**:
   ```bash
   DOCS="$(pwd)/docs" MANUAL_DATE=$(date +%Y-%m-%d) bash .claude/skills/generate-manual/build.sh
   ```
   產出 `docs/manual.html` 與 `docs/manual.pdf`。

7. **驗證**:用 Read 開 `docs/manual.pdf`(pages 2-4)確認版面與截圖正確。

8. 視需要 `git add docs database/seeders/DemoSeeder.php .claude/skills/generate-manual && git commit && git push`。
   **注意**:`docs/` 不會隨 rsync 部署上線(deploy skill 的排除清單含 `docs/`),所以手冊只進版控、不需部署。

## 檔案

- `capture.cjs` — 無頭 Chrome 截圖腳本(env:`BASE_URL`、`DOCS`、`IDS`、`CHROME_PATH`、`NODE_PATH`)。鎖淺色主題(`localStorage.theme=light` + `prefers-color-scheme: light`)。
- `build.sh` — 用 pandoc 串接各章、套用內嵌 CSS 與封面,輸出 html;再用 weasyprint 轉 pdf。
- 展示資料種子:`database/seeders/DemoSeeder.php`。
