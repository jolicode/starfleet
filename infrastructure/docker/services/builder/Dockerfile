ARG PROJECT_NAME

FROM ${PROJECT_NAME}_php-base

ARG NODEJS_VERSION=12.x
RUN echo "deb https://deb.nodesource.com/node_${NODEJS_VERSION} buster main" > /etc/apt/sources.list.d/nodejs.list \
    && apt-key adv --fetch-keys https://deb.nodesource.com/gpgkey/nodesource.gpg.key

# Default toys
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        make \
        nodejs \
        sudo \
        unzip \
    && apt-get clean \
    && npm install -g yarn \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Config
COPY etc/. /etc/
RUN adduser app sudo \
    && mkdir /var/log/php \
    && chmod 777 /var/log/php \
    && phpenmod app-default \
    && phpenmod app-builder

RUN mkdir -p /tmp/blackfire \
    && architecture=$(case $(uname -m) in i386 | i686 | x86) echo "i386" ;; x86_64 | amd64) echo "amd64" ;; aarch64 | arm64 | armv8) echo "arm64" ;; *) echo "amd64" ;; esac) \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux/$architecture | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

# Composer
COPY --from=composer:2.0.7 /usr/bin/composer /usr/bin/composer
RUN mkdir -p "/home/app/.composer/cache" \
    && chown app: /home/app/.composer -R

WORKDIR /home/app/application
