#!/bin/sh
# =============================================================
# E-Server Streaming Radio — Entrypoint con control de expiración
# Fecha de vencimiento: 2027-05-28 23:59:59
#
# ARQUITECTURA:
#   - Las plantillas viven en /etc/nginx/templates/ (NO en conf.d/)
#   - El entrypoint copia la correcta a /etc/nginx/conf.d/active.conf
#   - nginx.conf raíz incluye conf.d/*.conf (solo active.conf estará ahí)
# =============================================================

EXPIRY_EPOCH=1780099199   # 2027-05-28 23:59:59 UTC
NOW_EPOCH=$(date -u +%s)

# Limpiar configuraciones previas en conf.d (evita duplicados)
rm -f /etc/nginx/conf.d/*.conf

if [ "$NOW_EPOCH" -ge "$EXPIRY_EPOCH" ]; then
    echo "=============================================="
    echo "  E-SERVER: LICENCIA VENCIDA (2027-05-28)"
    echo "  Este servidor no está autorizado para operar."
    echo "=============================================="
    cp /etc/nginx/templates/nginx_expired_site.conf /etc/nginx/conf.d/active.conf
else
    DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
    echo "=============================================="
    echo "  E-Server Streaming Radio — ACTIVO"
    echo "  Días restantes de licencia: $DAYS_LEFT"
    echo "  Vence: 2027-05-28 23:59"
    echo "=============================================="
    cp /etc/nginx/templates/nginx_site.conf /etc/nginx/conf.d/active.conf
fi

exec /docker-entrypoint.sh "$@"
