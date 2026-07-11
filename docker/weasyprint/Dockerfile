FROM python:3.14-slim

# WeasyPrint system dependencies (Pango for text layout, fonts for rendering)
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        libpango-1.0-0 libpangoft2-1.0-0 libharfbuzz0b libharfbuzz-subset0 \
        fonts-dejavu fonts-liberation \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pip install --no-cache-dir weasyprint==69.0

COPY server.py /srv/server.py

EXPOSE 5001

USER nobody

CMD ["python", "/srv/server.py"]
