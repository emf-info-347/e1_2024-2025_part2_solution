FROM php:8.2-apache

# Installer les dépendances système et PDO MySQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Activer le module Apache Rewrite
RUN a2enmod rewrite

# Copier le code du service utilisateur
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html
