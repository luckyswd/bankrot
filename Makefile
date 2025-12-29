.PHONY: up down start stop install db-migrate cc check-code stan lint test

up: install
	docker-compose up -d

down:
	docker-compose down

start:
	docker-compose start

stop:
	docker-compose stop

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
