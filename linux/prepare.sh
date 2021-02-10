#!/bin/sh

# prepare php-src code and micro code

PHP_VER=${PHP_VER-8.0.2}
MICRO_VER=${MICRO_VER-master}

git clone --single-branch -b php-${PHP_VER} https://github.com/php/php-src php-src &&
git clone --single-branch -b ${MICRO_VER} https://github.com/longyan/phpmicro php-src/sapi/micro &&
cat >.dockerignore <<EOF
php-src/.git
php-src/sapi/micro/.git
EOF
