FROM composer:2

RUN apk add --no-cache $PHPIZE_DEPS gmp-dev \
    && docker-php-ext-install bcmath gmp
