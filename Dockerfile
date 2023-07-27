FROM alpine:latest

RUN apk update && \
  apk upgrade && \
  apk add --clean-protected --no-cache \
    apache2 \
    apache2-ssl \
    php \
    php-dom \
    php-apache2 \
    php-openssl && \
  rm -f "/etc/apache2/conf.d/default.conf" && \
  rm -f "/var/www/localhost/htdocs/index.html" && \
  sed -ri \
    -e 's!^(\s*CustomLog)\s+\S+!\1 /proc/self/fd/1!g' \
    -e 's!^(\s*ErrorLog)\s+\S+!\1 /proc/self/fd/2!g' \
    "/etc/apache2/httpd.conf" && \
  sed -ri \
    -e 's!^(\s*PidFile)\s+\S+!\1 "/var/run/httpd.pid"!g' \
    "/etc/apache2/conf.d/mpm.conf" && \
  rm -f "/var/run/apache2/apache2.pid" && \
  rm -rf /var/cache/apk/*

COPY ssl.conf /etc/apache2/conf.d/ssl.conf
COPY apache.conf /etc/apache2/conf.d/apache.conf

RUN openssl req -new -x509 -newkey rsa:2048 -sha256 -nodes -days 3652 -out /etc/ssl/apache2/server.pem -keyout /etc/ssl/apache2/server.key -subj "/CN=localhost"

RUN sed -i 's/providers = provider_sect/providers = provider_sect\nssl_conf = ssl_sect\n\n[ssl_sect]\nsystem_default = system_default_sect\n\n[system_default_sect]\nOptions = UnsafeLegacyRenegotiation/' /etc/ssl/openssl.cnf

EXPOSE 80/tcp 443/tcp

CMD ["/usr/sbin/httpd", "-D", "FOREGROUND"]
