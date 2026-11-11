#!/usr/bin/env bash
# Setup GiliSekotong di VPS Ubuntu 22.04 / 24.04 (dijalankan sebagai root).
#
#   sudo DOMAIN=gilisekotong.com CERTBOT_EMAIL=email@anda.com bash deploy/setup-ubuntu.sh
#
# Aman dijalankan ulang: database, .env, dan akun admin yang sudah ada tidak ditimpa.
# Variabel opsional: SKIP_SSL=1 (lewati HTTPS), ADMIN_EMAIL=... (email Super Admin pertama).
set -euo pipefail

DOMAIN="${DOMAIN:-gilisekotong.com}"
PHPV="${PHPV:-8.3}"
DB_NAME="${DB_NAME:-gilisekotong}"
DB_USER="${DB_USER:-gilisekotong}"
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WEB_USER=www-data

say()  { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m[!] %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m[x] %s\033[0m\n' "$*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || die "Jalankan sebagai root: sudo bash deploy/setup-ubuntu.sh"
[[ -f "$APP_DIR/artisan" ]] || die "File artisan tidak ditemukan di $APP_DIR. Ekstrak zip dulu, lalu jalankan skrip dari folder aplikasi."
. /etc/os-release
[[ "${ID:-}" == "ubuntu" ]] || warn "Skrip ini disiapkan untuk Ubuntu; distro terdeteksi: ${PRETTY_NAME:-tidak diketahui}."

artisan() { sudo -u "$WEB_USER" php "$APP_DIR/artisan" "$@"; }

# ---------------------------------------------------------------- paket
say "Memasang Nginx, MySQL, PHP $PHPV, ffmpeg, certbot"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y software-properties-common ca-certificates curl unzip openssl
if ! apt-cache show "php${PHPV}-fpm" >/dev/null 2>&1; then
    say "PHP $PHPV belum ada di repositori bawaan, menambahkan PPA ondrej/php"
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
fi
apt-get install -y nginx mysql-server ffmpeg certbot python3-certbot-nginx \
    "php${PHPV}-fpm" "php${PHPV}-cli" "php${PHPV}-mysql" "php${PHPV}-mbstring" "php${PHPV}-xml" \
    "php${PHPV}-curl" "php${PHPV}-zip" "php${PHPV}-gd" "php${PHPV}-bcmath" "php${PHPV}-intl"

# ---------------------------------------------------------------- PHP
say "Mengatur batas unggah PHP ke 40 MB"
for ini in "/etc/php/${PHPV}/fpm/php.ini" "/etc/php/${PHPV}/cli/php.ini"; do
    sed -i -E 's/^;?upload_max_filesize = .*/upload_max_filesize = 40M/; s/^;?post_max_size = .*/post_max_size = 40M/; s/^;?expose_php = .*/expose_php = Off/' "$ini"
done
systemctl enable --now "php${PHPV}-fpm" mysql nginx
systemctl restart "php${PHPV}-fpm"

# ---------------------------------------------------------------- database & .env
if [[ -f "$APP_DIR/.env" ]]; then
    say ".env sudah ada, dipakai apa adanya"
else
    say "Membuat database $DB_NAME dan file .env"
    DB_PASS="$(openssl rand -hex 20)"
    mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
    sed -e "s/__DOMAIN__/${DOMAIN}/g" -e "s/__DB_NAME__/${DB_NAME}/" -e "s/__DB_USER__/${DB_USER}/" -e "s/__DB_PASS__/${DB_PASS}/" \
        "$APP_DIR/deploy/env.production.example" > "$APP_DIR/.env"
fi

# ---------------------------------------------------------------- izin folder
say "Mengatur kepemilikan dan izin folder"
mkdir -p "$APP_DIR"/storage/{app/public,app/private,framework/{cache/data,sessions,views},logs} "$APP_DIR/bootstrap/cache"
chown -R root:root "$APP_DIR"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chown root:"$WEB_USER" "$APP_DIR/.env"
chmod 640 "$APP_DIR/.env"

# ---------------------------------------------------------------- Laravel
if ! grep -qE '^APP_KEY=base64:' "$APP_DIR/.env"; then
    say "Membuat APP_KEY"
    php "$APP_DIR/artisan" key:generate --force
fi

say "Menjalankan migrasi database"
# public/ milik root, jadi symlink storage dibuat sebagai root.
php "$APP_DIR/artisan" storage:link --force >/dev/null 2>&1 || true
artisan migrate --force

ENV_DB="$(grep -E '^DB_DATABASE=' "$APP_DIR/.env" | cut -d= -f2- | tr -d '"')"
USER_COUNT="$(mysql -N -B -e "SELECT COUNT(*) FROM \`${ENV_DB}\`.users" 2>/dev/null || echo 0)"
if [[ "$USER_COUNT" == "0" ]]; then
    say "Mengisi data awal (akun Super Admin, perahu, paket makan, model pohon keputusan)"
    sudo -u "$WEB_USER" env ADMIN_EMAIL="${ADMIN_EMAIL:-superadmin@${DOMAIN}}" php "$APP_DIR/artisan" db:seed --class=ProductionSeeder --force
fi

# Perintah di atas sebagian dijalankan sebagai root; pastikan file baru di storage tetap milik web server.
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
artisan optimize
artisan bmkg:sync || warn "Sinkron BMKG pertama gagal; akan dicoba lagi otomatis oleh cron."

# ---------------------------------------------------------------- Nginx
say "Mengatur Nginx untuk $DOMAIN"
# Konfigurasi yang sudah ada tidak ditimpa, karena berisi pengaturan HTTPS dari certbot.
if [[ -f /etc/nginx/sites-available/gilisekotong ]]; then
    warn "Konfigurasi Nginx sudah ada, dipakai apa adanya."
else
    sed -e "s#__DOMAIN__#${DOMAIN}#g" -e "s#__ROOT__#${APP_DIR}#g" -e "s#__PHPV__#${PHPV}#g" \
        "$APP_DIR/deploy/nginx-gilisekotong.conf" > /etc/nginx/sites-available/gilisekotong
fi
ln -sf /etc/nginx/sites-available/gilisekotong /etc/nginx/sites-enabled/gilisekotong
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# ---------------------------------------------------------------- cron (sinkron BMKG dll.)
say "Memasang cron: scheduler Laravel (tiap menit) dan backup harian (02.00)"
cat > /etc/cron.d/gilisekotong <<CRON
* * * * * ${WEB_USER} cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1
0 2 * * * root bash ${APP_DIR}/deploy/backup.sh >> /var/log/gilisekotong-backup.log 2>&1
CRON
chmod 644 /etc/cron.d/gilisekotong

# ---------------------------------------------------------------- HTTPS
if [[ "${SKIP_SSL:-0}" == "1" ]]; then
    warn "SKIP_SSL=1: HTTPS dilewati. Jalankan nanti: certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
elif [[ -z "${CERTBOT_EMAIL:-}" ]]; then
    warn "CERTBOT_EMAIL kosong: HTTPS dilewati. Jalankan nanti: certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
else
    say "Memasang sertifikat HTTPS Let's Encrypt"
    certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos -m "$CERTBOT_EMAIL" --redirect \
        || warn "Certbot gagal. Pastikan DNS ${DOMAIN} dan www.${DOMAIN} sudah mengarah ke IP VPS ini, lalu jalankan ulang certbot."
fi

say "Selesai. Buka https://${DOMAIN} dan masuk ke https://${DOMAIN}/masuk"
echo "Langkah berikutnya: Admin → Pengaturan (rekening, kontak), Admin → Email (SMTP), Admin → SEO, Admin → Galeri Beranda."
