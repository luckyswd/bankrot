.PHONY: up down start stop install

up: install
	@echo "🚀 Запуск Docker контейнеров..."
	docker-compose up -d --build
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
		echo "⚠️  backend/.env будет создан автоматически при установке Symfony"; \
	else \
		echo "✓ backend/.env существует"; \
	fi
	@echo "✅ Проверка завершена"
	@echo ""
