#!/usr/bin/env bash
#
# ARLS Ferraz - Deployment Script
# Safe production deployment with health checks and rollback capability
# Usage: ./deploy/deploy-production.sh
#

set -Eeuo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Deployment metadata
DEPLOY_LOG="/tmp/arls-deploy-$(date +%s).log"
TIMESTAMP=$(date -u '+%Y-%m-%d %H:%M:%S UTC')
DEPLOY_START_ISO=$(date --iso-8601=seconds)
DEPLOYMENT_PHASE="preflight"

# Lock file for concurrent execution protection
LOCK_FILE="/var/run/arls-deploy.lock"

# ============================================================================
# CLEANUP & SIGNAL HANDLING
# ============================================================================

cleanup_on_exit() {
  local exit_code=$?
  if [ -f "$LOCK_FILE" ]; then
    rm -f "$LOCK_FILE" 2>/dev/null || true
  fi
  exit $exit_code
}

handle_interrupt() {
  echo -e "\n${YELLOW}⚠ DEPLOYMENT INTERRUPTED${NC}"
  echo "Phase: $DEPLOYMENT_PHASE"
  echo "Log saved to: $DEPLOY_LOG"

  if [ "$DEPLOYMENT_PHASE" = "preflight" ] || [ "$DEPLOYMENT_PHASE" = "build" ]; then
    log_warning "Production unchanged. Interrupted during safe phase."
  else
    log_warning "Production may be in intermediate state. Check logs and validate manually."
  fi
  exit 130
}

trap cleanup_on_exit EXIT
trap handle_interrupt SIGINT SIGTERM

# ============================================================================
# DEPLOYMENT LOCK
# ============================================================================

acquire_deployment_lock() {
  if ! mkdir -p "$(dirname "$LOCK_FILE")" 2>/dev/null; then
    LOCK_FILE="/tmp/arls-deploy.lock"
  fi

  exec 200>"$LOCK_FILE" 2>/dev/null || abort "Could not create lock file"

  if ! flock -n 200 2>/dev/null; then
    abort "Another deployment is already running. Lock file: $LOCK_FILE"
  fi
}

# ============================================================================
# LOGGING AND ERROR HANDLING
# ============================================================================

log() {
  echo "[$(date +'%H:%M:%S')] $*" | tee -a "$DEPLOY_LOG"
}

log_step() {
  echo -e "\n${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}" | tee -a "$DEPLOY_LOG"
  echo -e "${GREEN}→ $1${NC}" | tee -a "$DEPLOY_LOG"
  echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}" | tee -a "$DEPLOY_LOG"
}

log_error() {
  echo -e "${RED}✗ ERROR: $1${NC}" | tee -a "$DEPLOY_LOG"
}

log_success() {
  echo -e "${GREEN}✓ $1${NC}" | tee -a "$DEPLOY_LOG"
}

log_warning() {
  echo -e "${YELLOW}⚠ WARNING: $1${NC}" | tee -a "$DEPLOY_LOG"
}

abort() {
  log_error "$1"
  echo -e "\n${RED}DEPLOYMENT ABORTED${NC}"
  echo "Log saved to: $DEPLOY_LOG"
  exit 1
}

# ============================================================================
# HTTP CHECK WITH RETRY AND TIMEOUT
# ============================================================================

http_check() {
  local url="$1"
  local max_time="${2:-10}"
  local connect_timeout="${3:-5}"
  local retries="${4:-3}"
  local attempt=0

  while [ $attempt -lt $retries ]; do
    attempt=$((attempt + 1))
    local response=$(curl -s -o /dev/null -w "%{http_code}" \
      --connect-timeout "$connect_timeout" \
      --max-time "$max_time" \
      "$url" 2>/dev/null || echo "000")

    if [ "$response" != "000" ]; then
      echo "$response"
      return 0
    fi

    if [ $attempt -lt $retries ]; then
      sleep 1
    fi
  done

  echo "000"
}

# ============================================================================
# DOCKER COMPOSE WRAPPER
# ============================================================================

compose() {
  docker compose -f deploy/compose.yaml "$@"
}

# ============================================================================
# PREFLIGHT CHECKS
# ============================================================================

