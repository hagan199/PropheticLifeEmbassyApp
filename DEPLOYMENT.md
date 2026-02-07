# Prophetic Life Embassy - Production Deployment Guide

## 📋 Table of Contents
1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Docker Production Deployment](#docker-production-deployment)
4. [Database Migrations](#database-migrations)
5. [Verification Tests](#verification-tests)
6. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software
- **Docker** (20.10+) and **Docker Compose** (2.0+)
- **Git** (for version control)
- **PostgreSQL Client** (optional, for database management)

### Required Environment Variables
Create a `.env.production` file based on `.env.production.example`:

```bash
# Copy example file
cp .env.production.example .env.production

# Generate Laravel application key
docker-compose -f docker-compose.prod.yml run --rm backend php artisan key:generate --show
```

**Critical Variables to Set:**
- `APP_KEY` - Laravel encryption key (generated above)
- `DB_DATABASE` - Database name (e.g., `ple_cms`)
- `DB_USERNAME` - Database user (e.g., `ple_user`)
- `DB_PASSWORD` - **Strong password** (minimum 16 characters)
- `REDIS_PASSWORD` - **Strong password** for Redis
- `APP_URL` - Your production domain (e.g., `https://cms.pleapp.com`)
- `VITE_API_URL` - API URL for frontend (e.g., `https://cms.pleapp.com/api`)

---

## Environment Setup

### 1. Clone Repository
```bash
git clone https://github.com/your-org/PropheticLifeEmbassyApp.git
cd PropheticLifeEmbassyApp
```

### 2. Configure Environment
```bash
# Backend environment
cp .env.production.example .env.production
nano .env.production  # Edit with your values

# Frontend environment
cp frontend/.env.example frontend/.env
nano frontend/.env  # Set VITE_API_URL to your production API
```

### 3. Set Permissions (Linux/Mac)
```bash
chmod +x backend/docker-entrypoint-production.sh
chmod 755 backend/storage
chmod 755 backend/bootstrap/cache
```

---

## Docker Production Deployment

### Build and Start Services

```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start all services
docker-compose -f docker-compose.prod.yml up -d

# Check service health
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f
```

**Expected Output:**
```
NAME                STATUS              PORTS
ple-backend         running (healthy)
ple-backend-nginx   running (healthy)   0.0.0.0:8000->80/tcp
ple-db              running (healthy)   0.0.0.0:5432->5432/tcp
ple-frontend        running (healthy)   0.0.0.0:3000->80/tcp
ple-queue-worker    running
ple-redis           running (healthy)   0.0.0.0:6379->6379/tcp
```

### Health Check Verification

```bash
# Backend health
curl http://localhost:8000/health
# Expected: "healthy"

# Frontend health
curl http://localhost:3000
# Expected: Vue.js app HTML

# Database health
docker-compose -f docker-compose.prod.yml exec db pg_isready -U ple_user
# Expected: "accepting connections"

# Redis health
docker-compose -f docker-compose.prod.yml exec redis redis-cli -a YOUR_REDIS_PASSWORD ping
# Expected: "PONG"
```

---

## Database Migrations

### Run Migrations

Migrations run automatically via `docker-entrypoint-production.sh`, but you can run them manually:

```bash
# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force

# Seed database (first time only)
docker-compose -f docker-compose.prod.yml exec backend php artisan db:seed --force
```

### Migration Status
```bash
# Check migration status
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate:status
```

**New Migration Added:**
- `2026_02_08_000001_create_minister_unit_attendance_table.php` - Ministry unit attendance tracking

### Seeded Data

The `RoleAndPermissionSeeder` creates:
- **6 Roles**: admin, pastor, usher, finance, pr_follow_up, department_leader
- **20 Permissions** across 8 modules (users, attendance, contributions, etc.)

---

## Verification Tests

### 1. Test Authentication

```bash
# Test login endpoint
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+233200000001",
    "password": "password"
  }'

# Expected: { "success": true, "token": "...", "user": {...} }
```

### 2. Test Permissions (Laravel Tinker)

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan tinker
```

```php
// In Tinker:
$admin = User::where('role', 'admin')->first();
$usher = User::where('role', 'usher')->first();

// Test Gates
Gate::allows('users.delete', $admin);  // Should return true
Gate::allows('users.delete', $usher);  // Should return false
Gate::allows('attendance.create', $usher);  // Should return true
```

### 3. Test API Endpoints

```bash
# Get users (requires admin permission)
curl http://localhost:8000/api/users \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected for admin: 200 OK with user list
# Expected for usher: 403 Forbidden
```

### 4. Test Attendance Import

Create test CSV file:
```bash
cat > test_attendance.csv << 'EOF'
member_name,service_type,service_date,present
John Doe,Sunday,2026-02-09,1
Jane Smith,Sunday,2026-02-09,1
EOF
```

Import via API:
```bash
curl -X POST http://localhost:8000/api/attendance/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test_attendance.csv" \
  -F "auto_approve=false"

# Expected: {"success": true, "data": {"success": 2, "failed": 0}}
```

### 5. Test Report Generation

```bash
# Generate financial report
curl "http://localhost:8000/api/finance/reports/monthly?month=2026-02" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Export as PDF
curl "http://localhost:8000/api/finance/reports/export?format=pdf&month=2026-02" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected: {"success": true, "download_url": "http://localhost:8000/exports/financial_report_2026-02.pdf"}
```

### 6. Test Code Review (Local)

```bash
# Run code quality checks
docker-compose -f docker-compose.prod.yml exec backend php artisan code:review

# Expected:
# 1. PHPStan: ✓ No errors found
# 2. Laravel Pint: ✓ Code style check passed
# 3. Custom checks: ✓ No issues found
```

---

## Performance Optimization

### Cache Warmup
```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan route:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan view:cache
```

### Queue Worker
The queue worker runs automatically in the `ple-queue-worker` container. Monitor it:

```bash
# View queue worker logs
docker-compose -f docker-compose.prod.yml logs -f queue-worker

# Check failed jobs
docker-compose -f docker-compose.prod.yml exec backend php artisan queue:failed
```

---

## Troubleshooting

### Service Not Starting

**Check logs:**
```bash
docker-compose -f docker-compose.prod.yml logs backend
docker-compose -f docker-compose.prod.yml logs db
```

**Common issues:**
1. **Missing environment variables** - Check `.env.production` has all required vars
2. **Database connection failed** - Verify DB credentials and wait for health check
3. **Permission denied** - Run `chmod 755 backend/storage backend/bootstrap/cache`

### Health Check Failures

```bash
# Check service health details
docker inspect ple-backend --format='{{json .State.Health}}'

# Restart unhealthy service
docker-compose -f docker-compose.prod.yml restart backend
```

### Database Connection Errors

```bash
# Test PostgreSQL connection
docker-compose -f docker-compose.prod.yml exec db psql -U ple_user -d ple_cms -c '\conninfo'

# Check database logs
docker-compose -f docker-compose.prod.yml logs db | tail -50
```

### Clear All Caches

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan config:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan route:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan view:clear
```

### Reset and Rebuild

```bash
# Stop all services
docker-compose -f docker-compose.prod.yml down

# Remove volumes (⚠️ DELETES ALL DATA)
docker-compose -f docker-compose.prod.yml down -v

# Rebuild and restart
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d
```

---

## Security Checklist

- [ ] Changed default database credentials
- [ ] Set strong Redis password
- [ ] Generated unique `APP_KEY`
- [ ] Configured `SANCTUM_STATEFUL_DOMAINS` for your domain
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configured HTTPS/SSL (not included in this Docker setup)
- [ ] Set up firewall rules (ports 22, 80, 443 only)
- [ ] Regular database backups configured
- [ ] Monitoring and alerting set up

---

## Backup and Restore

### Backup Database
```bash
docker-compose -f docker-compose.prod.yml exec db pg_dump -U ple_user ple_cms > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
cat backup_20260207.sql | docker-compose -f docker-compose.prod.yml exec -T db psql -U ple_user ple_cms
```

---

## Monitoring

### View Resource Usage
```bash
docker stats ple-backend ple-db ple-redis ple-frontend ple-queue-worker
```

### Application Logs
```bash
# Laravel logs
docker-compose -f docker-compose.prod.yml exec backend tail -f storage/logs/laravel.log

# Nginx access logs
docker-compose -f docker-compose.prod.yml logs -f backend-nginx
```

---

## Next Steps

1. **Domain Configuration**: Point your domain DNS to the server IP
2. **SSL/TLS Setup**: Configure Let's Encrypt with Certbot or use a reverse proxy (Traefik, Nginx Proxy)
3. **CI/CD**: Push to GitHub to trigger automated code review workflow
4. **Monitoring**: Set up Sentry, New Relic, or Laravel Telescope for production monitoring
5. **Backups**: Configure automated daily database backups

---

## Support

For issues or questions:
- **GitHub Issues**: [Create an issue](https://github.com/your-org/PropheticLifeEmbassyApp/issues)
- **Documentation**: Check `/docs` folder for detailed API docs
- **Email**: support@pleapp.com
