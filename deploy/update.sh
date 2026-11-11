#!/usr/bin/env bash
# Terapkan versi baru ke server yang sudah berjalan (dijalankan sebagai root, setelah zip diekstrak):
#
#   sudo -u www-data php artisan down
#   unzip -o /root/gilisekotong-update-XXXX.zip -d /var/www/gilisekotong
#   sudo bash deploy/update.sh
#
# Tidak menyentuh Nginx, HTTPS, PHP, .env, maupun file unggahan. Database dibackup dulu sebelum migrasi.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WEB_USER=www-data

say() { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }

[[ $EUID -eq 0 ]] || { echo "Jalankan sebagai root: sudo bash deploy/update.sh" >&2; exit 1; }
[[ -f "$APP_DIR/.env" ]] || { echo "File .env tidak ditemukan di $APP_DIR. Untuk pemasangan baru pakai deploy/setup-ubuntu.sh." >&2; exit 1; }

trap 'echo; echo "[x] Update berhenti di tengah jalan dan situs masih dalam mode perbaikan. Periksa pesan di atas. Backup ada di /var/backups/gilisekotong. Untuk membuka situs lagi: sudo -u www-data php artisan up" >&2' ERR

artisan() { sudo -u "$WEB_USER" php "$APP_DIR/artisan" "$@"; }

say "Backup database dan unggahan"
bash "$APP_DIR/deploy/backup.sh"

say "Mengatur kepemilikan folder"
mkdir -p "$APP_DIR"/storage/{app/public,app/private,framework/{cache/data,sessions,views},logs} "$APP_DIR/bootstrap/cache"
chown -R root:root "$APP_DIR"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chown root:"$WEB_USER" "$APP_DIR/.env"
chmod 640 "$APP_DIR/.env"

say "Migrasi database"
artisan migrate --force

say "Menyimpan cache konfigurasi, route, dan tampilan"
artisan optimize

artisan up
trap - ERR
say "Selesai. Situs sudah aktif kembali."
