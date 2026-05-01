#!/bin/sh
# =============================================================
# E-Server Streaming Radio — Entrypoint con control de expiración
# Fecha de vencimiento: 2027-05-28 23:59:59
#
# ARQUITECTURA CORRECTA:
#   - Los archivos de config viven en /etc/nginx/conf.d/
#   - El nginx.conf raíz (de la imagen base) incluye conf.d/*.conf
#   - En lugar de sobreescribir el nginx.conf raíz (peligroso),
#     activamos/desactivamos archivos dentro de conf.d/
# =============================================================

EXPIRY_EPOCH=1780099199   # 2027-05-28 23:59:59 UTC (epoch)
NOW_EPOCH=$(date -u +%s)

# Eliminar cualquier configuración activa previa
rm -f /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/active.conf

if [ "$NOW_EPOCH" -ge "$EXPIRY_EPOCH" ]; then
    echo "=============================================="
    echo "  E-SERVER: LICENCIA VENCIDA (2027-05-28)"
    echo "  Este servidor no está autorizado para operar."
    echo "=============================================="
    # Activar configuración de expiración (devuelve 403 a todo)
    cp /etc/nginx/conf.d/nginx_expired_site.conf /etc/nginx/conf.d/active.conf
else
    DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
    echo "=============================================="
    echo "  E-Server Streaming Radio — ACTIVO"
    echo "  Días restantes de licencia: $DAYS_LEFT"
    echo "  Vence: 2027-05-28 23:59"
    echo "=============================================="
    # Activar configuración normal
    cp /etc/nginx/conf.d/nginx_site.conf /etc/nginx/conf.d/active.conf
fi

exec /docker-entrypoint.sh "$@"
