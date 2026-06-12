---
name: deploy
description: 部署 motoranger 到線上(main.motoranger.net)。觸發詞:「部署」「deploy」「更新線上」「更新站點」「上線」。
---

# Motoranger 線上部署

## 環境資訊

- **網址**:https://main.motoranger.net(Caddy 自動 Let's Encrypt)
- **主機**:Linode jp-osa(大阪),`ssh root@172.235.215.143`(SSH 金鑰 `~/.ssh/id_ed25519`)
- **專案路徑**:`/opt/motoranger`
- **架構**:docker compose 三容器 — `caddy`(80/443 反代)+ `app`(Laravel Octane/FrankenPHP,容器內 8000)+ `mysql`(8.4)
- **設定**:server 的 `/opt/motoranger/.env` 只放 compose 變數(APP_KEY / APP_URL / APP_PORT / DB_PASSWORD),其餘 env 寫在 docker-compose.yml
- **儲存**:圖片走 local storage(`storage-data` volume 掛 `/app/storage/app`),不用 S3;cache/session=database、queue=sync、無 redis

## 部署步驟

1. **前置檢查**:工作區乾淨、測試通過

   ```bash
   git status --short && php artisan test
   ```

2. **同步程式碼**(注意排除清單 — 千萬不要同步 storage/、.env、database.sqlite)

   ```bash
   rsync -az --delete \
     --exclude .git --exclude /legacy --exclude node_modules --exclude vendor \
     --exclude frankenphp --exclude database/database.sqlite --exclude .env \
     --exclude storage/ --exclude docs/ \
     ./ root@172.235.215.143:/opt/motoranger/
   ```

3. **重建並重啟**(程式碼在 image 內,任何 PHP/Blade 變更都要 rebuild;有 layer cache 通常 < 1 分鐘)

   ```bash
   ssh root@172.235.215.143 'cd /opt/motoranger && docker compose build app && docker compose up -d'
   ```

   - 容器啟動時 entrypoint 會自動 `migrate --force`(AUTO_MIGRATE=true)與 `php artisan optimize`。

4. **驗證**

   ```bash
   curl -s -o /dev/null -w "front: %{http_code}\n"  https://main.motoranger.net/
   curl -s -o /dev/null -w "admin: %{http_code}\n"  https://main.motoranger.net/admin/login
   ssh root@172.235.215.143 'docker ps --format "{{.Names}} {{.Status}}"'
   ```

   front/admin 都要 200;`app` 容器應為 healthy。

## 注意事項

- **git push 與部署是兩件事**:目前部署用 rsync(server 沒有 git checkout);push 到 GitHub(`origin main`)不會自動更新線上。
- 前端資產(Tailwind)在 Dockerfile 的 assets stage 內 build,本機不需先 `npm run build`。
- `composer.json` 變更會使 image cache 失效,build 會比較久(composer install 重跑)。
- 機器只有 1c/2GB(+2G swap),build 期間網站仍由舊容器服務,無停機。

## 維運指令

```bash
# 看 app log
ssh root@172.235.215.143 'cd /opt/motoranger && docker compose logs app --tail 50'

# 進入 tinker
ssh root@172.235.215.143 'cd /opt/motoranger && docker compose exec app php artisan tinker'

# 手動備份線上資料庫
ssh root@172.235.215.143 'cd /opt/motoranger && DBPASS=$(grep ^DB_PASSWORD .env | cut -d= -f2) && docker compose exec -T mysql mysqldump -uroot -p"$DBPASS" --single-transaction motoranger | gzip' > backup-$(date +%Y%m%d).sql.gz

# 重跑舊資料轉移(legacy dump 已在 mysql 容器的 motoranger_legacy db;會清空現有資料!需明確確認)
ssh root@172.235.215.143 'cd /opt/motoranger && DBPASS=$(grep ^DB_PASSWORD .env | cut -d= -f2) && docker compose exec -T app php artisan legacy:migrate --fresh --host=mysql --port=3306 --database=motoranger_legacy --username=root --password="$DBPASS"'
```
