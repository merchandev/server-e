# Streaming Radio E-Server

Servidor Docker de streaming de radio para el dominio `diarioeloriental.com`.

> **⚠️ Licencia vence: 2027-05-28 23:59** — Después de esa fecha el servidor se bloquea automáticamente.
> **👥 Límite:** 1000 usuarios simultáneos.

## Arquitectura

```
[Encoder / Opticodec / Butt]
        │
        ▼  (push vía Icecast protocolo en puerto 8100)
  ┌─────────────────────┐
  │   Icecast (8100)    │  ← LÍMITE: 1000 clientes
  └──────┬──────────────┘
         │ (red interna Docker: eserver-internal)
  ┌──────▼──────────────┐
  │   Nginx (80)        │  ← verifica expiración en arranque
  └──────┬──────────────┘
         │ (red externa Docker: traefik-net)
  ┌──────▼──────┐
  │   Traefik   │  ← SSL automático Let's Encrypt
  └──────┬──────┘
         │
  radio.diarioeloriental.com (HTTPS)
```

## Mapa de puertos del VPS (sin conflictos)

| Servicio                    | Puerto host | Notas                               |
|-----------------------------|-------------|-------------------------------------|
| radio-streaming-server      | 8000        | Icecast – Sonora/Radio Monagas      |
| mv-streaming (TV)           | 1935        | RTMP – Monagas Visión TV            |
| **eserver-icecast** ✅ NUEVO | **8100**    | **Streaming Radio E-Server**        |

## Sistema de expiración (doble capa)

### 1. Capa servidor (Nginx entrypoint)
Al arrancar el contenedor `eserver-nginx`, el script `entrypoint.sh` verifica la fecha.
Si la fecha actual ≥ **2027-05-28 23:59:59 UTC**, activa `nginx_expired.conf` que devuelve **403** a todas las peticiones.

### 2. Capa cliente (JavaScript)
En `index.html` y `embed.html`, el JS verifica `new Date() >= new Date('2027-05-28T23:59:59')`:
- El botón de play queda **deshabilitado**
- Aparece un **banner de licencia vencida**
- Si hay audio reproduciéndose, se **corta inmediatamente**

## Deploy en el VPS (Hostinger)

### 1. Subir la carpeta al VPS

```bash
# Desde tu PC (PowerShell/CMD)
scp -r "C:\Users\merch\OneDrive\Escritorio\e" usuario@srv1212736.hstgr.cloud:/home/usuario/eserver-radio
```

### 2. En el VPS: construir y levantar

```bash
cd /home/usuario/eserver-radio
docker compose up -d --build
```

### 3. Verificar que corre

```bash
docker compose ps
docker logs eserver-icecast --tail 50
docker logs eserver-nginx --tail 30
```

### 4. Verificar que Icecast responde

```bash
curl http://localhost:8100/status.xsl
```

## Credenciales Icecast

| Campo            | Valor                  |
|------------------|------------------------|
| Source password  | `oriental2024stream`   |
| Admin user       | `admin`                |
| Admin password   | `oriental2024admin`    |
| Puerto Icecast   | `8100`                 |

> Puedes cambiar las contraseñas en `config/icecast.xml` antes del deploy.

## Configuración del Encoder (Opticodec / Butt / etc.)

| Campo        | Valor                              |
|--------------|------------------------------------|
| Host         | IP del VPS (srv1212736.hstgr.cloud)|
| Puerto       | `8100`                             |
| Password     | `oriental2024stream`               |
| Mountpoint   | `/radio.aac`                       |
| Formato      | AAC / MP3                          |

## URLs finales

- Player principal: `https://radio.diarioeloriental.com`
- Widget embebible: `https://radio.diarioeloriental.com/embed.html`
- Stream directo:   `https://radio.diarioeloriental.com/radio.aac`
- Admin Icecast:    `http://IP_VPS:8100/admin` (solo interno)

## DNS requerido

Añadir en el panel DNS de `diarioeloriental.com`:

```
radio    A    [IP_DEL_VPS]    (mismo IP que monagasvision.com)
```

## Requisito previo: Red traefik-net

La red `traefik-net` debe existir en Docker. Si no existe:

```bash
docker network create traefik-net
```
