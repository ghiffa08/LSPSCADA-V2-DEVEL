# OBSERVASI SYSTEM OPTIMIZATION - DEPLOYMENT GUIDE

## Overview
This guide covers the deployment of the optimized observasi (observation) system for LSP SCADA application. The optimization includes performance improvements, security enhancements, and modern frontend/backend architecture.

## Pre-deployment Requirements

### System Requirements
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Redis (recommended for caching)
- Apache/Nginx with mod_rewrite
- Composer 2.x
- Node.js 16+ (for frontend asset compilation)

### Database Requirements
- InnoDB storage engine
- Foreign key constraints enabled
- Sufficient privileges for:
  - Creating indexes
  - Creating stored procedures
  - Creating triggers

### PHP Extensions Required
```
php-mysql
php-redis
php-json
php-mbstring
php-xml
php-curl
php-gd
php-zip
```

## Backup Procedures

### 1. Database Backup
```bash
# Create full database backup
mysqldump -u username -p --single-transaction --routines --triggers lsp_scada_app_devel > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup integrity
mysql -u username -p -e "SELECT COUNT(*) FROM observasi;" lsp_scada_app_devel
mysql -u username -p -e "SELECT COUNT(*) FROM detail_observasi;" lsp_scada_app_devel
```

### 2. Application Backup
```bash
# Backup current application files
tar -czf app_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  app/Controllers/Api/Observasi.php \
  app/Services/ObservasiService.php \
  app/Models/ObservasiModel.php \
  app/Requests/ObservasiRequest.php \
  app/Views/asesor/utility/ceklist-js.php

# Backup writable directory (logs, cache, sessions)
tar -czf writable_backup_$(date +%Y%m%d_%H%M%S).tar.gz writable/
```

## Deployment Steps

### Step 1: Environment Preparation

#### 1.1 Enable Maintenance Mode
```bash
# Create maintenance page
touch public/.maintenance

# Or use CodeIgniter's environment
echo "CI_ENVIRONMENT=production" > .env
echo "app.forceGlobalSecureRequests = true" >> .env
```

#### 1.2 Update Environment Configuration
```bash
# Database configuration
database.default.hostname = localhost
database.default.database = lsp_scada_app_devel
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306

# Cache configuration (Redis recommended)
cache.handler = redis
cache.redis.host = 127.0.0.1
cache.redis.password = 
cache.redis.port = 6379
cache.redis.timeout = 0

# Security configuration
encryption.key = your-32-character-encryption-key
security.tokenRandomize = true
security.tokenName = csrf_token
security.headerName = X-CSRF-TOKEN
security.cookieName = csrf_cookie
security.expires = 7200
```

### Step 2: Database Migration

#### 2.1 Check Current Migration Status
```bash
cd /path/to/your/application
php spark migrate:status
```

#### 2.2 Run New Migration
```bash
# Run the observasi optimization migration
php spark migrate:latest

# Verify migration success
php spark migrate:status
```

#### 2.3 Verify Database Changes
```sql
-- Check if new indexes are created
SHOW INDEXES FROM observasi;
SHOW INDEXES FROM detail_observasi;

-- Verify stored procedures
SHOW PROCEDURE STATUS WHERE Db = 'lsp_scada_app_devel';

-- Check triggers
SHOW TRIGGERS LIKE 'observasi';
SHOW TRIGGERS LIKE 'detail_observasi';

-- Test stored procedure
CALL GetObservasiProgress(1, 1);
```

### Step 3: Application Files Deployment

#### 3.1 Upload Optimized Files
```bash
# Copy new files (ensure proper permissions)
cp app/Controllers/Api/Observasi.php /path/to/production/app/Controllers/Api/
cp app/Services/ObservasiService.php /path/to/production/app/Services/
cp app/Models/ObservasiModel.php /path/to/production/app/Models/
cp app/Requests/ObservasiRequest.php /path/to/production/app/Requests/

# Copy optimized frontend
cp app/Views/asesor/utility/ceklist-js-optimized.php /path/to/production/app/Views/asesor/utility/

# Set proper permissions
chown -R www-data:www-data /path/to/production/
chmod -R 755 /path/to/production/
chmod -R 777 /path/to/production/writable/
```

