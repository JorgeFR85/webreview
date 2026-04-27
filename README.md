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
<<<<<<< HEAD


## Seed automático de review inicial

Al acceder al admin con un usuario administrador, el tema crea automáticamente (una sola vez) la review inicial:
- **Toyota RAV4 2026 GR Sport Plug-in Hybrid**
- Slug: `toyota-rav4-2026-gr-sport-plug-in-hybrid-review`

Esto te permite ver la Home con una review real sin cargar contenido manualmente.


## IA para subir artículos y aprobarlos después

Ahora el plugin soporta también borradores de **artículos (post)** con revisión humana:

- `POST /wp-json/autorevista/v1/generate-article-draft` (crea artículo en `draft`)
- `POST /wp-json/autorevista/v1/approve-draft/{id}` (lo pasa a `pending` para aprobación editorial)

Ejemplo para crear artículo en borrador:

```bash
curl -X POST "http://localhost:8080/?rest_route=/autorevista/v1/generate-article-draft" \
  -H "Content-Type: application/json; charset=utf-8" \
  -u "jorge:TU_APP_PASSWORD" \
  --data-binary '{
    "title": "Toyota actualiza su estrategia PHEV en 2026",
    "angle": "Noticia de mercado con contexto para comprador",
    "verified_facts": ["Dato oficial 1", "Dato oficial 2"],
    "sources": [
      {"label": "Fuente oficial 1", "url": "https://example.com/1"},
      {"label": "Fuente oficial 2", "url": "https://example.com/2"}
    ],
    "categories": ["Noticias"],
    "tags": ["Toyota", "PHEV", "Mercado"]
  }'
```

Flujo recomendado:
1. La IA crea el artículo en `draft`.
2. Tú revisas estilo, hechos y enlaces.
3. Llamas a `approve-draft/{id}` para moverlo a `pending`.
4. Publicas manualmente cuando esté validado.
=======
>>>>>>> origin/main
