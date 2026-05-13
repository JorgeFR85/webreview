# SECURITY REVIEW

## Alcance
- Carpeta revisada: `meeting_recorder_app/`
- Fecha: 2026-05-13

## Vulnerabilidades encontradas y correcciones

1. **Escritura en rutas no restringidas por sesión/base de salida**
   - Severidad: **Media**
   - Archivo/línea afectada (antes): `app.py` (construcción de rutas de salida sin validación estricta)
   - Cambio aplicado:
     - Se añade validación de directorio base con `resolve()`.
     - Se fuerza que el directorio de sesión cuelgue de la carpeta base.
     - Se valida que `audio.wav`, `transcript.txt`, `transcript.srt`, `summary.md` permanezcan dentro de la carpeta de sesión.
   - Estado: **Corregido**.

2. **Permisos potencialmente inseguros en archivos de transcripción/resumen**
   - Severidad: **Media**
   - Archivo/línea afectada (antes): `app.py` (`write_text` directo, sin modo explícito)
   - Cambio aplicado:
     - Escritura segura mediante `os.open(..., 0o600)` + `os.fdopen` para `transcript.txt`, `transcript.srt`, `summary.md`.
   - Estado: **Corregido**.

3. **Posible exposición de información sensible en logs/errores**
   - Severidad: **Baja**
   - Archivo/línea afectada (antes): `app.py` (logs con rutas completas y detalles de estado de audio)
   - Cambio aplicado:
     - Mensajes de log más genéricos para evitar revelar detalles innecesarios.
     - Manejo de errores en procesamiento final con mensaje controlado y sin volcado de trazas.
   - Estado: **Corregido**.

## Hallazgos no confirmados

- **Dependencias vulnerables u obsoletas**: **No confirmado** en este entorno al no disponer inicialmente de `pip-audit` instalado.
- **Uso inseguro de subprocess/shell=True**: **No confirmado como problema** (no se detectó uso de `subprocess` en el código revisado).
- **Envío accidental de datos a servicios externos**: **No confirmado como problema** (el flujo implementado usa Whisper local y no incluye llamadas HTTP/API explícitas).

## Riesgos pendientes

- Whisper puede invocar internamente `ffmpeg` a través de dependencias de terceros; no hay invocación directa en este código. Se recomienda fijar versiones y ejecutar auditoría periódica de dependencias.
- Esta versión no implementa cifrado en reposo de audio/transcripciones; el riesgo depende del perfil de amenazas del equipo local.
