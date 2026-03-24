FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

# Enable Apache mod_rewrite (optional but good for many PHP apps)
RUN a2enmod rewrite
