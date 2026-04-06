import subprocess
import threading
import json
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import parse_qs, urlparse

# ── Ports ──────────────────────────────────────────────────────────────────────
PHP_PORT = 8080
API_PORT = 9000

# ── Languages list ─────────────────────────────────────────────────────────────
# Add or remove languages here freely.
# dir: "right" = right-to-left, "left" = left-to-right
LANGUAGES = [
    {"code": "ar",    "name": "العربية",    "name_en": "Arabic",     "dir": "right", "flag": "🇸🇦"},
    {"code": "en",    "name": "English",    "name_en": "English",    "dir": "left",  "flag": "🇬🇧"},
    {"code": "fr",    "name": "Français",   "name_en": "French",     "dir": "left",  "flag": "🇫🇷"},
    {"code": "es",    "name": "Español",    "name_en": "Spanish",    "dir": "left",  "flag": "🇪🇸"},
    {"code": "de",    "name": "Deutsch",    "name_en": "German",     "dir": "left",  "flag": "🇩🇪"},
    {"code": "tr",    "name": "Türkçe",     "name_en": "Turkish",    "dir": "left",  "flag": "🇹🇷"},
    {"code": "it",    "name": "Italiano",   "name_en": "Italian",    "dir": "left",  "flag": "🇮🇹"},
    {"code": "pt",    "name": "Português",  "name_en": "Portuguese", "dir": "left",  "flag": "🇧🇷"},
    {"code": "ru",    "name": "Русский",    "name_en": "Russian",    "dir": "left",  "flag": "🇷🇺"},
    {"code": "zh-CN", "name": "中文",       "name_en": "Chinese",    "dir": "left",  "flag": "🇨🇳"},
    {"code": "ja",    "name": "日本語",     "name_en": "Japanese",   "dir": "left",  "flag": "🇯🇵"},
    {"code": "ko",    "name": "한국어",     "name_en": "Korean",     "dir": "left",  "flag": "🇰🇷"},
    {"code": "fa",    "name": "فارسی",      "name_en": "Persian",    "dir": "right", "flag": "🇮🇷"},
    {"code": "ur",    "name": "اردو",       "name_en": "Urdu",       "dir": "right", "flag": "🇵🇰"},
    {"code": "hi",    "name": "हिन्दी",    "name_en": "Hindi",      "dir": "left",  "flag": "🇮🇳"},
    {"code": "bn",    "name": "বাংলা",      "name_en": "Bengali",    "dir": "left",  "flag": "🇧🇩"},
    {"code": "nl",    "name": "Nederlands", "name_en": "Dutch",      "dir": "left",  "flag": "🇳🇱"},
    {"code": "pl",    "name": "Polski",     "name_en": "Polish",     "dir": "left",  "flag": "🇵🇱"},
    {"code": "sv",    "name": "Svenska",    "name_en": "Swedish",    "dir": "left",  "flag": "🇸🇪"},
]


# ── Translation function ───────────────────────────────────────────────────────
def translate(text: str, lang: str) -> str:
    from deep_translator import GoogleTranslator
    return GoogleTranslator(source="auto", target=lang).translate(text)


# ── API Handler ────────────────────────────────────────────────────────────────
class TranslateHandler(BaseHTTPRequestHandler):

    def log_message(self, format, *args):
        print(f"[API] {format % args}")

    def send_json(self, status: int, data):
        body = json.dumps(data, ensure_ascii=False).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")
        self.end_headers()
        self.wfile.write(body)

    def do_OPTIONS(self):
        self.send_response(204)
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")
        self.end_headers()

    def do_GET(self):
        parsed = urlparse(self.path)

        # ── GET /languages  → return full language list ────────────────────────
        if parsed.path == "/languages":
            self.send_json(200, LANGUAGES)
            return

        # ── GET /translate?text=hello&lang=ar ─────────────────────────────────
        if parsed.path == "/translate":
            params = parse_qs(parsed.query)
            text = params.get("text", [""])[0].strip()
            lang = params.get("lang", ["en"])[0].strip()
            if not text:
                self.send_json(400, {"error": "Missing 'text' parameter"}); return
            try:
                self.send_json(200, {"translated": translate(text, lang)})
            except Exception as e:
                self.send_json(500, {"error": str(e)})
            return

        self.send_json(404, {"error": "Available: /translate?text=hello&lang=ar  or  /languages"})

    def do_POST(self):
        if self.path == "/translate":
            length = int(self.headers.get("Content-Length", 0))
            try:
                data = json.loads(self.rfile.read(length))
                text = data.get("text", "").strip()
                lang = data.get("lang", "en").strip()
            except json.JSONDecodeError:
                self.send_json(400, {"error": "Invalid JSON"}); return
            if not text:
                self.send_json(400, {"error": "Missing 'text' field"}); return
            try:
                self.send_json(200, {"translated": translate(text, lang)})
            except Exception as e:
                self.send_json(500, {"error": str(e)})
        else:
            self.send_json(404, {"error": "Not found"})


# ── Start API in background thread ────────────────────────────────────────────
def start_api():
    server = HTTPServer(("0.0.0.0", API_PORT), TranslateHandler)
    print(f"[API] Translation API  → http://localhost:{API_PORT}")
    print(f"[API] Languages list   → http://localhost:{API_PORT}/languages")
    server.serve_forever()


# ── Main ───────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("Installing deep-translator...")
    subprocess.call("pip install deep-translator -q", shell=True)

    api_thread = threading.Thread(target=start_api, daemon=True)
    api_thread.start()

    print(f"[PHP] PHP server       → http://localhost:{PHP_PORT}")
    print(f"[PHP] Open http://localhost:{PHP_PORT}/index.php in your browser")
    print("Press Ctrl+C to stop.\n")
    subprocess.call(f"php -S 0.0.0.0:{PHP_PORT} -t .", shell=True)
