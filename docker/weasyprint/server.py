"""Minimal HTTP service converting a URL to PDF with WeasyPrint.

POST /convert  {"url": "http://webserver/roadbook/<id>/raw"}  -> application/pdf
GET  /health                                                  -> 200 ok
"""

import json
import logging
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlparse

from weasyprint import HTML

ALLOWED_HOSTS = {"webserver"}  # only fetch pages from our own nginx
PORT = 5001

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
logger = logging.getLogger("weasyprint-service")


class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == "/health":
            self._respond(200, b"ok", "text/plain")
        else:
            self._respond(404, b"not found", "text/plain")

    def do_POST(self):
        if self.path != "/convert":
            self._respond(404, b"not found", "text/plain")
            return

        try:
            length = int(self.headers.get("Content-Length", 0))
            payload = json.loads(self.rfile.read(length) or b"{}")
            url = payload.get("url", "")

            host = urlparse(url).hostname
            if host not in ALLOWED_HOSTS:
                self._respond(400, b'{"error": "url host not allowed"}', "application/json")
                return

            logger.info("converting %s", url)
            pdf = HTML(url=url).write_pdf()
            self._respond(200, pdf, "application/pdf")
        except Exception as exc:  # noqa: BLE001 - report any conversion failure to the caller
            logger.exception("conversion failed")
            body = json.dumps({"error": str(exc)}).encode()
            self._respond(500, body, "application/json")

    def _respond(self, status, body, content_type):
        self.send_response(status)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, fmt, *args):
        logger.info(fmt, *args)


if __name__ == "__main__":
    logger.info("listening on :%d", PORT)
    ThreadingHTTPServer(("0.0.0.0", PORT), Handler).serve_forever()
