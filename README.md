# Streaming Radio E-Server

Servidor Docker de streaming de radio.

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
  │   Nginx (80)        │
  └──────┬──────────────┘
         │ (red externa Docker: traefik-net)
  ┌──────▼──────┐
  │   Traefik   │  ← SSL automático Let's Encrypt
  └──────┬──────┘
         │
  radio.tudominio.com (HTTPS)
```

## Mapa de puertos del VPS (sin conflictos)

| Servicio                    | Puerto host | Notas                               |
|-----------------------------|-------------|-------------------------------------|
| radio-streaming-server      | 8000        | Icecast                             |
| mv-streaming (TV)           | 1935        | RTMP                                |
| **eserver-icecast** ✅ NUEVO | **8100**    | **Streaming Radio E-Server**        |

## Deploy en el VPS (Hostinger)

### 1. Subir la carpeta al VPS

```bash
# Desde tu PC (PowerShell/CMD)
scp -r "C:\ruta\a\tu\carpeta\eserver-radio" usuario@IP_DEL_VPS:/home/usuario/eserver-radio
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
| Host         | IP del VPS                         |
| Puerto       | `8100`                             |
| Password     | `oriental2024stream`               |
| Mountpoint   | `/radio.aac`                       |
| Formato      | AAC / MP3                          |

## URLs finales

- Player principal: `https://radio.tudominio.com`
- Widget embebible: `https://radio.tudominio.com/embed.html`
- Stream directo:   `https://radio.tudominio.com/radio.aac`
- Admin Icecast:    `http://IP_VPS:8100/admin` (solo interno)

## DNS requerido

Añadir en el panel DNS de tu dominio:

```
radio    A    [IP_DEL_VPS]
```

## Requisito previo: Red traefik-net

La red `traefik-net` debe existir en Docker. Si no existe:

```bash
docker network create traefik-net
```
