# ARLS Ferraz - Production Deployment Guide

## The Golden Rule

⚠️ **NEVER execute on the HOST before Docker build:**

```bash
php artisan config:cache
php artisan optimize
php artisan route:cache
php artisan view:cache
```

**Why:** Production caches must be generated ONLY inside the container at runtime with the correct `.env` configuration loaded.

**When:** These commands are executed ONLY inside the running container:
1. After the new image is deployed
2. With `.env` production mounted
3. With `/var/www/html` as context
4. With storage volume mounted

## Incident Report: September 28, 2026

### Summary
Production deployment returned HTTP 500 after a successful Docker build.

### Root Cause
1. `php artisan config:cache` was executed on the HOST
2. Generated `bootstrap/cache/config.php` with absolute path: `/opt/arls-ferraz/producao/storage/logs`
3. `docker build COPY . .` included this contaminated file
4. Container FPM attempted to access `/opt/arls-ferraz/producao/storage/logs` (HOST path inside container)
5. Permission denied → Monolog StreamHandler error → HTTP 500

### Mechanism

```
Host execution: php artisan config:cache
                ↓
Generated: bootstrap/cache/config.php with /opt/arls-ferraz/producao paths
                ↓
Docker build: COPY . . (includes contaminated config.php)
                ↓
Container starts: FPM loads config.php with HOST paths
                ↓
Request arrives: tries to access /opt/arls-ferraz/producao → Permission denied
                ↓
HTTP 500
```

### Resolution

The issue was resolved with container restart:
```bash
docker restart arls-app
```

New PHP-FPM workers started fresh without the cached HOST paths, allowing proper initialization with `/var/www/html` paths.

### Structural Fix

To prevent recurrence:

1. **`.dockerignore`** was updated to exclude runtime cache files:
   - `bootstrap/cache/config.php`
   - `bootstrap/cache/routes*.php`
   - `bootstrap/cache/events.php`
   - `bootstrap/cache/packages.php`
   - `bootstrap/cache/services.php`

2. **Dockerfile** was kept simple - it does NOT execute Artisan cache commands. Instead, `deploy-production.sh` runs them at runtime.

3. **HOST cleanup**: All contaminated cache files were removed from the HOST before the next deployment.

### Lesson Learned

FPM workers maintain state in memory. Simply updating code or config files may not be reflected in running workers. A proper deployment strategy must ensure:
- New images are built without HOST contamination
- Containers are recreated (not just restarted)
- Cache generation happens after container starts with correct context
- Health checks verify the deployed state

## Deployment Strategy

### Before Deployment

**Ensure the HOST is clean:**
```bash
# These files should NOT exist before deployment
ls -la bootstrap/cache/config.php      # should fail
ls -la bootstrap/cache/packages.php    # should fail
ls -la bootstrap/cache/services.php    # should fail

# Only this should exist:
cat bootstrap/cache/.gitignore         # should show: * + !.gitignore
```

**Ensure ENV file permissions are correct:**
```bash
# Verify deploy/app.env has secure permissions
ls -la deploy/app.env

# Expected:
# -rw-r----- 1 root www-data ...
# (mode 640 - readable by www-data group, not world-readable)
```

The deployment script will validate this during preflight.

### Deployment Process

Use the automated script:
```bash
./deploy/deploy-production.sh
```

The script handles:
1. ✓ Deployment lock (prevents concurrent deployments)
2. ✓ Preflight validation:
   - Directory and file checks
   - Docker Compose v2 availability
   - Git repository status
   - **ENV file permissions and readability** (aborts if insecure)
3. ✓ Safe Git operations (fetch with explicit A-D case handling, no auto-pull)
4. ✓ Image backup with rollback tags (preserves current Image IDs)
5. ✓ Build new images (while old containers still running)
6. ✓ Image integrity verification (no HOST paths)
7. ✓ Container recreation with `--force-recreate` (no `docker compose down`)
8. ✓ Application readiness verification:
   - Container running check
   - PHP-FPM process verification (via `docker top`)
   - **HTTP response validation through Nginx** (more reliable than process checks)
9. ✓ Artisan cache generation IN RUNTIME:
   - `optimize:clear` (clears old caches)
   - `package:discover` (discovers Laravel packages from Composer)
   - `config:cache` (with correct `/var/www/html` context)
   - Path validation (ensures `/var/www/html`, aborts if `/opt/arls-ferraz/producao` found)
   - `route:cache` (with fallback to `route:clear` if incompatible)
   - `view:cache` (compiles Blade templates)
10. ✓ App container restart (loads fresh cache into new PHP-FPM workers)
11. ✓ Complete health checks (home, login, external, logs)
12. ✓ **Automatic rollback if ANY critical failure occurs after container recreation**

### Deployment Safety Features

- **Post-Recreate Rollback:** Any failure after containers are recreated automatically triggers rollback to previous images
- **Recursive Rollback Protection:** Script prevents rollback from attempting to rollback itself
- **Signal Handling:** Ctrl+C after recreate triggers automatic rollback (SIGINT/SIGTERM)
- **Immutable Lock:** Only one deployment can run at a time (flock-based)
- **Runtime-Only Caches:** All Laravel caches generated inside container with correct environment
- **Path Validation:** Deployment aborts if HOST paths leak into container caches
- **Health Checks with Timeouts:** HTTP checks include connection timeouts and retry logic

### After Deployment

Monitor logs:
```bash
docker logs -f arls-app
```

Verify externally:
```bash
curl https://arlsferrazdevasconcelos.com.br
```

## If Something Goes Wrong

### Automatic Rollback

The deploy script will **automatically attempt rollback** if health checks fail:

1. Captures logs of the failed deployment
2. Restores previous Image IDs using rollback tags
3. Recreates containers with `--force-recreate`
4. Re-validates health checks
5. If rollback succeeds, deployment terminates with full audit trail
6. If rollback fails, script stops with instructions for manual intervention

### Manual Rollback (if needed)

Using the rollback tag created by the deploy script:
```bash
docker tag arls-app-php:rollback-<timestamp> arls-app-php:latest
docker tag arls-app-web:rollback-<timestamp> arls-app-web:latest
docker compose -f deploy/compose.yaml up -d --force-recreate app web
sleep 3
curl http://127.0.0.1:9002/
```

**Note:** The script uses `--force-recreate` (not `docker compose down`), which ensures volumes are preserved.

## Cache File Locations

### Should NOT be in Docker Image
- `bootstrap/cache/config.php` ✗
- `bootstrap/cache/routes*.php` ✗
- `bootstrap/cache/events.php` ✗
- `bootstrap/cache/packages.php` ✗
- `bootstrap/cache/services.php` ✗

### SHOULD be in Docker Image (structural files)
- `bootstrap/cache/.gitignore` ✓

### Generated at Runtime (inside container)
- All above cache files are regenerated by Artisan commands inside the running container
- They persist in the volume between restarts
- They are cleared and regenerated on each deployment

## Important Notes

- **Never manually edit cache files**
- **Never commit cache files to Git** (.gitignore prevents this)
- **Never run Artisan commands on the HOST for production** (including migrate, optimize, etc)
- **Always use the deployment script** for production updates
- **Test in staging first** if possible
- **Monitor logs after deployment** for any path-related errors

## See Also

- `deploy/deploy-production.sh` - Automated deployment script
- `deploy/compose.yaml` - Service definitions
- `deploy/Dockerfile` - Multi-stage build configuration
- `.dockerignore` - Files excluded from Docker build context
