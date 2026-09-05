CONSOLE = php bin/console
TEST_DB_URL = sqlite:///$(CURDIR)/var/test.db

.DEFAULT_GOAL := help

.PHONY: help install composer-install db-migrate db-diff cc \
        check-code stan lint lint-fix test test-prepare seed jwt-gen

help:
	@echo "Разработка:"
	@echo "  make install         composer install с dev-зависимостями"
	@echo "  make check-code      stan + lint"
	@echo "  make stan            PHPStan"
	@echo "  make lint            PHP CS Fixer, только проверка"
	@echo "  make lint-fix        PHP CS Fixer, применить исправления"
	@echo "  make test            Пересоздать тестовую базу и запустить PHPUnit"
	@echo ""
	@echo "База данных:"
	@echo "  make db-migrate      Применить миграции"
	@echo "  make db-diff         Сгенерировать миграцию по изменениям сущностей"
	@echo "  make seed            Загрузить фикстуры группы seed"
	@echo ""
	@echo "Обслуживание:"
	@echo "  make cc              Очистить кэш Symfony и Doctrine"
	@echo "  make jwt-gen         Сгенерировать пару JWT-ключей"
	@echo "  make composer-install Установка без dev-зависимостей, для деплоя"

install:
	composer install --no-interaction --prefer-dist

composer-install:
	@bash -c 'set -e; \
	install_args="install --no-dev --optimize-autoloader --no-interaction --no-scripts"; \
	if command -v composer > /dev/null 2>&1; then \
		composer $$install_args; \
	elif [ -f composer.phar ]; then \
		php composer.phar $$install_args; \
	else \
		echo "Composer не найден, скачиваем"; \
		php -r "copy(\"https://getcomposer.org/installer\", \"composer-setup.php\");"; \
		php composer-setup.php --quiet; \
		rm -f composer-setup.php; \
		php composer.phar $$install_args; \
		rm -f composer.phar; \
	fi'

db-migrate:
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

db-diff:
	$(CONSOLE) doctrine:migrations:diff --no-interaction

cc:
	$(CONSOLE) cache:clear --no-debug
	$(CONSOLE) doctrine:cache:clear-result
	$(CONSOLE) doctrine:cache:clear-query
	$(CONSOLE) doctrine:cache:clear-metadata

check-code: stan lint

stan:
	vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.neon

lint:
	vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.dist.php -v --diff --ansi

lint-fix:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -v --diff --ansi

test-prepare:
	mkdir -p var
	rm -rf var/cache/test
	rm -f var/test.db
	DATABASE_URL="$(TEST_DB_URL)" $(CONSOLE) doctrine:schema:create --no-interaction
	DATABASE_URL="$(TEST_DB_URL)" $(CONSOLE) doctrine:fixtures:load --group=seed --no-interaction

test: test-prepare
	php bin/phpunit --no-coverage

seed:
	$(CONSOLE) doctrine:fixtures:load --group=seed

jwt-gen:
	$(CONSOLE) lexik:jwt:generate-keypair