preflight_checks() {
  DEPLOYMENT_PHASE="preflight"
  log_step "Running preflight checks"

  # Check we're in the right directory
  if [ ! -f "deploy/compose.yaml" ]; then
    abort "deploy/compose.yaml not found. Please run from project root."
  fi
  log_success "In project root directory"

  # Check Docker is available
  if ! command -v docker &> /dev/null; then
    abort "Docker is not available"
  fi
  log_success "Docker available"

  # Check Docker Compose v2 is available
  if ! docker compose version &> /dev/null; then
    abort "Docker Compose v2 is not available. Install Docker with integrated compose plugin."
  fi
  log_success "Docker Compose v2 available"

  # Check deploy files exist
  if [ ! -f "deploy/Dockerfile" ]; then
    abort "deploy/Dockerfile not found"
  fi
  log_success "Dockerfile exists"

  if [ ! -f "deploy/app.env" ]; then
    abort "deploy/app.env not found"
  fi
  log_success "Production .env exists"

  # Check Git
  if ! git rev-parse --git-dir > /dev/null 2>&1; then
    abort "Not a Git repository"
  fi
  log_success "Git repository detected"

  # Check current branch
  CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
  if [ "$CURRENT_BRANCH" != "main" ] && [ "$CURRENT_BRANCH" != "master" ]; then
    log_warning "Current branch is '$CURRENT_BRANCH', not 'main'"
  fi

  # Check working tree clean
  if ! git diff-index --quiet HEAD --; then
    abort "Working tree has uncommitted changes. Please commit or stash."
  fi
  log_success "Working tree is clean"

  # Check for untracked files (optional warning)
  UNTRACKED=$(git ls-files --others --exclude-standard | wc -l)
  if [ "$UNTRACKED" -gt 0 ]; then
    log_warning "Found $UNTRACKED untracked files"
  fi

  # Test current endpoint
  RESPONSE=$(http_check "http://127.0.0.1:9002/" 5 3 1)
  if [ "$RESPONSE" = "200" ]; then
    log_success "Current endpoint responds (HTTP $RESPONSE)"
  else
    log_warning "Current endpoint does not respond (HTTP $RESPONSE)"
  fi
}

# ============================================================================
# GIT FETCH & VALIDATION
# ============================================================================

git_fetch_and_validate() {
  DEPLOYMENT_PHASE="git"
  log_step "Fetching and validating Git"

  LOCAL_SHA=$(git rev-parse HEAD)
  log "Current commit: $LOCAL_SHA"

  # Fetch remote-tracking refs only (no checkout of current branch)
  git fetch --prune origin 2>&1 | tee -a "$DEPLOY_LOG" || abort "Git fetch failed"

  # Determine synchronization state
  REMOTE_SHA=$(git rev-parse origin/main) || abort "Could not determine remote SHA"
  BASE_SHA=$(git merge-base HEAD origin/main) || abort "Could not determine merge base"

  log "Local:  $LOCAL_SHA"
  log "Remote: $REMOTE_SHA"
  log "Base:   $BASE_SHA"

  # CASE A: Already synchronized
  if [ "$LOCAL_SHA" = "$REMOTE_SHA" ]; then
    log_success "Already up to date (no new commits)"
    return 0
  fi

  # CASE B: Local is behind remote and fast-forward is possible
  if [ "$LOCAL_SHA" = "$BASE_SHA" ] && [ "$LOCAL_SHA" != "$REMOTE_SHA" ]; then
    log "Fast-forward merge available. Merging..."
    if ! git merge --ff-only origin/main 2>&1 | tee -a "$DEPLOY_LOG"; then
      abort "Could not fast-forward merge. Resolve history manually."
    fi

    NEW_SHA=$(git rev-parse HEAD)
    if [ "$NEW_SHA" != "$REMOTE_SHA" ]; then
      abort "Fast-forward merge resulted in unexpected SHA: $NEW_SHA vs $REMOTE_SHA"
    fi
    log_success "Fast-forwarded to: $NEW_SHA"
    return 0
  fi

  # CASE C: Local is ahead of remote
  if [ "$REMOTE_SHA" = "$BASE_SHA" ] && [ "$LOCAL_SHA" != "$REMOTE_SHA" ]; then
    abort "Local branch is ahead of remote. Cannot auto-push. Resolve manually."
  fi

  # CASE D: Branches diverged
  abort "Local and remote branches have diverged (mergebase differs from both). Cannot proceed."
}

