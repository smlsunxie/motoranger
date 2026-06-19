#!/usr/bin/env bash
# 把 docs 各章 README.md 編成 manual.html 與 manual.pdf。
# 需要:pandoc、weasyprint。用法:DOCS=/abs/path/to/docs bash build.sh
set -euo pipefail

DOCS="${DOCS:-$(cd "$(dirname "$0")/../../../docs" && pwd)}"
DATE="${MANUAL_DATE:-$(date +%Y-%m-%d)}"
cd "$DOCS"

SECTIONS=(01-getting-started 02-repair-orders 03-quotes 04-customers-vehicles 05-parts 06-settings 07-front-site)

tmp="$(mktemp -d)"
all="$tmp/all.md"
: > "$all"
for d in "${SECTIONS[@]}"; do
    # 章節內 ![](images/..) 需加上章節資料夾前綴,才能在 docs/manual.html 正確解析
    sed "s#](images/#](${d}/images/#g" "$d/README.md" >> "$all"
    printf '\n\n' >> "$all"
done

pandoc "$all" -t html5 -o "$tmp/body.html"

cat > manual.html <<'HTML'
<!DOCTYPE html><html lang="zh-Hant"><head><meta charset="utf-8"><title>Moto Ranger 操作說明書</title><style>
body { font-family: "PingFang TC", "Noto Sans TC", sans-serif; font-size: 13px; line-height: 1.75; color: #1e293b; max-width: 960px; margin: 0 auto; padding: 24px; }
h1 { font-size: 26px; border-bottom: 3px solid #f59e0b; padding-bottom: 8px; margin-top: 0; page-break-before: always; }
h1:first-of-type { page-break-before: avoid; }
h2 { font-size: 19px; color: #b45309; margin-top: 28px; }
img { max-width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; margin: 10px 0; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
table { border-collapse: collapse; width: 100%; margin: 12px 0; }
th, td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; }
th { background: #fef3c7; }
blockquote { border-left: 4px solid #f59e0b; background: #fffbeb; margin: 12px 0; padding: 8px 14px; color: #92400e; }
code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; }
a { color: #b45309; }
ol li, ul li { margin: 3px 0; }
.cover { text-align: center; padding-top: 200px; page-break-after: always; }
.cover h1 { border: none; font-size: 40px; page-break-before: avoid; }
.cover p { color: #64748b; font-size: 15px; }
</style></head><body>
HTML

printf '<div class="cover"><h1>Moto Ranger<br>操作說明書</h1><p>汽機車維修紀錄系統 — 後台管理與前台網站</p><p>https://main.motoranger.net</p><p>%s</p></div>\n' "$DATE" >> manual.html
cat "$tmp/body.html" >> manual.html
echo '</body></html>' >> manual.html

weasyprint manual.html manual.pdf

rm -rf "$tmp"
echo "✓ manual.html + manual.pdf 已更新($DATE)"
