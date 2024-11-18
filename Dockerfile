FROM nginx:1.16-alpine as nginx-base
RUN apk add --no-cache bash

COPY docker-images/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker-images/nginx/vhost.conf /opt/vhost.conf
COPY docker-images/nginx/docker-entrypoint.sh /usr/local/bin/

RUN chown -R nginx:0 /var/cache/nginx && \
    chmod -R g+w /var/cache/nginx && \
    chown -R nginx:0 /etc/nginx && \
    chmod -R g+w /etc/nginx

EXPOSE 8080
STOPSIGNAL SIGTERM
USER nginx

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["nginx"]


FROM php:8.1.17-fpm-alpine3.16 as php-fpm-base
# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN set -xe; \
        apk add --update --no-cache -t .php-run-deps \
        bash \
        freetype \
        icu-libs \
        libbz2 \
        libevent \
        libjpeg-turbo \
        libjpeg-turbo-utils \
        libmcrypt \
        libpng \
        libuuid \
        zlib \
        libmemcached \
        libmemcached-libs \
        libwebp \
        libxml2 \
        libxslt \
        libzip \
        imagemagick-libs \
        imagemagick \
        xvfb \
        ttf-dejavu ttf-droid ttf-freefont ttf-liberation \
        yaml && \
    apk add --update --no-cache -t .php-build-deps \
        g++ \
        make \
        autoconf \
        libzip-dev \
        icu-dev \
        bzip2-dev \
        freetype-dev \
        zlib-dev \
        libmemcached-dev \
        libmcrypt-dev \
        jpeg-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        imagemagick-dev \
        unixodbc-dev \
        yaml-dev && \
    docker-php-ext-install \
        bcmath \
        bz2 \
        calendar \
        exif \
        intl \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        zip; \
        docker-php-ext-configure gd \
            --with-webp \
            --with-freetype \
            --with-jpeg; \
    NPROC=$(getconf _NPROCESSORS_ONLN) && \
    docker-php-ext-install "-j${NPROC}" gd && \
    pecl channel-update pecl.php.net && \
    pecl install yaml-2.2.2 \
                 apcu-5.1.21 \
                 uploadprogress-2.0.2 \
                 memcached-3.1.5 \
                 mcrypt-1.0.5 \
                 imagick-3.7.0 && \
    docker-php-ext-enable yaml \
                          apcu \
                          uploadprogress \
                          memcached \
                          mcrypt \
                          imagick && \
    sed -i 's/<\/policymap>/  <policy domain="coder" rights="read" pattern="PDF" \/>\n<\/policymap>/' /etc/ImageMagick-7/policy.xml && \
    apk del --purge .php-build-deps && \
    rm -rf \
        /usr/src/php/ext/ast \
        /usr/src/php/ext/uploadprogress \
        /usr/include/php \
        /usr/lib/php/build \
        /tmp/* \
        /root/.composer \
        /var/cache/apk/*; \
    curl "https://github.com/tideways/php-xhprof-extension/archive/v5.0.4.tar.gz" -fsL -o ./php-xhprof-extension.tar.gz && \
        tar xf ./php-xhprof-extension.tar.gz && \
        cd php-xhprof-extension-5.0.4 && \
        apk add --update --no-cache build-base autoconf && \
        phpize && \
        ./configure && \
        make && \
        make install; \
    rm -rf ./php-xhprof-extension.tar.gz ./php-xhprof-extension-5.0.4; \
    docker-php-ext-enable tideways_xhprof

# download patched wkhtmltopdf
COPY --from=madnight/alpine-wkhtmltopdf-builder:0.12.5-alpine3.10-606718795 /bin/wkhtmltopdf /usr/bin/wkhtmltopdf
COPY --from=madnight/alpine-wkhtmltopdf-builder:0.12.5-alpine3.10-606718795 /bin/wkhtmltoimage /usr/bin/wkhtmltoimage
USER www-data

FROM php-fpm-base as cli-base
USER root
RUN apk add --update --no-cache -t .build-deps \
    git \
    openssh-client \
    ca-certificates \
    patch \
    mariadb-client
RUN wget -qO- https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2
RUN drush_launcher_url="https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar"; \
    wget -O drush.phar "${drush_launcher_url}"; \
    chmod +x drush.phar; \
    mv drush.phar /usr/local/bin/drush
USER www-data
WORKDIR /var/www/html
ENTRYPOINT ["/bin/bash", "-c"]
CMD ["tail -f /dev/null"]

FROM php-fpm-base as php-fpm-local
USER root
RUN  apk add --update --no-cache -t .php-build-deps \
       g++ \
       make \
       autoconf \
       linux-headers && \
     pecl install xdebug-3.2.1 && \
    apk del --purge .php-build-deps && \
    rm -rf \
        /usr/src/php/ext/ast \
        /usr/src/php/ext/uploadprogress \
        /usr/include/php \
        /usr/lib/php/build \
        /tmp/* \
        /root/.composer \
        /var/cache/apk/*

COPY docker-images/php-fpm-local/docker-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]

FROM cli-base as cli-local
USER root
COPY docker-images/cli-local/docker-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
USER www-data
CMD ["tail","-f","/dev/null"]
