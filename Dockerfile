FROM php:8.2-fpm AS builder

WORKDIR /var/www/app

ENV TZ=UTC
ENV ZSH_VERSION=v1.2.1

# Install dependencies in a single layer
RUN ln -snf /usr/share/zoneinfo/"$TZ" /etc/localtime && echo "$TZ" > /etc/timezone \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        wget \
        zip \
        unzip \
        curl \
        libssl-dev \
        libxml2-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libsodium-dev \
        libmcrypt-dev \
        libmemcached-dev \
        supervisor \
        libzip-dev \
        freetds-dev \
        vim \
        libpq-dev \
        nodejs \
        cron \
        libonig-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # PHP extensions
    && docker-php-ext-install mbstring exif bcmath \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl zip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && composer self-update

# Install Oh My Zsh for better developer experience
RUN sh -c "$(wget -O- https://github.com/deluan/zsh-in-docker/releases/download/$ZSH_VERSION/zsh-in-docker.sh)" -- \
    -t frisk

# Final stage
FROM php:8.2-fpm

WORKDIR /var/www/app

# Copy installed extensions and configurations from builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Install only the necessary runtime packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        supervisor \
        libpq-dev \
        git \
        zip \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # Reinstall extensions to ensure they work properly
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd zip \
    # Install Node.js and npm properly
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash \
    && apt-get update \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy php.ini and supervisor configs
COPY php.ini /usr/local/etc/php/conf.d/
COPY ./docker/supervisor.conf.d /etc/supervisor/conf.d/

# Add healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php-fpm -t || exit 1

# Create proper user without specific UID/GID
RUN groupadd appuser && \
    useradd -g appuser -m appuser

# Set proper permissions
RUN mkdir -p /var/www/app && \
    chown -R appuser:appuser /var/www/app

# Set proper volume ownership
VOLUME /var/www/app

# Use non-root user for better security
USER appuser

# Switch back to root for supervisor
USER root

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
