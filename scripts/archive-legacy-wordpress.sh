#!/usr/bin/env bash
set -euo pipefail

DOMAIN_ROOT="${HOME}/domains/enkaf.sa"
BACKUP_ROOT="${DOMAIN_ROOT}/enkaf-backups"
LEGACY_ROOT="${HOME}/legacy-archives/enkaf"
SOURCE_TAR="${1:-${BACKUP_ROOT}/before-9564d10852ef-20260818-190802.tar.gz}"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="${DOMAIN_ROOT}/.legacy-archive-${STAMP}"
ZIP="${LEGACY_ROOT}/enkaf-wordpress-legacy-${STAMP}.zip"
DB_STATUS="not-found"

if [ ! -f "$SOURCE_TAR" ]; then
  echo "ENKAF_LEGACY_ARCHIVE_ERROR source_backup_missing"
  exit 2
fi
if ! command -v zip >/dev/null 2>&1 || ! command -v unzip >/dev/null 2>&1; then
  echo "ENKAF_LEGACY_ARCHIVE_ERROR zip_tools_missing"
  exit 2
fi

mkdir -p "$LEGACY_ROOT" "$WORK"
chmod 700 "$LEGACY_ROOT" || true
trap 'rm -rf "$WORK"' EXIT

tar -xzf "$SOURCE_TAR" -C "$WORK"

WP_CONFIG="$WORK/public_html/wp-config.php"
if [ -f "$WP_CONFIG" ]; then
  DB_NAME="$(sed -n "s/.*define( *['\"]DB_NAME['\"] *, *['\"]\([^'\"]*\)['\"] *).*/\1/p" "$WP_CONFIG" | head -n 1)"
  DB_USER="$(sed -n "s/.*define( *['\"]DB_USER['\"] *, *['\"]\([^'\"]*\)['\"] *).*/\1/p" "$WP_CONFIG" | head -n 1)"
  DB_PASSWORD="$(sed -n "s/.*define( *['\"]DB_PASSWORD['\"] *, *['\"]\([^'\"]*\)['\"] *).*/\1/p" "$WP_CONFIG" | head -n 1)"
  DB_HOST="$(sed -n "s/.*define( *['\"]DB_HOST['\"] *, *['\"]\([^'\"]*\)['\"] *).*/\1/p" "$WP_CONFIG" | head -n 1)"
  DB_HOST="${DB_HOST:-localhost}"
  DB_HOST="${DB_HOST%%:*}"
  if command -v mysqldump >/dev/null 2>&1 && [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
    if mysqldump --single-transaction --skip-lock-tables --no-tablespaces -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$WORK/database.sql" 2>"$WORK/database-dump-error.txt"; then
      DB_STATUS="included"
      rm -f "$WORK/database-dump-error.txt"
    else
      DB_STATUS="failed"
      rm -f "$WORK/database.sql"
    fi
  else
    DB_STATUS="unavailable"
  fi
fi

cat > "$WORK/LEGACY-ARCHIVE-MANIFEST.txt" <<EOF
Client: ENKAF / إنكاف
Purpose: Legacy WordPress preservation only; not a production source.
Created: $(date -u +%Y-%m-%dT%H:%M:%SZ)
Source backup: $(basename "$SOURCE_TAR")
Database dump: $DB_STATUS
Production source of truth: https://github.com/marketinghorizonssa-alt/Enkaf
EOF

(
  cd "$WORK"
  zip -qr "$ZIP" .
)
unzip -tq "$ZIP" >/dev/null
if [ ! -s "$ZIP" ]; then
  echo "ENKAF_LEGACY_ARCHIVE_ERROR empty_zip"
  exit 3
fi

SIZE="$(du -h "$ZIP" | awk '{print $1}')"
HASH="$(sha256sum "$ZIP" | awk '{print $1}')"
echo "ENKAF_LEGACY_ARCHIVE_OK path=$ZIP size=$SIZE db_dump=$DB_STATUS sha256=$HASH"
