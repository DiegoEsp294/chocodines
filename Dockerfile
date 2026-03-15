FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Permitir .htaccess en el document root
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Verificar que el driver está disponible (falla el build si no está)
RUN php -m | grep -i pdo_pgsql

EXPOSE 80
