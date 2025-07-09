# Usa una imagen base de Debian para mayor control
FROM debian:bookworm-slim

# Instala Nginx, PHP y PHP-FPM
# También instala las extensiones PHP necesarias (pdo_mysql)
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        nginx \
        php8.2-fpm \
        php8.2-mysql \
        php8.2-cli \
        php8.2-common \
        php8.2-curl \
        php8.2-json \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-zip \
    && rm -rf /var/lib/apt/lists/* && \
    apt-get clean # Agregado para limpiar el cache de apt inmediatamente

# Copia los archivos de tu aplicación al directorio de trabajo de Nginx
# El directorio /var/www/html es el DocumentRoot por defecto de Nginx
COPY . /var/www/html/

# Configura los permisos correctos para los archivos de la aplicación
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# --- Configuración de PHP-FPM ---
# Configura PHP-FPM para escuchar en el puerto 9000 (TCP)
# Esto es más fiable que un socket UNIX en algunos entornos de contenedor
RUN sed -i 's/^listen = \/run\/php\/php8.2-fpm.sock/listen = 127.0.0.1:9000/' /etc/php/8.2/fpm/pool.d/www.conf \
    && sed -i 's/^;listen.owner = www-data/listen.owner = www-data/' /etc/php/8.2/fpm/pool.d/www.conf \
    && sed -i 's/^;listen.group = www-data/listen.group = www-data/' /etc/php/8.2/fpm/pool.d/www.conf \
    && sed -i 's/^;listen.mode = 0660/listen.mode = 0660/' /etc/php/8.2/fpm/pool.d/www.conf

# Configuración de PHP para mostrar errores en stderr (para logs de Railway)
COPY php.ini /etc/php/8.2/fpm/conf.d/99-custom.ini
# También para CLI, por si acaso
COPY php.ini /etc/php/8.2/cli/conf.d/99-custom.ini


# --- Configuración de Nginx ---
# Elimina la configuración predeterminada de Nginx
RUN rm /etc/nginx/sites-enabled/default

# Copia tu configuración personalizada de Nginx
COPY nginx-app.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Expone el puerto 8080 (donde Nginx escuchará)
EXPOSE 8080

# Comando para iniciar Nginx y PHP-FPM en primer plano
# Esto asegura que ambos servicios se ejecuten y que los logs se capturen.
CMD service php8.2-fpm start && nginx -g "daemon off;"
