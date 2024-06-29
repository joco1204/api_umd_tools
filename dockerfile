# Usar la imagen oficial de PHP con Apache
FROM php:7.4-apache

# Copiar los archivos del proyecto en el contenedor
COPY . /var/www/html/

# Instalar las extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar permisos para el directorio de Apache
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Establecer el archivo .htaccess
COPY .htaccess /var/www/html/

# Exponer el puerto 80 para el servidor web
EXPOSE 80
