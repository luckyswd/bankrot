.PHONY: up down start stop install db-migrate cc check-code stan lint test

up: install
	@echo "🚀 Запуск Docker контейнеров..."
	docker-compose up -d
	@echo ""
	@echo "⏳ Ожидание запуска контейнеров..."
	@sleep 5
	@echo "🗄️  Выполнение миграций базы данных..."
	docker exec bankruptcy-php php bin/console doctrine:migrations:migrate --no-interaction
	@echo ""
	@echo "✅ Проект запущен!"
	@echo ""
	@echo "📍 Frontend: http://localhost"
	@echo "📍 Backend API: http://api.localhost"
	@echo "📍 Тестовая страница: http://localhost/test"
	@echo ""

down:
	@echo "🛑 Остановка и удаление контейнеров..."
	docker-compose down
	@echo "✅ Контейнеры остановлены"

start:
	@echo "▶️  Запуск контейнеров..."
	docker-compose start
	@echo "✅ Контейнеры запущены"

stop:
	@echo "⏸️  Остановка контейнеров..."
	docker-compose stop
	@echo "✅ Контейнеры остановлены"

install:
	@echo "📦 Проверка и копирование .env файлов..."
	@if [ ! -f frontend/.env.local ]; then \
		cp frontend/env.example frontend/.env.local; \
		echo "✅ Создан frontend/.env.local"; \
	else \
		echo "✓ frontend/.env.local уже существует"; \
	fi
	@if [ ! -f backend/.env ]; then \
		if [ -f backend/.env.dev ]; then \
			cp backend/.env.dev backend/.env; \
			echo "✅ Создан backend/.env из .env.dev"; \
		else \
			echo "⚠️  backend/.env будет создан автоматически при установке Symfony"; \
		fi \
	else \
		echo "✓ backend/.env существует"; \
	fi
	@echo "✅ Проверка завершена"
	@echo ""

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
