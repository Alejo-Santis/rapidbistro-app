# Deploy en AWS Free Tier — RapidBistro

Guía paso a paso para desplegar RapidBistro en una instancia EC2 t2.micro (Free Tier).

---

## 1. Infraestructura recomendada (Free Tier)

| Servicio | Tipo | Notas |
|----------|------|-------|
| EC2 | t2.micro (1 vCPU, 1 GB RAM) | Free Tier: 750 h/mes |
| RDS PostgreSQL | db.t3.micro | Free Tier: 750 h/mes, 20 GB |
| SES | — | Free Tier: 62 000 emails/mes desde EC2 |
| Route 53 | — | ~$0.50/mes por zona hosted |
| ACM | — | Certificado SSL gratuito |

> **Alternativa económica:** usar PostgreSQL en la misma EC2 (evita costo de RDS).  
> Para producción real, RDS es preferible por backups automáticos.

---

## 2. Preparar la instancia EC2

### 2.1 Lanzar instancia
- AMI: **Ubuntu Server 24.04 LTS**
- Tipo: `t2.micro`
- Key pair: crear o seleccionar un `.pem`
- Security Group (puertos):
  - 22 (SSH) — solo tu IP
  - 80 (HTTP) — 0.0.0.0/0
  - 443 (HTTPS) — 0.0.0.0/0

### 2.2 Conectar por SSH
```bash
chmod 400 tu-key.pem
ssh -i tu-key.pem ubuntu@<IP_PUBLICA_EC2>
```

---

## 3. Instalar dependencias del servidor

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.3 + extensiones
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-redis

# Nginx
sudo apt install -y nginx

# PostgreSQL (si no usas RDS)
sudo apt install -y postgresql postgresql-contrib

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Supervisor (para queue workers)
sudo apt install -y supervisor

# Git
sudo apt install -y git
```

---

## 4. Configurar PostgreSQL

```bash
sudo -u postgres psql

-- Dentro de psql:
CREATE USER rapidbistro WITH PASSWORD 'TU_PASSWORD_SEGURA';
CREATE DATABASE rapidbistro_db OWNER rapidbistro;
GRANT ALL PRIVILEGES ON DATABASE rapidbistro_db TO rapidbistro;
\q
```

---

## 5. Clonar y configurar la aplicación

```bash
cd /var/www
sudo git clone https://github.com/TU_USER/rapidbistro-app.git
sudo chown -R ubuntu:www-data /var/www/rapidbistro-app
cd /var/www/rapidbistro-app

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Instalar dependencias JS y compilar assets
npm ci
npm run build

# Configurar .env
cp .env.example .env
php artisan key:generate
```

### 5.1 Editar `.env` para producción

```bash
nano .env
```

```env
APP_NAME="RapidBistro"
APP_ENV=production
APP_KEY=base64:...   # generado por key:generate
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1    # o endpoint de RDS
DB_PORT=5432
DB_DATABASE=rapidbistro_db
DB_USERNAME=rapidbistro
DB_PASSWORD=TU_PASSWORD_SEGURA

QUEUE_CONNECTION=database

# Mailtrap (testing) — reemplazar por SES en producción
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=TU_MAILTRAP_USER
MAIL_PASSWORD=TU_MAILTRAP_PASS
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="RapidBistro"

# AWS SES (producción)
# MAIL_MAILER=ses
# AWS_ACCESS_KEY_ID=...
# AWS_SECRET_ACCESS_KEY=...
# AWS_DEFAULT_REGION=us-east-1
```

### 5.2 Migrar y seedear

```bash
php artisan migrate --force
php artisan db:seed --force

# Cache de configuración para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5.3 Permisos correctos

```bash
sudo chown -R www-data:www-data /var/www/rapidbistro-app/storage
sudo chown -R www-data:www-data /var/www/rapidbistro-app/bootstrap/cache
sudo chmod -R 775 /var/www/rapidbistro-app/storage
sudo chmod -R 775 /var/www/rapidbistro-app/bootstrap/cache
```

---

## 6. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/rapidbistro
```

```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    root /var/www/rapidbistro-app/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 10M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/rapidbistro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 7. SSL con Let's Encrypt (Certbot)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com -d www.tudominio.com

# Renovación automática (ya viene configurada con certbot)
sudo systemctl status certbot.timer
```

---

## 8. Supervisor — Queue Workers persistentes

```bash
# Copiar configuraciones incluidas en el proyecto
sudo cp /var/www/rapidbistro-app/supervisor/rapidbistro-worker.conf \
        /etc/supervisor/conf.d/

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rapidbistro-worker:*

# Verificar estado
sudo supervisorctl status
```

Logs en `/var/log/supervisor/rapidbistro-worker.log`.

---

## 9. Cron — Laravel Scheduler

```bash
sudo crontab -u www-data -e

# Agregar esta línea:
* * * * * cd /var/www/rapidbistro-app && php artisan schedule:run >> /dev/null 2>&1
```

> Esto ejecuta recordatorios de reserva, limpieza de tokens expirados, etc.

---

## 10. Configurar Mailtrap (testing)

1. Crear cuenta en [mailtrap.io](https://mailtrap.io)
2. Ir a **Email Testing → Inboxes → Tu inbox → SMTP Settings**
3. Seleccionar integración **Laravel**
4. Copiar credenciales al `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<tu_username>
MAIL_PASSWORD=<tu_password>
MAIL_ENCRYPTION=tls
```

Para **producción**, migrar a AWS SES o Resend (más económicos que SendGrid).

---

## 11. Script de deploy (actualizaciones futuras)

```bash
# /var/www/rapidbistro-app/deploy.sh
#!/bin/bash
set -e

cd /var/www/rapidbistro-app

echo "→ Pulling latest code..."
git pull origin main

echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "→ Building frontend assets..."
npm ci
npm run build

echo "→ Running migrations..."
php artisan migrate --force

echo "→ Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Restarting queue workers..."
sudo supervisorctl restart rapidbistro-worker:*

echo "→ Reloading PHP-FPM..."
sudo systemctl reload php8.3-fpm

echo "✓ Deploy completado."
```

```bash
chmod +x /var/www/rapidbistro-app/deploy.sh
```

---

## 12. Checklist antes de ir a producción

- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] SSL activo (HTTPS)
- [ ] `php artisan config:cache` ejecutado
- [ ] `php artisan route:cache` ejecutado
- [ ] Supervisor corriendo (`supervisorctl status`)
- [ ] Cron configurado para el scheduler
- [ ] Mailtrap reemplazado por SES/Resend
- [ ] Backups de base de datos configurados (RDS automated backups o pg_dump en cron)
- [ ] Security Group de EC2 con puerto 22 limitado a tu IP
- [ ] Variables sensibles nunca en git (`.env` en `.gitignore`)

---

## 13. Costos estimados Free Tier (12 meses)

| Recurso | Free Tier | Costo post-free |
|---------|-----------|-----------------|
| EC2 t2.micro | 750 h/mes gratis | ~$8.50/mes |
| RDS db.t3.micro | 750 h/mes + 20 GB gratis | ~$13/mes |
| SES (desde EC2) | 62 000 emails/mes gratis | $0.10/1000 |
| Route 53 | — | ~$0.50/mes |
| **Total estimado** | **$0** (12 meses) | **~$22/mes** |

> Para mantener costos bajos post-free: usar PostgreSQL en la misma EC2 (sin RDS).
