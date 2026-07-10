FROM phpdockerio/php:8.4-fpm
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
ARG UID=1000
ARG GID=1000

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install php8.4-intl php8.4-xsl php8.4-tidy \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Remap www-data UID/GID to match the host user so files created inside the
# container are owned by the host user on the mounted volume. Bump the
# pre-existing ubuntu user (uid=1000) out of the way first to avoid a conflict.
RUN usermod -u 9999 ubuntu && groupmod -g 9999 ubuntu \
    && usermod -u ${UID} www-data && groupmod -g ${GID} www-data

# Allow php-fpm to run as www-data (non-root): pre-create the PID file with
# correct ownership so the master process can write it without needing root.
RUN touch /run/php-fpm.pid && chown www-data:www-data /run/php-fpm.pid
