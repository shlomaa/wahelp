#!/bin/bash

set -e

if [[ $DEBUG ]]; then
  set -x
fi

if [ -n "$PHP_SENDMAIL_PATH" ]; then
     sed -i 's@^;sendmail_path.*@'"sendmail_path = ${PHP_SENDMAIL_PATH}"'@' $PHP_INI_DIR/php.ini
fi

if [[ $PHP_XDEBUG_ENABLED = 1 ]]; then
     docker-php-ext-enable xdebug
fi

cat <<EOF >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
xdebug.mode = develop,debug
xdebug.discover_client_host = true
xdebug.start_with_request = yes
xdebug.client_port = 9000
xdebug.client_host = localhost
EOF

if [[ $PHP_XDEBUG_REMOTE_HOST ]]; then
     sed -i 's/^xdebug.client_host.*/'"xdebug.client_host = ${PHP_XDEBUG_REMOTE_HOST}"'/' $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
fi

exec "$@"
