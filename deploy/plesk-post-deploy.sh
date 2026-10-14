#!/bin/bash
# Optional Plesk Git "Additional deployment actions" script.
# In Plesk → Git → Repository settings → Enable additional deployment actions,
# paste or point to this script so each pull prepares runtime dirs.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

mkdir -p storage/rate_limits
chmod 750 storage storage/rate_limits 2>/dev/null || true

if [[ ! -f .env ]]; then
  echo "WARNING: .env is missing. Copy .env.example to .env and fill DB + Turnstile values."
fi

# Keep document root on public/ in Plesk (Domains → Hosting Settings).
echo "Post-deploy ready. Document root should be: ${ROOT}/public"
