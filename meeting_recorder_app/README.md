# Grabador local de reuniones (Windows + Python 3.11)

Aplicación local con Tkinter que:
- graba audio del micrófono en local,
- guarda la sesión en `./recordings/YYYY-MM-DD_HH-MM/`,
- ejecuta transcripción local con Whisper,
- genera `transcript.txt`, `transcript.srt` y `summary.md`.

> No se integra con Microsoft Teams, no crea reuniones y no se une como bot.

## 1) Requisitos

- Windows 10/11
- Python 3.11
- FFmpeg instalado y disponible en `PATH`

## 2) Instalar FFmpeg en Windows

Opción recomendada con winget:

```powershell
winget install --id Gyan.FFmpeg -e
```

Verifica:

```powershell
ffmpeg -version
```

## 3) Crear entorno virtual Python 3.11

```powershell
py -3.11 -m venv .venv
.\.venv\Scripts\activate
python -m pip install --upgrade pip
```

## 4) Instalar dependencias (incluye Whisper local)

```powershell
pip install -r requirements.txt
```

## 5) Ejecutar la aplicación

```powershell
python app.py
```

## 6) Uso

1. Pulsa **Iniciar grabación**.
2. Pulsa **Detener grabación** al terminar.
3. La app guarda automáticamente:
   - `audio.wav`
   - `transcript.txt`
   - `transcript.srt`
   - `summary.md`

## 7) Nota sobre audio del sistema en Windows

Esta primera versión prioriza estabilidad y graba **micrófono** local.
Para audio del sistema (loopback WASAPI), se puede añadir en una iteración posterior seleccionando un dispositivo loopback compatible.

## 8) Configuración

Edita `config.yaml`:

- `whisper_model`: por ejemplo `tiny`, `base`, `small`, `medium`
- `language`: `es`
- `output_dir`: carpeta de salida
- `summary_provider`: proveedor de resumen (`local_heuristic` en esta versión)

## Privacidad

- Procesamiento local por defecto.
- No se envía audio a servicios externos salvo configuración explícita futura.

## 9) Análisis estático y seguridad

Si no tienes las herramientas instaladas, puedes ejecutarlas así:

```powershell
python -m pip install pip-audit bandit ruff
python -m pip_audit
python -m bandit -r .
python -m ruff check .
```
