# Dockerfile

# Usar una imagen base de PHP-FPM (por ejemplo, PHP 8.2)
# alpine es una versión ligera, ideal para contenedores
FROM php:8.2-fpm-alpine

# Instalar extensiones PHP necesarias
# pdo_mysql es CRUCIAL para tu conexión a MySQL con PDO
# mbstring es común para manejo de cadenas
# pdo_sqlite (opcional, si usaras SQLite)
# gd (opcional, si hicieras manipulación de imágenes)
# zip (opcional, si necesitaras descomprimir)
RUN docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-enable opcache

# Copiar el archivo php.ini personalizado (si lo tienes)
# Asegúrate de que tu php.ini esté en la raíz de tu proyecto local
COPY php.ini /usr/local/etc/php/conf.d/

# Establecer el directorio de trabajo dentro del contenedor
# Aquí es donde se copiará tu código
WORKDIR /app

# Copiar todo tu código de la aplicación al directorio de trabajo
# El '.' al final significa "copia todo el contenido del directorio actual"
COPY . /app

# Exponer el puerto por el que PHP-FPM escuchará (por defecto 9000)
# Nota: Railway mapeará esto a su puerto $PORT para acceso externo
EXPOSE 9000

# Comando para iniciar PHP-FPM
# Este es el proceso principal que escuchará las peticiones HTTP
# Railway se encargará de "enrutar" las peticiones a este puerto
CMD ["php-fpm"]

# Opcional: Si quieres usar el servidor web integrado de PHP (como en tu Procfile original)
# Y si NO vas a usar Nginx o Apache por separado
# Descomenta la siguiente línea y comenta las líneas EXPOSE 9000 y CMD ["php-fpm"]
# CMD ["php", "-S", "0.0.0.0:8000", "-t", "."] # Usa el puerto 8000 o el que prefieras
# En este caso, Railway asignará su $PORT a este puerto.
