# webreview

## Cómo ver la web en local

Este repo incluye un stack mínimo de WordPress con Docker Compose para que puedas ver la Home y validar el tema/plugin.

### 1) Levantar WordPress

```bash
docker compose up -d
```

### 2) Abrir instalación

- Web: `http://localhost:8080`
- Admin: `http://localhost:8080/wp-admin`

Completa el asistente de instalación de WordPress (título del sitio, usuario admin, etc.).

### 3) Activar tema y plugin

En **Apariencia > Temas**:
- activa el tema padre (GeneratePress / Astra / Kadence),
- activa después **AutoRevista Child**.

> Nota: en `style.css` del child theme ahora está `Template: generatepress`. Si usas Astra o Kadence, cambia ese valor antes de activar el tema hijo.

En **Plugins**:
- activa **AutoRevista AI Drafts**.

### 4) Configurar página de inicio

En **Ajustes > Lectura**:
- selecciona **Una página estática**,
- crea y asigna una página llamada “Home” como portada.

`front-page.php` renderiza automáticamente el layout editorial solicitado.

### 5) Crear contenido rápido para ver bloques

- Crea 2-3 posts en “Noticias”.
- Crea 2-3 entradas del CPT “Reviews”.

La Home ya mostrará:
- Hero con review destacada,
- últimas reviews,
- últimas noticias,
- bloque para marcas,
- newsletter placeholder.

## Endpoints del plugin IA

Una vez activado el plugin y logueado como editor/admin:

- `POST /wp-json/autorevista/v1/generate-draft`
- `POST /wp-json/autorevista/v1/approve-draft/{id}`

Ejemplo mínimo de payload para generar borrador:

```json
{
  "model": "Toyota RAV4 2026 GR Sport Plug-in Hybrid",
  "angle": "Review técnica y de uso real",
  "verified_facts": [
    "Autonomía declarada varía por mercado/ciclo",
    "Versión GR Sport con enfoque dinámico"
  ],
  "sources": [
    { "label": "Toyota Global Newsroom", "url": "https://global.toyota/en/newsroom/" },
    { "label": "Toyota USA Pressroom", "url": "https://pressroom.toyota.com/" }
  ]
}
```
