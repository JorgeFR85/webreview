import datetime
import os
import queue
import threading
import wave
from dataclasses import dataclass
from pathlib import Path
from tkinter import BOTH, DISABLED, END, NORMAL, Button, Label, StringVar, Text, Tk

import numpy as np
import sounddevice as sd
import whisper
import yaml


@dataclass
class AppConfig:
    whisper_model: str
    language: str
    output_dir: str
    summary_provider: str
    sample_rate: int = 16000
    channels: int = 1


class MeetingRecorderApp:
    def __init__(self, root: Tk, config: AppConfig) -> None:
        self.root = root
        self.config = config
        self.base_output_dir = self._resolve_base_output_dir(self.config.output_dir)
        self.root.title("Grabador local de reuniones")
        self.root.geometry("640x360")

        self.status_var = StringVar(value="Listo para grabar")
        self.recording = False
        self.audio_queue: queue.Queue[np.ndarray] = queue.Queue()
        self.audio_chunks: list[np.ndarray] = []
        self.stream: sd.InputStream | None = None
        self.recording_dir: Path | None = None

        self.model = whisper.load_model(self.config.whisper_model)

        self._build_ui()

    @staticmethod
    def _resolve_base_output_dir(output_dir: str) -> Path:
        base_path = Path(output_dir).expanduser().resolve()
        base_path.mkdir(parents=True, exist_ok=True)
        return base_path

    def _safe_session_dir(self, session_folder: str) -> Path:
        candidate = (self.base_output_dir / session_folder).resolve()
        if candidate.parent != self.base_output_dir:
            raise ValueError("Ruta de salida fuera del directorio base permitido.")
        return candidate

    @staticmethod
    def _secure_write_text(path: Path, content: str) -> None:
        flags = os.O_WRONLY | os.O_CREAT | os.O_TRUNC
        fd = os.open(path, flags, 0o600)
        with os.fdopen(fd, "w", encoding="utf-8") as file_handle:
            file_handle.write(content)

    def _build_ui(self) -> None:
        Label(self.root, text="Grabación local (micrófono)", font=("Segoe UI", 14, "bold")).pack(pady=12)
        Label(self.root, textvariable=self.status_var).pack(pady=4)

        self.start_btn = Button(self.root, text="Iniciar grabación", command=self.start_recording, width=20)
        self.start_btn.pack(pady=8)

        self.stop_btn = Button(
            self.root,
            text="Detener grabación",
            command=self.stop_recording,
            width=20,
            state=DISABLED,
        )
        self.stop_btn.pack(pady=8)

        self.log = Text(self.root, height=10)
        self.log.pack(fill=BOTH, expand=True, padx=16, pady=12)

    def _log(self, message: str) -> None:
        self.log.insert(END, message + "\n")
        self.log.see(END)

    def _audio_callback(self, indata, frames, time_info, status) -> None:
        del frames, time_info
        if status:
            self._log("Aviso de audio detectado.")
        self.audio_queue.put(indata.copy())

    def start_recording(self) -> None:
        if self.recording:
            return

        now = datetime.datetime.now().strftime("%Y-%m-%d_%H-%M")
        self.recording_dir = self._safe_session_dir(now)
        self.recording_dir.mkdir(parents=True, exist_ok=True)

        self.audio_chunks.clear()
        self.recording = True
        self.status_var.set("Grabando...")
        self.start_btn.config(state=DISABLED)
        self.stop_btn.config(state=NORMAL)

        self.stream = sd.InputStream(
            samplerate=self.config.sample_rate,
            channels=self.config.channels,
            callback=self._audio_callback,
            dtype="float32",
        )
        self.stream.start()
        threading.Thread(target=self._collector_thread, daemon=True).start()
        self._log(f"Grabación iniciada en: {self.recording_dir}")

    def _collector_thread(self) -> None:
        while self.recording:
            try:
                chunk = self.audio_queue.get(timeout=0.5)
                self.audio_chunks.append(chunk)
            except queue.Empty:
                continue

    def stop_recording(self) -> None:
        if not self.recording:
            return

        self.recording = False
        if self.stream is not None:
            self.stream.stop()
            self.stream.close()

        self.status_var.set("Procesando...")
        self.start_btn.config(state=NORMAL)
        self.stop_btn.config(state=DISABLED)

        threading.Thread(target=self._finalize_recording, daemon=True).start()

    def _finalize_recording(self) -> None:
        try:
            if not self.audio_chunks or self.recording_dir is None:
                self.status_var.set("No se capturó audio")
                self._log("No se detectó audio para guardar.")
                return

            audio = np.concatenate(self.audio_chunks, axis=0)
            wav_path = (self.recording_dir / "audio.wav").resolve()
            if wav_path.parent != self.recording_dir:
                raise ValueError("Ruta de audio inválida.")

            pcm_audio = np.int16(np.clip(audio, -1.0, 1.0) * 32767)
            with wave.open(str(wav_path), "wb") as wf:
                wf.setnchannels(self.config.channels)
                wf.setsampwidth(2)
                wf.setframerate(self.config.sample_rate)
                wf.writeframes(pcm_audio.tobytes())

            self._log("Audio guardado correctamente.")
            self._run_transcription(wav_path)
            self.status_var.set("Listo")
        except Exception:
            self.status_var.set("Error en procesamiento")
            self._log("Se produjo un error procesando la sesión.")

    def _run_transcription(self, wav_path: Path) -> None:
        assert self.recording_dir is not None
        self._log("Iniciando transcripción local con Whisper...")

        result = self.model.transcribe(str(wav_path), language=self.config.language, task="transcribe")

        transcript_path = (self.recording_dir / "transcript.txt").resolve()
        srt_path = (self.recording_dir / "transcript.srt").resolve()
        summary_path = (self.recording_dir / "summary.md").resolve()

        for output in (transcript_path, srt_path, summary_path):
            if output.parent != self.recording_dir:
                raise ValueError("Ruta de salida fuera del directorio de sesión.")

        self._secure_write_text(transcript_path, result.get("text", "").strip() + "\n")
        self._secure_write_text(srt_path, self._to_srt(result.get("segments", [])))
        self._secure_write_text(summary_path, self._build_summary(result.get("text", "")))

        self._log("Transcripción y artefactos generados correctamente.")

    @staticmethod
    def _format_timestamp(seconds: float) -> str:
        ms = int((seconds - int(seconds)) * 1000)
        total = int(seconds)
        s = total % 60
        m = (total // 60) % 60
        h = total // 3600
        return f"{h:02d}:{m:02d}:{s:02d},{ms:03d}"

    def _to_srt(self, segments) -> str:
        lines = []
        for i, seg in enumerate(segments, 1):
            start = self._format_timestamp(seg["start"])
            end = self._format_timestamp(seg["end"])
            text = seg["text"].strip()
            lines.append(f"{i}\n{start} --> {end}\n{text}\n")
        return "\n".join(lines)

    def _build_summary(self, transcript: str) -> str:
        bullets = [line.strip() for line in transcript.split(".") if line.strip()][:8]
        decisions = [b for b in bullets if "decid" in b.lower()][:4]
        tasks = [b for b in bullets if any(k in b.lower() for k in ["tarea", "hacer", "pendiente", "enviar"])][:6]
        risks = [b for b in bullets if any(k in b.lower() for k in ["riesgo", "bloque", "problema", "retraso"])][:4]
        questions = [b for b in bullets if "?" in b][:5]

        def section(items, fallback):
            return "\n".join([f"- {i}" for i in items]) if items else f"- {fallback}"

        return f"""# Resumen de reunión

## Resumen ejecutivo
{section(bullets[:5], "Sin contenido suficiente para resumir.")}

## Decisiones tomadas
{section(decisions, "No se detectaron decisiones explícitas.")}

## Tareas pendientes
{section(tasks, "No se detectaron tareas pendientes con responsable claro.")}

## Riesgos o bloqueos
{section(risks, "No se detectaron riesgos o bloqueos explícitos.")}

## Preguntas de seguimiento
{section(questions, "No se detectaron preguntas de seguimiento.")}
"""


def load_config(path: str = "config.yaml") -> AppConfig:
    with open(path, "r", encoding="utf-8") as fh:
        raw = yaml.safe_load(fh)
    return AppConfig(
        whisper_model=raw["whisper_model"],
        language=raw["language"],
        output_dir=raw["output_dir"],
        summary_provider=raw["summary_provider"],
    )


if __name__ == "__main__":
    cfg = load_config()
    root = Tk()
    app = MeetingRecorderApp(root, cfg)
    root.mainloop()
