# 📁 Bankruptcy Management System

Система управления банкротством с веб-интерфейсом на React и API на Symfony.

## 🏗️ Структура проекта

```
bankruptcy/
├── backend/                    # Symfony 6.4 Backend API
│   ├── bin/                   # Исполняемые файлы (console)
│   ├── config/                # Конфигурация приложения
│   │   └── packages/         # Конфиги пакетов (CORS, framework и т.д.)
│   ├── public/               # Публичная директория (index.php)
│   ├── src/                  # Исходный код
│   │   └── Controller/      # API контроллеры
│   ├── var/                  # Кеш и логи
│   ├── vendor/               # Зависимости Composer
│   ├── .env                  # Переменные окружения
│   └── composer.json         # PHP зависимости
│
├── frontend/                  # React Frontend
│   ├── src/                  # Исходный код
│   │   ├── components/      # React компоненты
│   │   ├── pages/           # Страницы (TestApi, Login и т.д.)
│   │   ├── context/         # React Context
│   │   └── App.jsx          # Главный компонент
│   ├── public/              # Статические файлы
│   ├── .env.local           # Переменные окружения (не в git)
│   ├── env.example          # Пример .env файла
│   ├── vite.config.js       # Конфигурация Vite
│   └── package.json         # NPM зависимости
│
├── docker/                   # Docker конфигурации
│   ├── nginx/
│   │   └── default.conf     # Nginx конфигурация
│   ├── php/
│   │   ├── Dockerfile       # PHP 8.4 + расширения
│   │   └── config/
│   │       └── php.ini      # PHP настройки
│   ├── frontend/
│   │   └── Dockerfile       # Node.js 20 Alpine
│   └── mysql/
│       └── var/mysql/       # Данные MySQL
│
├── docker-compose.yml        # Оркестрация контейнеров
├── Makefile                  # Команды для управления проектом
└── README.md                 # Этот файл
```

## 🚀 Быстрый старт

### Требования
- Docker
- Docker Compose
- Make

### Запуск проекта

**Одна команда для запуска всего:**

```bash
make up
```

### Другие команды

```bash
make down    # Остановить и удалить контейнеры
make start   # Запустить остановленные контейнеры
make stop    # Остановить контейнеры (не удаляя)
```

## 🌐 Доступ к приложению

После запуска проект доступен по адресам:

- **Frontend**: http://localhost
- **Backend API**: http://api.localhost
- **Тестовая страница**: http://localhost/test

---

## 🚢 Развёртывание на сервере

Рабочий адрес — **https://bankrot.shefcode.tech**. Фронтенд и API живут на
одном домене: SPA отдаётся с `/`, API доступен по `/api/v1/*`.

Сервер общий, на нём работают `raschetnik.by`, `alimenty.by` и
`promo.shefcode.tech`. Схема та же, что у соседей: контейнеры описаны в
`docker-compose.prod.yml`, наружу публикуется единственный порт
**127.0.0.1:8094**, перед ним стоит системный nginx хоста, который терминирует
TLS. Код приезжает на сервер по `rsync`, git на сервере не нужен.

### Разовая установка

1. A-запись `bankrot.shefcode.tech` -> IP сервера.
2. Проверить, что порт свободен: `ss -ltnp | grep 8094`.
   Заняты соседними проектами: `8091` (promo), `8092` (raschetnik),
   `8093` (alimenty).
3. Создать каталог и залить в него код (первый раз — вручную, дальше это делает
   CI):

   ```bash
   mkdir -p /srv/sites/bankrot.shefcode.tech
   ```

4. Создать `.env.prod` из `.env.prod.example` и задать **новые** значения
   `APP_SECRET`, `JWT_PASSPHRASE`, `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`.
   Значения из `backend/.env` и `backend/.env.dev` лежат в git открытым
   текстом — на сервере их использовать нельзя.

   ```bash
   cd /srv/sites/bankrot.shefcode.tech
   cp .env.prod.example .env.prod && nano .env.prod
   ```

5. Собрать и поднять контейнеры, применить миграции, сгенерировать ключи JWT:

   ```bash
   make prod-up
   make prod-migrate
   make prod-jwt-gen
   ```

6. **Сменить пароль администратора.** Миграция `Version20251104101442` заводит
   пользователя `admin` с хэшем пароля, который лежит в git — до смены пароля
   вход в систему открыт всем, кто видел репозиторий:

   ```bash
   docker compose --env-file .env.prod -f docker-compose.prod.yml \
       exec -u www-data php php bin/console app:user-create-admin --username=admin
   ```

7. Настроить хостовый nginx по шаблону
   [`deploy/nginx-bankrot.shefcode.tech.conf`](deploy/nginx-bankrot.shefcode.tech.conf),
   затем выпустить сертификат: `certbot --nginx -d bankrot.shefcode.tech`.
8. Прописать в секретах GitHub `DEPLOY_HOST`, `DEPLOY_USER`,
   `DEPLOY_SSH_PASSWORD`, при необходимости `DEPLOY_PORT`.

### Обновление

Push в ветку `staging` запускает `.github/workflows/staging.yml`: PHPStan,
PHP CS Fixer, PHPUnit и проверка типов TypeScript, затем деплой — `rsync`
репозитория на сервер, пересборка образов, миграции, очистка кэша и проверка
`/api/v1/health`. При падении любой проверки деплой не выполняется.

### Что важно знать

- **Код на сервере руками не правим.** Деплой делает `rsync --delete`, любые
  локальные изменения будут стёрты. Из синхронизации исключены `.env.prod`,
  `backend/var`, `vendor` и `node_modules`.
- **Состояние живёт в docker-томах:** `db_data` (база), `app_var` (загруженные
  шаблоны документов, кэш, логи), `app_jwt` (ключи JWT). Удаление тома
  `app_var` уничтожит все загруженные шаблоны — резервное копирование не
  настроено. Обычный `make prod-down` безопасен, `down -v` — нет.
- Откат: выкатить предыдущий коммит через **Actions -> Run workflow** на нужной
  ветке. Миграции автоматически не откатываются.

