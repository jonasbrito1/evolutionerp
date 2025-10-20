FROM php:8.2-apache

WORKDIR /var/www/html

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    gd \
    bcmath

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Copiar código Laravel (vendor já deve estar pronto)
COPY . .

# Criar diretórios necessários se não existirem
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Definir DocumentRoot do Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Adicionar configuração de Directory para o public
RUN echo '<Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Criar arquivo .htaccess para Laravel
RUN mkdir -p /var/www/html/public && \
    echo '<IfModule mod_rewrite.c>' > /var/www/html/public/.htaccess && \
    echo '    <IfModule mod_negotiation.c>' >> /var/www/html/public/.htaccess && \
    echo '        Options -MultiViews -Indexes' >> /var/www/html/public/.htaccess && \
    echo '    </IfModule>' >> /var/www/html/public/.htaccess && \
    echo '    RewriteEngine On' >> /var/www/html/public/.htaccess && \
    echo '    RewriteCond %{REQUEST_FILENAME} !-f' >> /var/www/html/public/.htaccess && \
    echo '    RewriteCond %{REQUEST_FILENAME} !-d' >> /var/www/html/public/.htaccess && \
    echo '    RewriteRule ^(.*)$ index.php?path=$1 [QSA,L]' >> /var/www/html/public/.htaccess && \
    echo '</IfModule>' >> /var/www/html/public/.htaccess

# Criar script de entrada para rodar migrations na primeira vez
RUN echo '#!/bin/bash' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Aguardando banco de dados..."' >> /entrypoint.sh && \
    echo 'sleep 5' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'if [ ! -f /var/www/html/.env.setup ]; then' >> /entrypoint.sh && \
    echo '    echo "Primeira execução - rodando setup..."' >> /entrypoint.sh && \
    echo '    php artisan key:generate --force' >> /entrypoint.sh && \
    echo '    php artisan migrate --force' >> /entrypoint.sh && \
    echo '    php artisan db:seed --force' >> /entrypoint.sh && \
    echo '    touch /var/www/html/.env.setup' >> /entrypoint.sh && \
    echo '    echo "Setup concluído!"' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'chmod -R 755 storage bootstrap/cache' >> /entrypoint.sh && \
    echo 'chown -R www-data:www-data /var/www/html' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'exec apache2-foreground' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

# Permissões iniciais
RUN chmod 755 /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \;

# Expor porta
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
