# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Habilita el módulo de reescritura de Apache (mod_rewrite) si lo necesitas para URLs amigables
# RUN a2enmod rewrite

# Copia los archivos de tu aplicación al directorio de trabajo de Apache
# El directorio /var/www/html es el DocumentRoot por defecto de Apache en esta imagen
COPY . /var/www/html/

# Configura las extensiones PHP necesarias
# PDO MySQL es crucial para tu aplicación
RUN docker-php-ext-install pdo_mysql

# Opcional: Configuración de PHP para mostrar errores (solo para desarrollo, deshabilitar en producción)
# COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Exponer el puerto 80 (Apache escucha en este puerto)
EXPOSE 80

# Comando por defecto para iniciar Apache (ya incluido en la imagen base)
# CMD ["apache2-foreground"]
