FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    zlib1g-dev \
    wget \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by TYPO3
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        intl \
        opcache \
        soap

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files and install TYPO3
RUN composer create-project typo3/cms-base-distribution:^13.4 . --no-dev --no-interaction \
    && composer config --no-plugins allow-plugins.typo3/cms-composer-installers true \
    && composer config --no-plugins allow-plugins.typo3/class-alias-loader true

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copy setup script
COPY setup/install-typo3.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/install-typo3.sh

# Copy Apache configuration
COPY typo3/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache and run setup
CMD install-typo3.sh && apache2-foreground
