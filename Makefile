DC ?= docker compose
DC_PROD ?= docker compose -f compose.prod.yml
PHP_SERVICE ?= php
FRONTEND_SERVICE ?= frontend
PHP_EXEC = $(DC) exec -T $(PHP_SERVICE)
PROD_PHP_EXEC = $(DC_PROD) exec -T -u www-data $(PHP_SERVICE)

.DEFAULT_GOAL := help

.PHONY: help up down start stop restart build ps logs logs-php logs-frontend \
        install sh sh-frontend \
        stan lint lint-fix test check-code cc db-migrate seed jwt-gen \
        prod-build prod-up prod-down prod-ps prod-logs prod-migrate prod-cc \
        prod-jwt-gen prod-sh

help:
	@echo "Контейнеры:"
	@echo "  make up             Подготовить .env и поднять контейнеры"
	@echo "  make down           Остановить и удалить контейнеры"
	@echo "  make start          Запустить остановленные контейнеры"
	@echo "  make stop           Остановить контейнеры, не удаляя"
	@echo "  make restart        stop + start"
	@echo "  make build          Пересобрать образы"
	@echo "  make ps             Статус контейнеров"
	@echo "  make logs           Логи всех сервисов"
	@echo "  make sh             Shell в контейнере php"
	@echo "  make sh-frontend    Shell в контейнере frontend"
	@echo ""
	@echo "Backend (выполняется внутри контейнера php):"
	@echo "  make check-code     stan + lint"
	@echo "  make stan           PHPStan"
	@echo "  make lint           PHP CS Fixer, только проверка"
	@echo "  make lint-fix       PHP CS Fixer, применить исправления"
	@echo "  make test           PHPUnit на отдельной SQLite-базе"
	@echo "  make cc             Очистить кэш Symfony и Doctrine"
	@echo "  make db-migrate     Применить миграции Doctrine"
	@echo "  make seed           Загрузить фикстуры группы seed"
	@echo "  make jwt-gen        Сгенерировать пару JWT-ключей"
	@echo ""
	@echo "Сервер (compose.prod.yml, требует .env в корне):"
	@echo "  make prod-build     Собрать prod-образы"
	@echo "  make prod-up        Поднять prod-контейнеры"
	@echo "  make prod-down      Остановить prod-контейнеры"
	@echo "  make prod-ps        Статус prod-контейнеров"
	@echo "  make prod-logs      Логи prod-контейнеров"
	@echo "  make prod-sh        Shell в контейнере bankrot-php"
	@echo "  make prod-migrate   Применить миграции на сервере"
	@echo "  make prod-cc        Очистить кэш на сервере"
	@echo "  make prod-jwt-gen   Сгенерировать JWT-ключи на сервере"

up: install
	$(DC) up -d

down:
	$(DC) down

start:
	$(DC) start

stop:
	$(DC) stop

restart: stop start

build:
	$(DC) build

ps:
	$(DC) ps

logs:
	$(DC) logs -f --tail=100

logs-php:
	$(DC) logs -f --tail=100 $(PHP_SERVICE)

logs-frontend:
	$(DC) logs -f --tail=100 $(FRONTEND_SERVICE)

sh:
	$(DC) exec $(PHP_SERVICE) sh

sh-frontend:
	$(DC) exec $(FRONTEND_SERVICE) sh

install:
	@echo "Проверка .env файлов"
	@if [ -f frontend/.env.local ]; then \
		echo "  frontend/.env.local уже существует"; \
	else \
		cp frontend/.env frontend/.env.local; \
		echo "  создан frontend/.env.local из frontend/.env"; \
	fi
	@if [ -f backend/.env ]; then \
		echo "  backend/.env уже существует"; \
	elif [ -f backend/.env.dev ]; then \
		cp backend/.env.dev backend/.env; \
		echo "  создан backend/.env из backend/.env.dev"; \
	else \
		echo "  backend/.env.dev не найден, создайте backend/.env вручную"; \
	fi

check-code:
	$(PHP_EXEC) make check-code

stan:
	$(PHP_EXEC) make stan

lint:
	$(PHP_EXEC) make lint

lint-fix:
	$(PHP_EXEC) make lint-fix

test:
	$(PHP_EXEC) make test

cc:
	$(PHP_EXEC) make cc

db-migrate:
	$(PHP_EXEC) make db-migrate

seed:
	$(PHP_EXEC) make seed

jwt-gen:
	$(PHP_EXEC) make jwt-gen

prod-build:
	$(DC_PROD) build

prod-up:
	$(DC_PROD) up -d

prod-down:
	$(DC_PROD) down

prod-ps:
	$(DC_PROD) ps

prod-logs:
	$(DC_PROD) logs -f --tail=100

prod-sh:
	$(DC_PROD) exec $(PHP_SERVICE) sh

prod-migrate:
	$(PROD_PHP_EXEC) make db-migrate

prod-cc:
	$(PROD_PHP_EXEC) make cc

prod-jwt-gen:
	$(PROD_PHP_EXEC) make jwt-gen
