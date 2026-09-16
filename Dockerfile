# Gunakan base image resmi PHP 8.2 dengan Apache
FROM php:8.2-apache

# Instal sistem paket: Python 3, pip, dan LibreOffice headless (untuk konversi Word ke PDF di Linux)
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    python3-pip \
    python3-venv \
    libreoffice-writer \
    libreoffice-calc \
    libreoffice-impress \
    poppler-utils \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan modul rewrite dan headers Apache
RUN a2enmod rewrite headers

# Konfigurasi batas upload PHP
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Direktori kerja
WORKDIR /var/www/html

# Salin kode aplikasi ke kontainer
COPY . /var/www/html

# Instal dependensi Python
RUN pip3 install --no-cache-dir --break-system-packages -r engine/requirements.txt

# Siapkan folder storage dan hak akses
RUN mkdir -p storage/uploads storage/results \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage

# Port standar
EXPOSE 80

CMD ["apache2-foreground"]
