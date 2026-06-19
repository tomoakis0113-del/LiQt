FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN mkdir -p -m 0755 /usr/share/keyrings && \
    curl -fsSL https://pkg.cloudflare.com/cloudflare-public-v2.gpg | tee /usr/share/keyrings/cloudflare-public-v2.gpg >/dev/null && \
    echo 'deb [signed-by=/usr/share/keyrings/cloudflare-public-v2.gpg] https://pkg.cloudflare.com/cloudflared any main' | tee /etc/apt/sources.list.d/cloudflared.list && \
    apt-get update && apt-get install -y cloudflared

# --- ここを修正 ---
# TUNNEL_TOKEN が空ならエラーメッセージを出して終了、あれば実行
CMD ["/bin/sh", "-c", "if [ -z \"$TUNNEL_TOKEN\" ]; then echo 'ERROR: TUNNEL_TOKEN is not set. Exiting...'; exit 1; fi; exec cloudflared tunnel run"]