# AutoRevista: kit de lanzamiento (WordPress + SEO automoción)

## 1) Estructura del tema WordPress

```text
wp-content/
├─ themes/
│  └─ autorevista-child/
│     ├─ style.css
│     ├─ functions.php
│     ├─ front-page.php
│     ├─ template-parts/
│     │  └─ home/
│     │     └─ cards.php
│     └─ assets/
│        └─ css/
└─ plugins/
   └─ autorevista-ai-drafts/
      └─ autorevista-ai-drafts.php
```

### Blueprint de páginas

1. **Home** (`/`): hero + últimas reviews + últimas noticias + bloque para marcas + newsletter.  
2. **Reviews** (`/reviews/`): archivo del CPT `review` con filtros por segmento, etiqueta, combustible.  
3. **Noticias** (`/noticias/`): archivo de posts estándar por categorías de actualidad.  
4. **Sobre nosotros** (`/sobre-nosotros/`): misión, metodología de pruebas y perfiles del equipo.  
5. **Contacto para marcas** (`/contacto-para-marcas/`): formulario B2B con CTA a colaboraciones y préstamos de unidades.  
6. **Política editorial** (`/politica-editorial/`): transparencia, conflicto de interés, fuentes, correcciones.

### Recomendaciones de credibilidad (clave para invitaciones de marca)

- Añadir en cabecera/footer un acceso visible a **Política editorial** y **Contacto para marcas**.
- Mostrar autor, fecha de actualización y bloque de fuentes en cada review.
- Diferenciar claramente contenido editorial vs. contenido patrocinado.
- Mantener fichas técnicas con unidades y ciclo de homologación (WLTP/EPA) explícitos.

---

## 2) Código base del tema hijo (enfoque GeneratePress/Astra/Kadence)

- El CSS aplica estilo editorial minimalista: aire, cards sobrias y tipografía legible.
- `functions.php` registra CPT `review` y taxonomía `review_tag`.
- `front-page.php` implementa la Home solicitada con bloques listos para producción.

> Nota: en `style.css` está configurado `Template: generatepress`. Si usas Astra o Kadence, solo cambia ese valor por `astra` o `kadence`.

---

## 3) Plugin básico de IA para generar borradores

### Endpoint REST propuesto

**Namespace:** `autorevista/v1`

1. `POST /wp-json/autorevista/v1/generate-draft`
2. `POST /wp-json/autorevista/v1/approve-draft/{id}`

### Campos de entrada (generate-draft)

- `model` (string, obligatorio)
- `angle` (string, obligatorio)
- `verified_facts` (array, obligatorio)
- `sources` (array, obligatorio, mínimo 2)
  - `label` (string)
  - `url` (string)
- `target_keywords` (array, opcional)
- `disclaimers` (array, opcional)

### Campos de salida (generate-draft)

- `post_id`
- `status` (`draft_created`)
- `next_step` (`editorial_review_required`)
- `confidence_notes` (array de avisos de revisión)

### Flujo editorial con revisión humana (antes de publicar)

1. IA genera borrador en estado `draft`.
2. El plugin guarda metadatos:
   - `_ar_ai_status = pending_human_review`
   - `_ar_ai_sources = [...]`
   - `_ar_ai_plagiarism = pending_manual_check`
3. Editor humano valida:
   - exactitud factual,
   - coherencia editorial,
   - tono y transparencia.
4. Aprobación interna vía endpoint `approve-draft/{id}` → estado `pending`.
5. Editor jefe publica manualmente.

### Medidas anti-plagio recomendadas

- Umbral interno de similitud por párrafo (ej. rechazar >20-25% de coincidencia literal).
- Bloques obligatorios de reescritura humana en intro, conducción y veredicto.
- Prohibir publicación si faltan fuentes o si hay fuentes no trazables.
- Registro de auditoría: usuario editor, fecha y checklist de control.

### Citas/fuentes obligatorias

Toda review debe cerrar con:

- mínimo 2 fuentes primarias (fabricante, regulador, fichas oficiales),
- fecha de consulta,
- nota de mercado/ciclo (ej. “dato WLTP europeo” o “estimación fabricante para EE. UU.”).

---

## 4) Review inicial (ejemplo SEO + tono editorial serio)

### SEO title
Toyota RAV4 2026 GR Sport Plug-in Hybrid: prueba, autonomía y opinión

### Meta description
Analizamos el Toyota RAV4 2026 GR Sport Plug-in Hybrid: diseño, interior, tecnología, motor, autonomía y veredicto honesto con pros y contras.

### Slug
`toyota-rav4-2026-gr-sport-plug-in-hybrid-review`

### Categorías
- Reviews
- SUV
- Híbridos enchufables

### Tags
- Toyota RAV4 2026
- GR Sport
- Plug-in Hybrid
- PHEV
- SUV mediano
- Toyota Safety Sense

---

# Toyota RAV4 2026 GR Sport Plug-in Hybrid: review completa

