# Deployment Guide — Kos Kosan Pro

## 1. Persyaratan Server

- PHP 8.3+
- MySQL 8.0+ / MariaDB 10.6+
- Composer 2.x
- Node.js 20+ & NPM
- Apache/Nginx dengan mod_rewrite

---

## 2. Deployment Pertama Kali

### Clone dari GitHub
```bash
cd /var/www
git clone https://github.com/linducip2208/kos.git kos-kosan
cd kos-kosan
```

### Install dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Setup environment
```bash
cp .env.example .env
nano .env
```

Isi minimal:
```
APP_NAME="Kos Kosan Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kos_kosan
DB_USERNAME=root
DB_PASSWORD=your_password

LICENSE_DEV_BYPASS=false
LICENSE_SERVER_URL=https://whitelabel.co.id
```

### Generate key & migrate
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

### Set permission
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 3. Nginx Config

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/kos-kosan/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|woff2|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## 4. Supervisor (Queue + Scheduler)

```ini
[program:kos-queue]
command=php /var/www/kos-kosan/artisan queue:work --sleep=3 --tries=3
user=www-data
numprocs=2
autostart=true
autorestart=true

[program:kos-scheduler]
command=php /var/www/kos-kosan/artisan schedule:work
user=www-data
autostart=true
autorestart=true
```

---

## 5. Update dari GitHub (setelah deploy pertama)

```bash
cd /var/www/kos-kosan
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### One-liner update script:
```bash
cd /var/www/kos-kosan && git pull origin main && composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan migrate --force && php artisan optimize:clear
```

---

## 6. Cron Job

```cron
* * * * * cd /var/www/kos-kosan && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler otomatis menjalankan:
- `license:validate` — daily 05:00
- `invoices:generate` — daily 06:00
- `invoices:mark-overdue` — daily 07:00
- `invoices:send-reminders` — daily 08:00
- `lease:check-expiring` — weekly Monday
- `backup:run` — daily 02:00
- `seo:indexnow` — daily 02:45

---

## 7. Submit ke Search Engine

Setelah deploy:
1. **Google Search Console**: tambahkan domain, verifikasi
2. **Submit sitemap**: `https://domain-anda.com/sitemap.xml`
3. **IndexNow key**: sudah auto-generate di `/indexnow-key.txt`
4. **Bing Webmaster**: import dari GSC atau daftar manual
