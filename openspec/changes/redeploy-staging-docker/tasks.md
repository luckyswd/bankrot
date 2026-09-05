## 1. Health-эндпоинт бэкенда

- [x] 1.1 Создать `backend/src/Controller/HealthController.php` с маршрутом
  `GET /api/v1/health`, возвращающим `200` и JSON-статус без авторизации;
  проверить `curl -i http://api.localhost/api/v1/health` в локальном docker —
  ответ `200` без заголовка `Authorization` (правило `PUBLIC_ACCESS` для этого
  пути уже есть в `backend/config/packages/security.yaml`, эндпоинт отсутствует)
- [x] 1.2 Добавить функциональный тест `backend/tests/Controller/HealthControllerTest.php`
  по образцу `ContractsControllerTest.php`, проверяющий код `200` для
  анонимного запроса; убедиться, что `make test` его выполняет

## 2. Prod-образы

- [x] 2.1 Создать `docker/php/Dockerfile.prod`: база `php:8.4-fpm-alpine`, те же
  расширения, что в `docker/php/Dockerfile`, `COPY backend/`,
  `composer install --no-dev --optimize-autoloader --no-scripts`,
  `config/php.production.ini` вместо `php.ini`; проверить локально
  `docker build -f docker/php/Dockerfile.prod .` — сборка проходит, в образе
  есть `vendor/autoload_runtime.php`
- [x] 2.2 Создать `docker/frontend/Dockerfile.prod` как multi-stage:
  `node:20-alpine` -> `npm ci && npm run build`, затем копирование `dist` в
  `nginx:stable-alpine`; проверить, что в итоговом образе лежит
  `/usr/share/nginx/html/index.html`
- [x] 2.3 Создать `docker/nginx/prod.conf`: один `server` на `:80`,
  `location /api` -> `fastcgi_pass php:9000` (включая `/api/doc`),
  `location /` -> `try_files $uri /index.html`, `client_max_body_size 100M`,
  проброс `HTTPS`/`X-Forwarded-Proto` в fastcgi-параметры; проверить
  `nginx -t` внутри собранного образа

## 3. compose.prod.yml

- [x] 3.1 Создать `compose.prod.yml` с `name: bankrot-prod` (без своего имени
  проект совпадёт с dev по имени каталога и пересоздаст dev-контейнеры),
  сервисами `nginx`, `php`, `mysql`, именами контейнеров `bankrot-nginx`,
  `bankrot-php`, `bankrot-mysql`, отдельной сетью и публикацией только
  `127.0.0.1:${HTTP_PORT:-8094}:80` у nginx; проверить
  `docker compose -f compose.prod.yml config` — валидный вывод, у `mysql` нет
  секции `ports`
- [x] 3.2 Описать именованные тома `bankrot-db` -> `/var/lib/mysql`,
  `bankrot-var` -> `/var/www/html/var`, `bankrot-jwt` -> `/var/www/html/config/jwt`;
  проверить, что `docker compose -f compose.prod.yml config --volumes`
  перечисляет ровно эти три тома и bind-mount исходников нет ни у одного сервиса
- [x] 3.3 Добавить `healthcheck` MySQL через `mysqladmin ping` и
  `depends_on: {mysql: {condition: service_healthy}}` у php, `depends_on: php`
  у nginx; проверить локальным запуском `docker compose -f compose.prod.yml up -d`,
  что php стартует только после `healthy` базы
- [x] 3.4 Добавить `.env` рядом с `compose.prod.yml` в `.gitignore` и создать
  отслеживаемый образец `compose.prod.env.example` с ключами `HTTP_PORT`,
  `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`;
  проверить `git status` — реальный `.env` не отслеживается

## 4. Конфигурация окружения

- [x] 4.1 Вычистить `backend/.env.prod` от реальных значений `APP_SECRET`,
  `JWT_PASSPHRASE` и пароля базы, удалить `backend/.env.stage` (использовался
  только старым шагом `cp .env.stage .env`); проверить
  `git grep -n "7dbe335d9e2699cc7f51374cd9ca9360\|a415378c7a137af4dea44f5d207da97c"` —
  совпадений в отслеживаемых файлах нет, кроме локального `backend/.env`
- [x] 4.2 Передавать секреты через `environment:` в `compose.prod.yml`, а не
  через `backend/.env.local` (Symfony грузит `.env.prod` после `.env.local`, и
  он бы его перекрыл; реальные переменные окружения имеют приоритет над всеми
  dotenv-файлами). Обязательные ключи объявить как `${VAR:?...}`; проверить,
  что `docker compose -f compose.prod.yml config` падает без `.env`
- [x] 4.3 Настроить доверие к обратному прокси: `framework.trusted_proxies` и
  `trusted_headers` в `backend/config/packages/framework.yaml` через переменную
  окружения; проверить, что при запросе с `X-Forwarded-Proto: https` Symfony
  считает запрос защищённым (`Request::isSecure()` в функциональном тесте или
  `dump` в dev-контуре)
- [x] 4.4 Заменить `VITE_API_URL` в `frontend/.env.production` на пустое
  значение, чтобы `src/config/api.js` собирал относительный `/api/v1`;
  проверить `npm run build` и `grep -r "appbankrot" frontend/dist` — совпадений нет
- [x] 4.5 Починить цепочку миграций: добавить в `Version20250124000000` `skipIf`
  по наличию таблицы `contracts` (она создаётся только в `Version20251107134535`,
  из-за чего `make db-migrate` падал на пустой базе); проверить, что все
  миграции проходят на чистой базе
