FROM php:8 as builder

# install latest and greatest composer binary
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
  && apt-get install -y git unzip libxml2-dev \
  && rm -rf /var/lib/apt/*

# copy files
COPY . /app
WORKDIR /app

# enable php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql soap

# run installation
ENV NVM_VERSION v0.39.0
ENV NODE_VERSION v14.18.1
ENV NVM_DIR /app/.nvm
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader \
  && mkdir ${NVM_DIR} \
  && curl -f -o- https://raw.githubusercontent.com/nvm-sh/nvm/$NVM_VERSION/install.sh | bash

# configure php
RUN php artisan config:clear \
  && php artisan clear-compiled \
  && php artisan storage:link

# configure nvm
RUN . ${NVM_DIR}/nvm.sh \
  && npm ci \
  && npm run production


FROM php:8-apache as dashboard

COPY --from=builder --chown=www-data:www-data /app /var/www/html
WORKDIR /var/www/html

# install dependencies
RUN apt-get update \
  && apt-get install -y git unzip openssh-server libxml2-dev\
  && pecl install -o -f redis \
  && docker-php-ext-install mysqli pdo pdo_mysql soap \
  && docker-php-ext-enable redis \
  && rm -rf /var/lib/apt/* \
  && rm -rf /tmp/pear

# install sshd for app services
COPY ops/dashboard/sshd_config /etc/ssh
COPY ops/dashboard/ssh_setup.sh /tmp/ssh_setup.sh
RUN chmod +x /tmp/ssh_setup.sh \
  && (sleep 1; /tmp/ssh_setup.sh 2>&1 > /dev/null) \
  && rm -f /tmp/ssh_setup.sh \
  && echo "root:Docker!" | chpasswd

# retrieve CA certificate for SSL connection to Azure mysql
# https://docs.microsoft.com/en-us/azure/mysql/howto-configure-ssl
RUN mkdir /ssl
ENV MYSQL_ATTR_SSL_CA /ssl/BaltimoreCyberTrustRoot.crt.pem
RUN curl -Lo ${MYSQL_ATTR_SSL_CA} https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem

# configure apache
COPY ops/dashboard/dashboard.conf /etc/apache2/sites-available
RUN unlink /etc/apache2/sites-enabled/000-default.conf \
  && ln -s /etc/apache2/sites-available/dashboard.conf /etc/apache2/sites-enabled/000-dashboard.conf \
  && a2enmod rewrite headers

# configure php
COPY ops/dashboard/php.ini /tmp
RUN mv "/tmp/php.ini" "$PHP_INI_DIR/php.ini"

# enable entrypoint script
RUN mv ops/dashboard/entrypoint.sh / \
  && chmod +x /entrypoint.sh
CMD [ "/entrypoint.sh" ]

# expose ports for http and ssh access
EXPOSE 80 2222
