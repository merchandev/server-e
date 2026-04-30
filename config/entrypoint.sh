#!/bin/sh
# =============================================================
# E-Server Streaming Radio — Entrypoint con control de expiración
# Fecha de vencimiento: 2027-05-28 23:59:59
# =============================================================

EXPIRY_EPOCH=1780099199   # 2027-05-28 23:59:59 UTC (epoch)
NOW_EPOCH=$(date -u +%s)

if [ "$NOW_EPOCH" -ge "$EXPIRY_EPOCH" ]; then
    echo "=============================================="
    echo "  E-SERVER: LICENCIA VENCIDA (2027-05-28)"
    echo "  Este servidor no está autorizado para operar."
    echo "=============================================="
    # Usar config de expiración que devuelve 403 a todos
    cp /etc/nginx/conf.d/nginx_expired.conf /etc/nginx/nginx.conf
else
    DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
    echo "=============================================="
    echo "  E-Server Streaming Radio — ACTIVO"
    echo "  Días restantes de licencia: $DAYS_LEFT"
    echo "  Vence: 2027-05-28 23:59"
    echo "=============================================="
    cp /etc/nginx/conf.d/nginx.conf /etc/nginx/nginx.conf
fi

exec /docker-entrypoint.sh "$@"