#### 3.2 Update Composer Dependencies
```bash
cd /path/to/production/
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize
```

### Step 4: Cache and Session Management

#### 4.1 Clear Existing Cache
```bash
# Clear application cache
php spark cache:clear

# Clear views cache
rm -rf writable/cache/views/*

# Clear database cache
rm -rf writable/cache/database/*

# Clear session files
rm -rf writable/session/*
```

#### 4.2 Configure Redis (if using)
```bash
# Test Redis connection
redis-cli ping

# Configure Redis for sessions
echo "session.handler = redis" >> .env
echo "session.redis.host = 127.0.0.1" >> .env
echo "session.redis.port = 6379" >> .env
```

### Step 5: Web Server Configuration

#### 5.1 Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/production/public
    
    <Directory /path/to/production/public>
        AllowOverride All
        Require all granted
        
        # Security headers
        Header always set X-Content-Type-Options nosniff
        Header always set X-Frame-Options DENY
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
        Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
    </Directory>
    
    # Gzip compression
    LoadModule deflate_module modules/mod_deflate.so
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
    
    # Browser caching
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/svg+xml "access plus 1 month"
    </IfModule>
</VirtualHost>
```

#### 5.2 Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/production/public;
    index index.php;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    
    # Static file caching
    location ~* \.(css|js|png|jpg|jpeg|gif|svg|ico)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_param PHP_VALUE "expose_php=0";
        fastcgi_hide_header X-Powered-By;
    }
    
    # CodeIgniter routing
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(app|system|writable|tests)/ {
        deny all;
    }
}
```

### Step 6: SSL/HTTPS Configuration

#### 6.1 Install SSL Certificate
```bash
# Using Let's Encrypt
certbot --apache -d your-domain.com

# Or for Nginx
certbot --nginx -d your-domain.com
```

#### 6.2 Force HTTPS Redirects
```apache
# Apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

```nginx
# Nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### Step 7: Performance Optimization

#### 7.1 OPcache Configuration
```ini
; php.ini optimizations
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

#### 7.2 MySQL Optimization
```sql
-- MySQL configuration optimizations
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB
SET GLOBAL innodb_log_buffer_size = 16777216; -- 16MB
SET GLOBAL max_connections = 200;
SET GLOBAL query_cache_size = 67108864; -- 64MB
```

### Step 8: Security Hardening

#### 8.1 File Permissions
```bash
# Set secure permissions
find /path/to/production -type f -exec chmod 644 {} \;
find /path/to/production -type d -exec chmod 755 {} \;
chmod -R 777 /path/to/production/writable/
chmod 600 /path/to/production/.env
```

#### 8.2 Hide Sensitive Information
```apache
# Apache - Hide CodeIgniter files
<Files "*.ini">
    Order allow,deny
    Deny from all
</Files>

<Directory "*/app">
    Order allow,deny
    Deny from all
</Directory>

<Directory "*/system">
    Order allow,deny
    Deny from all
</Directory>

<Directory "*/writable">
    Order allow,deny
    Deny from all
</Directory>
```

### Step 9: Testing and Verification

#### 9.1 Functional Testing
```bash
# Test API endpoints
curl -X GET "https://your-domain.com/api/observasi/load?id_skema=1&id_asesmen=1&id_asesi=1"
curl -X POST "https://your-domain.com/api/observasi/single" -H "Content-Type: application/json" -d '{"test":"data"}'

# Test frontend
curl -I https://your-domain.com/asesor/observasi

# Check database connectivity
php spark observasi:test-connection
```

#### 9.2 Performance Testing
```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://your-domain.com/api/observasi/load?id_skema=1&id_asesmen=1&id_asesi=1

# Database performance test
mysql -u username -p -e "
SELECT 
    BENCHMARK(1000, (
        SELECT COUNT(*) 
        FROM observasi o 
        JOIN detail_observasi d ON o.id_observasi = d.id_observasi 
        WHERE o.id_asesmen = 1
    ))
