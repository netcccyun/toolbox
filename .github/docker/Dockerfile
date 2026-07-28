ARG ALPINE_VERSION=3.24
FROM alpine:${ALPINE_VERSION}
# Setup document root
WORKDIR /app/www

# Install packages and remove default server definition
RUN apk add --no-cache \
  bash \
  curl \
  nginx \
  php83 \
  php83-ctype \
  php83-curl \
  php83-dom \
  php83-fileinfo \
  php83-fpm \
  php83-gd \
  php83-gettext \
  php83-intl \
  php83-iconv \
  php83-mbstring \
  php83-mysqli \
  php83-opcache \
  php83-openssl \
  php83-phar \
  php83-sodium \
  php83-session \
  php83-simplexml \
  php83-tokenizer \
  php83-xml \
  php83-xmlreader \
  php83-xmlwriter \
  php83-zip \
  php83-pdo \
  php83-pdo_mysql \
  php83-pdo_sqlite \
  supervisor \
  && ln -sf /usr/bin/php83 /usr/bin/php

RUN rm -rf /var/cache/apk/* /tmp/*

# Configure nginx - http
COPY config/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
ENV PHP_INI_DIR /etc/php83
COPY config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Configure supervisord
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# CACHE_BUST 须写进每条相关 RUN，否则 GHA/BuildKit 可能单独命中 composer 相关层缓存，vendor 仍来自旧构建
ARG CACHE_BUST=local

# Add application
RUN mkdir -p /usr/src && echo "$CACHE_BUST" >/dev/null && wget --no-cache https://github.com/netcccyun/toolbox/archive/refs/heads/main.zip -O /usr/src/www.zip && unzip /usr/src/www.zip -d /usr/src/ && mv /usr/src/toolbox-main /usr/src/www && rm -f /usr/src/www.zip

# Install composer（与下面 install 一并随 CACHE_BUST 失效）
RUN echo "$CACHE_BUST" >/dev/null && wget https://getcomposer.org/download/latest-stable/composer.phar -O /usr/local/bin/composer && chmod +x /usr/local/bin/composer

RUN echo "$CACHE_BUST" >/dev/null && composer install -d /usr/src/www --no-interaction --no-dev --optimize-autoloader --no-cache

RUN adduser -D -s /sbin/nologin -g www www && chown -R www:www /usr/src/www /var/lib/nginx /var/log/nginx

# copy entrypoint script
COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["sh", "/entrypoint.sh"]

# Expose the port nginx is reachable on
EXPOSE 80

# Let supervisord start nginx & php-fpm
CMD /usr/sbin/crond && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1/fpm-ping || exit 1
