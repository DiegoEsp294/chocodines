FROM php:8.2-apache

# Instalar extensión PDO PostgreSQL
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Permitir .htaccess en el document root
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copiar archivos de la aplicación
COPY . /var/www/html/

EXPOSE 80