# ============================================================================
# IDENTIFY CURRENT IMAGES & CREATE ROLLBACK TAGS
# ============================================================================

identify_and_backup_images() {
  log_step "Identifying current images for rollback"

  # Get image IDs currently in use
  APP_IMAGE_ID=$(docker inspect --type=image arls-app-php:latest -f '{{.ID}}' 2>/dev/null || echo "none")
  WEB_IMAGE_ID=$(docker inspect --type=image arls-app-web:latest -f '{{.ID}}' 2>/dev/null || echo "none")

  log "Current app image ID: $APP_IMAGE_ID"
  log "Current web image ID: $WEB_IMAGE_ID"

  # Create rollback tags with timestamp
  ROLLBACK_TAG="rollback-$(date +%s)"

  if [ "$APP_IMAGE_ID" != "none" ]; then
    docker tag arls-app-php:latest "arls-app-php:$ROLLBACK_TAG" || log_warning "Could not tag app image for rollback"
    log_success "Tagged app image as: arls-app-php:$ROLLBACK_TAG"
  fi

  if [ "$WEB_IMAGE_ID" != "none" ]; then
    docker tag arls-app-web:latest "arls-app-web:$ROLLBACK_TAG" || log_warning "Could not tag web image for rollback"
    log_success "Tagged web image as: arls-app-web:$ROLLBACK_TAG"
  fi

  export ROLLBACK_TAG
}

# ============================================================================
# BUILD IMAGES
# ============================================================================

build_images() {
  DEPLOYMENT_PHASE="build"
  log_step "Building Docker images"

  log "Building app image (target: php)..."
  if ! docker build -t arls-app-php:latest \
    -f deploy/Dockerfile \
    --target php . 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "Failed to build app image"
  fi
  log_success "App image built"

  log "Building web image (target: web)..."
  if ! docker build -t arls-app-web:latest \
    -f deploy/Dockerfile \
    --target web . 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "Failed to build web image"
  fi
  log_success "Web image built"
}

# ============================================================================
# VERIFY NEW IMAGE INTEGRITY
# ============================================================================

verify_image_integrity() {
  log_step "Verifying new image does not contain HOST paths"

  # Check if new image has contaminated bootstrap/cache
  TEMP_CONTAINER=$(docker create arls-app-php:latest)
  trap "docker rm -f $TEMP_CONTAINER > /dev/null 2>&1 || true" RETURN

  log "Checking for contaminated config.php..."

  if docker cp "$TEMP_CONTAINER:/var/www/html/bootstrap/cache/config.php" "/tmp/config_check.php" 2>/dev/null; then
    if grep -q "/opt/arls-ferraz/producao" "/tmp/config_check.php"; then
      abort "New image contains contaminated config.php with /opt/arls-ferraz/producao path"
    fi
    log_success "config.php clean (no HOST paths detected)"
    rm -f "/tmp/config_check.php"
  else
    log_success "No config.php in image (will be generated at runtime)"
  fi
}

# ============================================================================
# RECREATE CONTAINERS
# ============================================================================

recreate_containers() {
  DEPLOYMENT_PHASE="recreate"
  log_step "Recreating ARLS containers with --force-recreate"

  log "Forcing recreation of app and web services..."
  if ! compose up -d --force-recreate app web 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "Failed to recreate containers"
  fi
  log_success "Containers recreated"
}

# ============================================================================
# WAIT FOR PHP-FPM
# ============================================================================

wait_for_php_fpm() {
  log_step "Waiting for PHP-FPM to be ready"

  MAX_ATTEMPTS=30
  ATTEMPT=0

  while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if docker exec arls-app sh -c 'ps aux | grep -q [p]hp-fpm' 2>/dev/null; then
      log_success "PHP-FPM is running"
      return 0
    fi

    ATTEMPT=$((ATTEMPT + 1))
    echo -n "."
    sleep 1
  done

  abort "PHP-FPM did not start after $MAX_ATTEMPTS attempts"
}

# ============================================================================
# ARTISAN COMMANDS - ONLY AFTER CONTAINER & ENV ARE READY
# ============================================================================

