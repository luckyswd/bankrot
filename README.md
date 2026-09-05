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

Сервер общий, на нём работают и другие проекты. Контейнеры описаны в
`compose.prod.yml`, наружу публикуется единственный порт **127.0.0.1:8094**,
перед ним стоит системный nginx хоста, который терминирует TLS.

### Разовая установка

1. Проверить, что порт свободен: `ss -ltnp | grep 8094`.
   Заняты соседними проектами: `8091`, `8092`, `8093`.
2. Склонировать ветку `staging` в `/srv/sites/bankrot.shefcode.tech`.
3. Создать в корне проекта файл `.env` из `compose.prod.env.example` и задать
   **новые** значения `APP_SECRET`, `JWT_PASSPHRASE`, `MYSQL_ROOT_PASSWORD`,
   `MYSQL_PASSWORD`. Значения из `backend/.env` и `backend/.env.dev` лежат в git
   открытым текстом — на сервере их использовать нельзя.
4. Собрать и поднять контейнеры:

   ```bash
   make prod-build
   make prod-up
   ```

5. Применить миграции и сгенерировать ключи JWT:

   ```bash
   make prod-migrate
   make prod-jwt-gen
   ```

6. Завести администратора (`ROLE_ADMIN`) — без него в систему не войти.
7. Настроить `/etc/nginx/sites-available/bankrot.shefcode.tech.conf`:
   редирект `http -> https`, TLS-сертификат, `client_max_body_size 100M`,
   `proxy_pass http://127.0.0.1:8094` и проброс заголовков
   `X-Forwarded-For`, `X-Forwarded-Proto`, `Host`.
8. Прописать в секретах GitHub `STG_SSH_HOST`, `STG_SSH_USER`, `STG_SSH_KEY`,
   `STG_PROJECT_DIR`.

### Обновление

Push в ветку `staging` запускает `.github/workflows/staging.yml`: PHPStan,
PHP CS Fixer, PHPUnit и проверка типов TypeScript, затем деплой по SSH —
`git reset --hard origin/staging`, пересборка образов, миграции, очистка кэша и
проверка `/api/v1/health`. При падении любой проверки деплой не выполняется.

### Что важно знать

- **Код на сервере руками не правим.** Деплой делает `git reset --hard`, любые
  локальные изменения в рабочей копии будут стёрты. Не отслеживаются только
  `.env` в корне и `backend/.env.local`.
- **Состояние живёт в docker-томах:** `bankrot-db` (база), `bankrot-var`
  (загруженные шаблоны документов, кэш, логи), `bankrot-jwt` (ключи JWT).
  Удаление тома `bankrot-var` уничтожит все загруженные шаблоны — резервное
  копирование не настроено.
- Откат: `git reset --hard <коммит>` в каталоге проекта и `make prod-build`,
  `make prod-up`. Миграции автоматически не откатываются.

