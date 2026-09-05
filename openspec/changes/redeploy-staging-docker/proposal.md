## Why

Юристы должны открывать систему по адресу `bankrot.shefcode.tech`, но текущий
деплой ведёт на старый сервер `appbankrot.ru` и раскладывает файлы по FTP/SCP
поверх работающего каталога: `.github/workflows/staging.yml` удаляет часть
файлов через `find ... -delete`, копирует новые и вручную спасает `config/jwt`
через `/tmp`. Пока идёт выкладка, дело в работе может отвалиться на середине
формирования пакета документов, а откатиться назад нечем.

Новый сервер — общий: на нём уже живут `alimenty`, `raschetnik`, `promoscout`.
Каждый проект там завёрнут в docker и спрятан за host-nginx, который слушает
443 и проксирует на свободный loopback-порт. Наш деплой должен встроиться в эту
схему, а не занять 80-й порт и не уронить соседей.

## What Changes

- **BREAKING** Старый способ деплоя (SCP файлов + выполнение `make` по SSH на
  голом сервере) заменяется на сборку и запуск docker-образов на сервере.
  Секреты GitHub `STG_FTP_*`, `STG_SERVER_BACKEND_DIR`, `STG_SERVER_FRONTEND_DIR`,
  `LOCAL_BACKEND_DIR`, `LOCAL_FRONTEND_DIR` перестают использоваться.
- **BREAKING** Адрес системы меняется на `https://bankrot.shefcode.tech`.
  Фронтенд и API живут на одном origin: SPA отдаётся с `/`, API доступен по
  `/api/v1/*`. Отдельного поддомена `api.*` больше нет.
- Появляется production-профиль docker compose (`compose.prod.yml`): php-fpm с
  вкомпилированным кодом и `composer install --no-dev`, nginx проекта, MySQL 8,
  собранная статика фронтенда. Vite dev-сервер в проде не запускается.
- Контейнер nginx проекта публикуется только на `127.0.0.1:8094` (порты 8091,
  8092, 8093 на сервере уже заняты). MySQL наружу не публикуется вовсе.
- Загруженные шаблоны документов (`backend/var/document-templates`), JWT-ключи
  (`backend/config/jwt`) и данные MySQL выносятся в именованные docker-тома,
  чтобы пересборка образа их не стирала.
- **BREAKING** `backend/.env.stage` перестаёт быть источником секретов: файл
  `.env.local` создаётся на сервере вручную и в репозиторий не попадает.
  Сейчас `APP_SECRET` и `JWT_PASSPHRASE` лежат в git открытым текстом, и на
  staging стоит `APP_ENV=dev` — оба факта исправляются.
- `.github/workflows/staging.yml` переписывается: джобы качества (PHPStan,
  PHP CS Fixer, PHPUnit, `tsc --noEmit`) сохраняются как гейт, шаг деплоя
  становится одним SSH-вызовом `git pull` + пересборка compose + миграции.
- Деплой считается успешным только после ответа `200` от `/api/v1/health`.

## Capabilities

### New Capabilities
- `deployment/staging`: наблюдаемое поведение развёрнутой системы на
  `bankrot.shefcode.tech` — маршрутизация SPA и API на одном домене, HTTPS,
  доступность документации API, health-проверка, сохранность загруженных
  шаблонов и учётных данных между выкладками, порядок прогона миграций и
  условия, при которых деплой не выполняется.

### Modified Capabilities
Нет: каталог `openspec/specs/` пуст, существующих спецификаций не затрагиваем.

## Impact

**Стадии `BankruptcyStage`.** Изменение инфраструктурное и не меняет поведение
ни одной из пяти стадий (`basic_info`, `pre_court`,
`judicial_procedure_initiation`, `judicial_procedure`, `judicial_report`).
Единственная связка с доменом — шаблоны документов всех пяти категорий
(`DocumentTemplate::$category`) лежат в `backend/var/document-templates` и
обязаны пережить выкладку.

**Справочники.** Восьми справочников (`Court`, `Creditor`, `Bailiff`,
`FinancialManager`, `Fns`, `Mchs`, `Rosgvardia`, `Gostekhnadzor`) изменение не
касается: правок кода в них нет, повторять ничего не нужно. На чистой базе они
наполняются заново — данные со старого сервера не переносятся.

**Файлы репозитория.**
- `.github/workflows/staging.yml` — переписывается целиком.
- `compose.prod.yml`, `docker/nginx/prod.conf`, `docker/php/Dockerfile.prod`,
  `docker/frontend/Dockerfile.prod` — новые файлы.
- `docker-compose.yml`, `Makefile`, `docker/nginx/default.conf` — локальная
  разработка не ломается, добавляются только prod-цели в `Makefile`.
- `backend/.env.stage`, `backend/.env.prod` — чистятся от секретов.
- `frontend/.env.production` — `VITE_API_URL` становится пустым
  (относительные пути `/api/v1`).
- `backend/config/packages/nelmio_cors.yaml` не меняется, но `CORS_ALLOW_ORIGIN`
  на сервере выставляется в `https://bankrot.shefcode.tech`.

**Инфраструктура сервера** (за пределами репозитория, выполняется руками):
каталог `/srv/sites/bankrot.shefcode.tech`, конфиг
`/etc/nginx/sites-available/bankrot.shefcode.tech.conf`, TLS-сертификат,
DNS-запись `bankrot.shefcode.tech`, SSH-ключ деплоя в секретах GitHub.

**Данные.** База создаётся пустой, наполняется миграциями Doctrine; перенос
данных с `appbankrot.ru` в рамки не входит. Первый пользователь заводится
вручную после выкладки.

## Вне рамок (Non-goals)

- Перенос данных со старого сервера (дамп MySQL, загруженные шаблоны).
- Переход на PostgreSQL 16, как в соседних проектах на этом сервере.
- Отдельный production-контур: `bankrot.shefcode.tech` — единственная среда,
  разделения staging/prod не вводим.
- Сборка образов в CI и registry (GHCR): образы собираются на сервере.
- Zero-downtime и автоматический откат: допускается простой на время
  пересборки контейнеров.
- Варниш-кэш перед проектом, как у `alimenty` и `raschetnik`.
- Мониторинг, алертинг, ротация логов, резервное копирование базы.
- Правки в `docker/mysql/var/mysql` — каталог остаётся артефактом локальной
  разработки и продолжает игнорироваться git.
