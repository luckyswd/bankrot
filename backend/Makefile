composer-install:
	@bash -c 'if command -v composer &> /dev/null; then \
		composer install --no-dev --optimize-autoloader --no-interaction --no-scripts; \
	elif [ -f composer.phar ]; then \
		php composer.phar install --no-dev --optimize-autoloader --no-interaction --no-scripts; \
	else \
		echo "Composer not found. Installing..."; \
		php -r "copy(\"https://getcomposer.org/installer\", \"composer-setup.php\");"; \
		php composer-setup.php --quiet; \
		php composer.phar install --no-dev --optimize-autoloader --no-interaction --no-scripts; \
		rm -f composer-setup.php composer.phar; \
	fi'

db-migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

fix-permissions:
	@if [ ! -d var ]; then \
		mkdir -p var; \
	fi
	@chmod -R 777 var 2>/dev/null || true
	@if [ ! -d var/document-templates ]; then \
		mkdir -p var/document-templates; \
		chmod -R 777 var/document-templates 2>/dev/null || true; \
	fi

cc:
	php bin/console cache:clear --no-debug
	php bin/console doctrine:cache:clear-result
	php bin/console doctrine:cache:clear-query
	php bin/console doctrine:cache:clear-metadata


check-code: stan lint

stan:
	vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.neon

lint:
	vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.dist.php -v --diff --ansi

test-prepare:
	mkdir -p var
	rm -f var/test.db
	php bin/console doctrine:schema:create --no-interaction
	php bin/console doctrine:fixtures:load --group=seed --no-interaction

test: test-prepare
	php bin/phpunit --no-coverage

seed:
	php bin/console doctrine:fixtures:load --group=seed

jwt-gen:
	php bin/console lexik:jwt:generate-keypair