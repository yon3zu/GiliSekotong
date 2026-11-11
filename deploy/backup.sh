#!/usr/bin/env bash
# Backup harian GiliSekotong: database + file unggahan (foto galeri, foto perahu, bukti transfer).
# Dipasang otomatis oleh setup-ubuntu.sh (cron 02.00). Backup disimpan 14 hari di /var/backups/gilisekotong.
# Salin juga sesekali ke luar VPS (Google Drive, laptop) agar aman bila VPS rusak.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEST="${BACKUP_DIR:-/var/backups/gilisekotong}"
KEEP_DAYS="${KEEP_DAYS:-14}"
STAMP="$(date +%Y%m%d-%H%M)"

env_get() { grep -E "^$1=" "$APP_DIR/.env" | head -1 | cut -d= -f2- | tr -d '"'; }

mkdir -p "$DEST"
chmod 700 "$DEST"

mysqldump --single-transaction --quick --no-tablespaces \
    -h "$(env_get DB_HOST)" -P "$(env_get DB_PORT)" -u "$(env_get DB_USERNAME)" -p"$(env_get DB_PASSWORD)" \
    "$(env_get DB_DATABASE)" | gzip > "$DEST/db-$STAMP.sql.gz"

tar -czf "$DEST/uploads-$STAMP.tar.gz" -C "$APP_DIR/storage/app" public private

find "$DEST" -type f -mtime +"$KEEP_DAYS" -delete
echo "Backup selesai: $DEST (db-$STAMP.sql.gz, uploads-$STAMP.tar.gz)"
