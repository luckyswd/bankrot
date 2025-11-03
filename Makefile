.PHONY: up down start stop

up: stop
	docker-compose up -d
	@echo "✅ Проект запущен"
	@echo "Backend:  http://api.localhost"
	@echo "Frontend: http://localhost"

down: ## Остановить и удалить контейнеры
	docker-compose down

start: stop ## Запустить остановленные контейнеры
	docker-compose start

stop: ## Остановить контейнеры (без удаления)
	docker-compose stop
