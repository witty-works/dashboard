FROM php:8.0 as builder

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
# todo: no-dev borken due to Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider
#RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader \
RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader \
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


FROM php:8.0-apache as dashboard

# see for azure app service example\
# https://github.com/Azure/app-service-builtin-images/blob/master/php/7.2.1-apache/Dockerfile

# enable apache modules
RUN a2enmod rewrite expires include deflate

# install additional php extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
         libpng-dev \
         libjpeg-dev \
         libpq-dev \
         libmcrypt-dev \
         libldap2-dev \
         libldb-dev \
         libicu-dev \
         libgmp-dev \
         libmagickwand-dev \
         libzip-dev \
         libonig-dev \
    && ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so \
    && ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && pecl install imagick-beta \
    && pecl install mcrypt \
    && pecl install redis \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd \
         mysqli \
         opcache \
         pdo \
         pdo_mysql \
         ldap \
         intl \
         gmp \
         zip \
         bcmath \
         mbstring \
         pcntl \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear \
    && rm -rf /var/lib/apt/lists/*

# setup app service and app relevant tooling
RUN apt-get update \
  && apt-get install -y git unzip openssh-server \
  && rm -rf /var/lib/apt/lists/*

# copy files
COPY ops/dashboard/files/ /


# setup sshd
RUN chmod +x /ssh_setup.sh \
  && (sleep 1; /ssh_setup.sh 2>&1 > /dev/null) \
  && rm -f /ssh_setup.sh \
  && echo "root:Docker!" | chpasswd

# retrieve CA certificate for SSL connection to Azure mysql
# https://docs.microsoft.com/en-us/azure/mysql/howto-configure-ssl
RUN mkdir /ssl
ENV MYSQL_ATTR_SSL_CA /ssl/BaltimoreCyberTrustRoot.crt.pem
RUN curl -Lo ${MYSQL_ATTR_SSL_CA} https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem

# copy dashboard application
COPY --from=builder --chown=www-data:www-data /app /var/www/html
WORKDIR /var/www/html

# create an empty html file in the public folder
# to keep load for frontdoor health checks low
RUN touch /var/www/html/public/_frontdoor.html

# configure apache
RUN unlink /etc/apache2/sites-enabled/000-default.conf \
  && ln -s /etc/apache2/sites-available/dashboard.conf /etc/apache2/sites-enabled/000-dashboard.conf

# enable entrypoint script
RUN chmod +x /entrypoint.sh
ENTRYPOINT [ "/entrypoint.sh" ]

# expose ports for http and ssh access
ENV PORT 8080
EXPOSE 8080 2222
