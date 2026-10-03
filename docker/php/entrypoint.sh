#!/bin/sh
set -eu

if [ "${1:-}" = "php-fpm" ]; then
	if [ ! -d "src/data/articles" ]; then
		printf "%s\n" "Не найден каталог исходных изображений: src/data/articles" >&2
		exit 1
	fi

	mkdir -p "public/data/articles"
	cp -R "src/data/articles/." "public/data/articles/"
fi

exec docker-php-entrypoint "$@"