" lsp_scada_app_devel
```

#### 9.3 Security Testing
```bash
# SSL/TLS verification
nmap --script ssl-enum-ciphers -p 443 your-domain.com

# SQL injection testing (use sqlmap carefully)
sqlmap -u "https://your-domain.com/api/observasi/load?id_skema=1" --batch --level=1

# XSS testing
curl -X POST "https://your-domain.com/api/observasi/single" \
  -H "Content-Type: application/json" \
  -d '{"keterangan":"<script>alert(\"xss\")</script>"}'
```

### Step 10: Monitoring Setup

#### 10.1 Application Monitoring
```bash
# Install monitoring tools
composer require --dev phpunit/phpunit
composer require kint-php/kint

# Set up log monitoring
tail -f /path/to/production/writable/logs/log-$(date +%Y-%m-%d).log
```

#### 10.2 Database Monitoring
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';

-- Monitor performance
SHOW PROCESSLIST;
SHOW ENGINE INNODB STATUS;
```

#### 10.3 System Monitoring
```bash
# CPU and memory monitoring
htop
iostat 1
free -h

# Disk space monitoring
df -h
du -sh /path/to/production/
```

## Post-deployment Tasks

### 1. Remove Maintenance Mode
```bash
rm public/.maintenance
```

### 2. User Acceptance Testing
- Test all observasi workflows
- Verify data integrity
- Check performance improvements
- Validate security features

### 3. Documentation Updates
- Update API documentation
- Refresh user manuals
- Update troubleshooting guides

### 4. Training
- Train users on new features
- Update training materials
- Create video tutorials

## Rollback Procedures

### Emergency Rollback
```bash
# 1. Enable maintenance mode
touch public/.maintenance

# 2. Restore database
mysql -u username -p lsp_scada_app_devel < backup_YYYYMMDD_HHMMSS.sql

# 3. Restore application files
tar -xzf app_backup_YYYYMMDD_HHMMSS.tar.gz -C /path/to/production/

# 4. Clear cache
php spark cache:clear

# 5. Restart web server
systemctl restart apache2  # or nginx

# 6. Remove maintenance mode
rm public/.maintenance
```

### Gradual Rollback
1. Route traffic to old system
2. Verify data consistency
3. Plan re-deployment
4. Update migration strategy

## Troubleshooting

### Common Issues

#### Database Connection Errors
```bash
# Check MySQL service
systemctl status mysql

# Test connection
mysql -u username -p -e "SELECT 1"

# Check PHP MySQL extension
php -m | grep mysql
```

#### Permission Issues
```bash
# Fix file permissions
chown -R www-data:www-data /path/to/production/
chmod -R 755 /path/to/production/
chmod -R 777 /path/to/production/writable/
```

#### Cache Issues
```bash
# Clear all caches
php spark cache:clear
rm -rf writable/cache/*
rm -rf writable/session/*

# Restart Redis
systemctl restart redis
```

#### Performance Issues
```bash
# Check system resources
top
free -h
df -h

# Monitor database
SHOW PROCESSLIST;
SHOW ENGINE INNODB STATUS;

# Check slow queries
tail -f /var/log/mysql/slow.log
```

## Success Metrics

### Performance Targets
- Page load time: < 2 seconds
- API response time: < 500ms
- Database query time: < 200ms
- 99.9% uptime

### Security Targets
- Zero SQL injection vulnerabilities
- Zero XSS vulnerabilities
- All communications over HTTPS
- Regular security audits

### User Experience
- 95%+ user satisfaction
- < 1% error rate
- Intuitive interface
- Mobile responsiveness

## Support and Maintenance

### Regular Maintenance Tasks
- Weekly: Check logs, monitor performance
- Monthly: Update dependencies, security patches
- Quarterly: Performance optimization review
- Annually: Complete security audit

### Support Contacts
- Development Team: dev@yourcompany.com
- System Administrator: admin@yourcompany.com
- Emergency Contact: +62-xxx-xxxx-xxxx

### Documentation
- Technical Documentation: `/docs/technical/`
- User Manual: `/docs/user/`
- API Documentation: `/docs/api/`
- Troubleshooting Guide: `/docs/troubleshooting/`