- [x] 4.6 Добавить миграцию `Version20260905090000`, приводящую `contracts` к
  сущностям: уникальный индекс по `court_id` заменить обычным, удалить
  `manager_id`, `sex`, `judicial_realization_decision_date`,
  `judicial_realization_resolution_date` и внешний ключ `FK_950A973783E3463`,
  все операции защитить проверками существования; проверить
  `doctrine:schema:validate` на чистой базе — схема в синхроне, и прогон на
  копии существующей базы — миграция применяется без ошибок

## 5. Workflow деплоя

- [x] 5.1 Заменить `working-directory: ${{ secrets.LOCAL_BACKEND_DIR }}` и
  `LOCAL_FRONTEND_DIR` на явные `backend` / `frontend` во всех джобах качества
  `.github/workflows/staging.yml`; проверить `workflow_dispatch`-запуском, что
  PHPStan, PHP CS Fixer, PHPUnit и `tsc --noEmit` проходят
- [x] 5.2 Удалить джобы `deploy-backend` и `deploy-frontend` целиком со всей
  логикой бэкапа `config/jwt` через `/tmp`; проверить, что в файле не осталось
  упоминаний `appleboy/scp-action` и `STG_FTP_*`
- [x] 5.3 Добавить джоб `deploy`, зависящий от всех джобов качества, с одним
  шагом `appleboy/ssh-action` по ключу (`STG_SSH_HOST`, `STG_SSH_USER`,
  `STG_SSH_KEY`, `STG_PROJECT_DIR`): `git fetch --prune` +
  `git reset --hard origin/staging`, `docker compose -f compose.prod.yml build`,
  `up -d`, `exec -T php make db-migrate`, `exec -T php make cc`; проверить, что
  условие джоба не пропускает деплой при падении любой проверки
- [x] 5.4 Добавить в конец скрипта деплоя проверку
  `curl -fsS --retry 10 --retry-delay 3 http://127.0.0.1:${HTTP_PORT}/api/v1/health`;
  проверить, что при заведомо сломанном контейнере джоб падает, а не завершается
  успешно

## 6. Локальные команды и документация

- [x] 6.1 Добавить в корневой `Makefile` цели `prod-build`, `prod-up`,
  `prod-down`, `prod-logs`, `prod-migrate` поверх `compose.prod.yml`, не трогая
  существующие цели (`stan`, `lint`, `test`, `cc`, `db-migrate`, `jwt-gen`
  переименовывать нельзя — их вызывает CI); проверить `make` — новые цели видны
  в справке
- [x] 6.2 Описать в `README.md` разовую установку на сервере из «Migration Plan»
  `design.md`: каталог `/srv/sites/bankrot.shefcode.tech`, `.env`-файлы,
  `make jwt-gen`, заведение администратора, конфиг host-nginx на
  `127.0.0.1:8094`, правило «код на сервере руками не правим» и предупреждение о
  потере тома `bankrot-var` (загруженные шаблоны документов)

## 7. Установка на сервере

- [ ] 7.1 Убедиться, что порт свободен: `ss -ltnp | grep 8094` — пусто; при
  занятости выбрать другой и записать его в `HTTP_PORT`
- [ ] 7.2 Клонировать ветку `staging` в `/srv/sites/bankrot.shefcode.tech`,
  создать в корне проекта `.env` из `compose.prod.env.example` с новыми
  значениями `APP_SECRET`, `JWT_PASSPHRASE`, `MYSQL_ROOT_PASSWORD`,
  `MYSQL_PASSWORD` (старые из git считать скомпрометированными); проверить
  `docker compose -f compose.prod.yml config`
- [ ] 7.3 Собрать и поднять контейнеры, дождаться `healthy` у `bankrot-mysql`,
  выполнить `make db-migrate` и `make jwt-gen`; проверить `docker ps` — три
  контейнера `bankrot-*` в статусе `Up`, соседние проекты не перезапускались
- [ ] 7.4 Завести администратора (`ROLE_ADMIN`) в пустой базе и проверить вход
  через `POST /api/v1/login`
- [ ] 7.5 Настроить `/etc/nginx/sites-available/bankrot.shefcode.tech.conf`:
  `proxy_pass http://127.0.0.1:8094`, `client_max_body_size 100M`, редирект
  `http -> https`, TLS-сертификат; проверить `nginx -t` и
  `systemctl reload nginx` без ошибок

## 8. Приёмка по спецификации

- [ ] 8.1 Проверить сценарии «Единый адрес системы»: открыть
  `https://bankrot.shefcode.tech/contracts`, обновить страницу на
  `/contract/:id` (ожидается `200`, не `404`), выполнить вход и убедиться в
  DevTools, что preflight-запросов `OPTIONS` к API нет
- [ ] 8.2 Проверить изоляцию: `docker ps` показывает публикацию только
  `127.0.0.1:8094`, порт MySQL не опубликован; домены `alimenty`, `raschetnik`,
  `promoscout` продолжают отвечать
- [ ] 8.3 Проверить сохранность данных: загрузить шаблон категории `pre_court`,
  выполнить повторный деплой и убедиться, что шаблон на месте, документ по нему
  формируется, а ранее выданный JWT продолжает приниматься
- [ ] 8.4 Проверить безопасность: `/api/doc` без учётных данных возвращает `401`,
  с учётными данными `ROLE_ADMIN` — страницу документации; вызвать заведомо
  ошибочный запрос к API и убедиться, что в ответе нет трассировки стека
  (окружение `prod`)
- [ ] 8.5 Проверить гейт CI: отправить в `staging` коммит с намеренной ошибкой
  PHP CS Fixer и убедиться, что джоб деплоя не запустился, а на сервере
  осталась прежняя версия; после проверки откатить коммит

## 9. Проверки качества

- [x] 9.1 Выполнить в контейнере `bankruptcy-php` команды `make stan`,
  `make lint` и `make test`, исправить все ошибки; задача не считается
  выполненной, пока все три не проходят