## Introducción
El **Toyota RAV4 2026 GR Sport Plug-in Hybrid** llega en un momento clave: el SUV medio electrificado ya no compite solo por etiqueta ECO y consumo, sino por experiencia completa de uso, conectividad y valor real para familia y empresa.

Esta versión GR Sport no es solo estética. Toyota la posiciona como una variante con enfoque dinámico dentro de la nueva generación del RAV4, manteniendo la filosofía práctica del modelo.

## Diseño exterior
Toyota diferencia claramente tres estilos en la nueva generación y reserva al **GR Sport** la imagen más agresiva y funcional: parrilla específica, tratamiento aerodinámico y una puesta en escena más ancha visualmente.

En términos de presencia, transmite más “coche de carretera rápida” que “SUV de escaparate”. No llega al extremo de un deportivo, pero sí eleva el nivel frente a versiones de corte más familiar.

## Interior
El salto más interesante está en ergonomía y arquitectura. Toyota anuncia una instrumentación y consola pensadas para reducir distracciones, con enfoque más horizontal y uso diario sencillo.

¿Es revolucionario? No. ¿Está mejor resuelto que antes en interfaz y organización? Sí, sobre todo para quien alterna ciudad, viajes y carga de dispositivos.

## Tecnología
En esta nueva etapa del RAV4, la marca introduce evolución en multimedia y asistentes de seguridad, con una base de software más moderna según el mercado.

Puntos relevantes para usuario real:
- pantallas grandes y conectividad smartphone,
- paquete de ayudas a la conducción de última hornada de Toyota,
- promesa de evolución digital más continua.

## Motor
Aquí está la parte más importante del coche: el sistema **Plug-in Hybrid de nueva generación**. El RAV4 GR Sport se integra en la gama PHEV con tracción total según mercado/versiones publicadas.

Sobre potencia exacta: **varía por mercado y comunicación comercial** (por ejemplo, en Norteamérica hay cifras de gama y acabados concretos). Conviene validar ficha local antes de compra o publicación final.

## Consumo y autonomía
Este punto requiere transparencia porque hay diferencias de homologación:

- Toyota comunica para el nuevo RAV4 PHEV un objetivo de **hasta 150 km en modo EV** en ciclo WLTP de desarrollo (comunicación global/japonesa).
- En EE. UU., Toyota habla de una estimación fabricante de **52 millas eléctricas** para el RAV4 Plug-in Hybrid 2026.
- En Reino Unido, Toyota publica **hasta 85 millas** eléctricas para su especificación local.

**Conclusión editorial:** no son cifras contradictorias; son cifras de mercados/ciclos distintos y, en algunos casos, objetivos o estimaciones preliminares. Para una comparativa seria, hay que usar siempre el mismo ciclo.

## Conducción
En el GR Sport, Toyota anuncia ajustes específicos de chasis/dirección y mayor ancho de vías en su presentación global, con la intención de mejorar aplomo y precisión.

Traducido a lenguaje de conductor: debería sentirse más directo en apoyos y cambios de ritmo que un RAV4 estándar, sin perder la facilidad de uso diaria que espera el público de este modelo.

Hasta contar con prueba instrumentada en asfalto y consumo en ruta mixta real, esta parte debe considerarse **pre-evaluación editorial basada en datos oficiales**.

## Pros y contras

### Pros
- Imagen más cuidada y técnica que la media del segmento.
- Plataforma PHEV con mejoras relevantes en autonomía eléctrica (según mercado).
- Enfoque práctico/familiar sin renunciar a un tacto más dinámico en acabado GR Sport.

### Contras
- Riesgo de confusión en autonomía si no se explica bien el ciclo de homologación.
- Precio potencialmente alto en acabado GR Sport frente a versiones no deportivas.
- Algunas cifras clave dependen del mercado final y del equipamiento definitivo.

## Veredicto
El Toyota RAV4 2026 GR Sport Plug-in Hybrid apunta a ser una de las propuestas más completas para quien quiere un SUV enchufable equilibrado entre imagen, uso familiar y tecnología.

¿Es ya “la referencia absoluta”? Aún es pronto sin prueba de larga duración y mediciones propias comparables. Pero por planteamiento de producto y evolución técnica, **sí tiene argumentos sólidos para estar en la shortlist premium-racional del segmento**.

Si eres marca o agencia y quieres que probemos tu modelo con metodología pública y criterios comparables, puedes escribirnos desde **Contacto para marcas**.

## Puntuación (0-10)
**8,4 / 10** (puntuación provisional sujeta a prueba dinámica completa y consumo medido por nuestra redacción).

## Fuentes verificables usadas en este borrador
- Toyota Global Newsroom (21 mayo 2025): presentación global nuevo RAV4.
- Toyota USA Newsroom (20 mayo 2025 y actualizaciones 2026): información de gama PHEV en EE. UU.
- Toyota UK Media Site (7 abril 2026): precios/rango y autonomía eléctrica para mercado británico.
