#!/bin/sh
# =============================================================
# E-Server Streaming Radio — Entrypoint
# =============================================================

# Limpiar configuraciones previas en conf.d (evita duplicados)
rm -f /etc/nginx/conf.d/*.conf

echo "=============================================="
echo "  E-Server Streaming Radio — ACTIVO PERMANENTE"
echo "  Sin bloqueos de licencia."
echo "=============================================="

# Activar siempre el sitio correcto
cp /etc/nginx/templates/nginx_site.conf /etc/nginx/conf.d/active.conf

exec /docker-entrypoint.sh "$@"

