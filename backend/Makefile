db-migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

cc:
	php bin/console cache:clear

check-code: stan lint

stan:
	vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.neon

lint:
	vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.dist.php -v --diff --ansi

test:
	 php bin/phpunit --no-coverage

seed:
	php bin/console doctrine:fixtures:load --group=seed