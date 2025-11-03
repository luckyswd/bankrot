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

