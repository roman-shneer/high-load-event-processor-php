FROM phpswoole/swoole:latest-alpine
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions pdo_pgsql pgsql redis

WORKDIR /var/www

COPY server.php .
COPY worker.php .
COPY monitor.php .
COPY index.html .
COPY init.sql .

CMD ["php", "server.php"]