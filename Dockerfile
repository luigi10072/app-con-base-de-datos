# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Habilita el módulo de reescritura de Apache (mod_rewrite) si lo necesitas para URLs amigables
# RUN a2enmod rewrite

# Copia los archivos de tu aplicación al directorio de trabajo de Apache
COPY . /var/www/html/

# Configura las extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql

# Copia la configuración de PHP para mostrar errores en stderr
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# --- COMANDOS DE DEPURACIÓN ---
# Lista los archivos copiados en el directorio de Apache
RUN ls -la /var/www/html/

# Muestra el contenido del php.ini para verificar que se copió correctamente
RUN cat /usr/local/etc/php/conf.d/php.ini

# Verifica si pdo_mysql está cargado (esto se ejecutará en tiempo de construcción)
# Esto no es un error si falla, pero nos da información.
RUN php -m | grep pdo_mysql || echo "pdo_mysql not listed by php -m"
# --- FIN COMANDOS DE DEPURACIÓN ---

# Opcional: Configuración de Apache para suprimir la advertencia de ServerName
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

# Exponer el puerto 80 (Apache escucha en este puerto)
EXPOSE 80

# Comando por defecto para iniciar Apache (ya incluido en la imagen base)
# CMD ["apache2-foreground"]
