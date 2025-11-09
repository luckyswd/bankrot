db-migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

cc:
	php bin/console cache:clear

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