run_artisan_commands() {
  DEPLOYMENT_PHASE="artisan"
  log_step "Running Artisan cache commands (NOW with correct .env)"

  log "Clearing old caches..."
  if ! docker exec arls-app php /var/www/html/artisan optimize:clear 2>&1 | tee -a "$DEPLOY_LOG"; then
    log_warning "optimize:clear had issues, continuing..."
  fi

  log "Running package discovery..."
  if ! docker exec arls-app php /var/www/html/artisan package:discover --ansi 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "package:discover failed"
  fi
  log_success "Package discovery completed"

  log "Generating config cache..."
  if ! docker exec arls-app php /var/www/html/artisan config:cache 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "config:cache failed"
  fi
  log_success "Config cache generated"

  # Validate paths in new cache
  log "Validating cached paths..."
  VALIDATION=$(docker exec arls-app php -r "
    require '/var/www/html/vendor/autoload.php';
    \$app = require '/var/www/html/bootstrap/app.php';
    echo 'base_path: ' . \$app->basePath() . PHP_EOL;
    echo 'storage_path: ' . \$app->storagePath() . PHP_EOL;
  " 2>&1)

  echo "$VALIDATION" | tee -a "$DEPLOY_LOG"

  if echo "$VALIDATION" | grep -q "/opt/arls-ferraz/producao"; then
    abort "CRITICAL: Cached paths still contain /opt/arls-ferraz/producao"
  fi
  log_success "Paths validated: /var/www/html"

  log "Clearing route cache if exists..."
  docker exec arls-app php /var/www/html/artisan route:clear 2>&1 | tee -a "$DEPLOY_LOG" || true

  log "Attempting route cache..."
  if docker exec arls-app php /var/www/html/artisan route:cache 2>&1 | tee -a "$DEPLOY_LOG"; then
    log_success "Route cache generated"
  else
    log_warning "route:cache failed (may have dynamic routes, proceeding without)"
  fi

  log "Generating view cache..."
  if ! docker exec arls-app php /var/www/html/artisan view:cache 2>&1 | tee -a "$DEPLOY_LOG"; then
    log_warning "view:cache had issues, continuing..."
  fi
}

# ============================================================================
# RESTART PHP-FPM TO LOAD FRESH CACHE
# ============================================================================

restart_app_container() {
  log_step "Restarting app container to load fresh cache"

  docker restart arls-app || abort "Failed to restart arls-app"
  sleep 2

  if ! docker exec arls-app sh -c 'ps aux | grep -q [p]hp-fpm' 2>/dev/null; then
    abort "PHP-FPM did not restart properly"
  fi
  log_success "arls-app restarted with fresh cache"
}

# ============================================================================
# HEALTH CHECKS
# ============================================================================

run_health_checks() {
  DEPLOYMENT_PHASE="healthcheck"
  log_step "Running health checks"

  # Check container status
  if ! docker ps | grep -q arls-app; then
    abort "Container arls-app is not running"
  fi
  log_success "Container arls-app is running"

  if ! docker ps | grep -q arls-web; then
    abort "Container arls-web is not running"
  fi
  log_success "Container arls-web is running"

  # Check internal home page
  log "Testing internal home page (127.0.0.1:9002/)..."
  RESPONSE=$(http_check "http://127.0.0.1:9002/" 10 5 3)

  if [ "$RESPONSE" = "200" ]; then
    log_success "Home page: HTTP $RESPONSE"
  else
    abort "Home page returned HTTP $RESPONSE (expected 200)"
  fi

  # Check internal login page
  log "Testing internal login page..."
  LOGIN_RESPONSE=$(http_check "http://127.0.0.1:9002/login" 10 5 2)

  if [ "$LOGIN_RESPONSE" = "200" ] || [ "$LOGIN_RESPONSE" = "302" ]; then
    log_success "Login page: HTTP $LOGIN_RESPONSE"
  else
    log_warning "Login page returned HTTP $LOGIN_RESPONSE"
  fi

  # Check external endpoint
  log "Testing external endpoint (https://arlsferrazdevasconcelos.com.br/)..."
  EXT_RESPONSE=$(http_check "https://arlsferrazdevasconcelos.com.br/" 10 5 2)

  if [ "$EXT_RESPONSE" = "200" ]; then
    log_success "External endpoint: HTTP $EXT_RESPONSE"
  elif [ "$EXT_RESPONSE" = "000" ]; then
    log_warning "Could not reach external endpoint (DNS/network issue, not a deploy failure)"
  elif [ "$EXT_RESPONSE" = "500" ] || [ "$EXT_RESPONSE" = "502" ] || [ "$EXT_RESPONSE" = "503" ]; then
    abort "External endpoint returned HTTP $EXT_RESPONSE (server error)"
  else
    log_warning "External endpoint returned HTTP $EXT_RESPONSE"
  fi

  # Check logs for NEW errors with /opt path
  log "Checking container logs for errors..."
  NEW_LOG_ENTRIES=$(docker logs --since "$DEPLOY_START_ISO" arls-app 2>&1 | wc -l)

  if docker logs --since "$DEPLOY_START_ISO" arls-app 2>&1 | grep -i "/opt/arls-ferraz/producao" > /dev/null 2>&1; then
    abort "CRITICAL: Found /opt/arls-ferraz/producao in NEW logs after deployment started"
  fi
  log_success "No HOST path references in new logs"
  log "Processed $NEW_LOG_ENTRIES new log entries"
}

# ============================================================================
# ROLLBACK FUNCTION
# ============================================================================

rollback_deployment() {
  log_step "ROLLING BACK to previous images"

  if [ -z "${ROLLBACK_TAG:-}" ]; then
    abort "No rollback tag available. Manual intervention required."
  fi

  log "Restoring previous image tags..."
  docker tag "arls-app-php:$ROLLBACK_TAG" arls-app-php:latest || log_warning "Could not restore app image"
  docker tag "arls-app-web:$ROLLBACK_TAG" arls-app-web:latest || log_warning "Could not restore web image"

  log "Recreating containers with previous images..."
  if ! compose up -d --force-recreate app web 2>&1 | tee -a "$DEPLOY_LOG"; then
    abort "Rollback failed to recreate containers"
  fi

  sleep 3

  log "Verifying rollback..."
  RESPONSE=$(http_check "http://127.0.0.1:9002/" 10 5 3)
  if [ "$RESPONSE" = "200" ]; then
    log_success "Rollback successful - application responding"
  else
    abort "Rollback verification failed (HTTP $RESPONSE)"
  fi
}

# ============================================================================
# GENERATE DEPLOYMENT REPORT
# ============================================================================

generate_report() {
  log_step "Generating deployment report"

  FINAL_SHA=$(git rev-parse HEAD)

  cat >> "$DEPLOY_LOG" << EOF

================================================================================
DEPLOYMENT REPORT
================================================================================
Timestamp:        $TIMESTAMP
Log file:         $DEPLOY_LOG

Git Information:
  Previous SHA:   $OLD_SHA
  Current SHA:    $FINAL_SHA
  Branch:         $CURRENT_BRANCH

Deployment Result: SUCCESS

Rollback Information:
  Tag:            ${ROLLBACK_TAG:-unknown}

Next Steps:
  Monitor application logs: docker logs -f arls-app
  Check health: curl https://arlsferrazdevasconcelos.com.br

Rollback (if needed):
  docker tag arls-app-php:${ROLLBACK_TAG} arls-app-php:latest
  docker tag arls-app-web:${ROLLBACK_TAG} arls-app-web:latest
  docker compose -f deploy/compose.yaml up -d --force-recreate app web

================================================================================
EOF

  log_success "Report saved to: $DEPLOY_LOG"
  cat "$DEPLOY_LOG"
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

main() {
  acquire_deployment_lock

  log_step "ARLS Ferraz Production Deployment"
  log "Start time: $TIMESTAMP"
  log "Log file: $DEPLOY_LOG"

  preflight_checks
  git_fetch_and_validate
  identify_and_backup_images
  build_images
  verify_image_integrity
  recreate_containers
  wait_for_php_fpm
  run_artisan_commands
  restart_app_container

  if ! run_health_checks; then
    log_error "Health checks failed"
    log "Attempting rollback..."
    rollback_deployment
    abort "Deploy failed, rollback completed"
  fi

  DEPLOYMENT_PHASE="completed"
  log_success "All health checks passed"
  generate_report

  echo -e "\n${GREEN}✓ DEPLOYMENT COMPLETED SUCCESSFULLY${NC}"
  exit 0
}

# Run main function
main "$@"
