# backend/CLAUDE.md

Symfony API для системы ведения дел о банкротстве.
Общие правила проекта — в корневом `CLAUDE.md`, глоссарий — в `CONTEXT.md`.

**Комментарии в коде не пишем.** Исключение — PHPDoc с типовой информацией,
невыразимой в сигнатуре (`@return array<int, string>`,
`@return Collection<int, Contracts>`, `@param array{id: int, name: string}`).
Подробности — в корневом `CLAUDE.md`.

---

## Стек

PHP 8.4, Symfony 6.4, Doctrine ORM 3.5, MySQL 8.

Бандлы: LexikJWTAuthenticationBundle 3.1, NelmioApiDocBundle 4,
NelmioCorsBundle 2.6, DoctrineMigrationsBundle, DoctrineFixturesBundle,
MakerBundle.

Работа с документами: PhpWord 1.4 (`.docx`), PhpSpreadsheet (`.xlsx`).

Инструменты: PHPStan 2.1 (level 5, с расширениями для Symfony и Doctrine),
PHP CS Fixer 3.89 (`@Symfony` + `@PSR12`), PHPUnit 12, Paratest.

---

## Команды

Выполняются внутри контейнера `bankruptcy-php` (WORKDIR `/var/www/html`).
Из корня репозитория те же цели доступны через проксирующий `Makefile`.

```bash
make                 # список целей
make check-code      # stan + lint
make stan            # PHPStan level 5
make lint            # PHP CS Fixer, только проверка
make lint-fix        # PHP CS Fixer, применить исправления
make test            # пересоздать SQLite-базу, загрузить фикстуры, PHPUnit

make db-migrate      # применить миграции
make db-diff         # сгенерировать миграцию по изменениям сущностей
make seed            # фикстуры группы seed
make cc              # очистить кэш Symfony и Doctrine
make jwt-gen         # сгенерировать пару JWT-ключей
```

`make composer-install` — установка **без** dev-зависимостей, предназначена для
деплоя на сервер. Для локальной работы используй `make install`.

**Перед завершением любой задачи, затронувшей `backend/`, прогони
`make check-code` и `make test` и исправь всё, что упало.** То же гоняет CI.

---

## Структура

```
src/
  Controller/       тонкие контроллеры, маршруты через атрибуты
  Entity/           сущности Doctrine
    Enum/           BankruptcyStage, ContractStatus
  Repository/       запросы к БД
  Service/          бизнес-логика
    Templates/      генерация документов из шаблонов
  Command/          консольные команды
  DataFixtures/     фикстуры, группа seed
  document-templates/  загруженные файлы шаблонов
config/packages/    конфигурация бандлов
migrations/         миграции Doctrine
tests/              функциональные тесты
```

---

## Правила кода

- `declare(strict_types=1);` в каждом файле.
- PSR-12 и правила `.php-cs-fixer.dist.php`: `@Symfony` + `@PSR12`,
  `yoda_style: false`, отступ 4 пробела, LF.
- Пустая строка перед `return`.
- Именованные аргументы при вызове функций и методов.
- Типы у всех аргументов, свойств и возвращаемых значений.
- Константы классов вместо магических строк и чисел.
- Для коллекций — типизированные свойства (`array<int, string>`,
  `Collection<int, X>`) или DTO.
- Зависимости — только автовнедрение через конструктор. Обращение к контейнеру
  (`$this->container->get()`) запрещено.
- Контроллеры тонкие: бизнес-логика живёт в `src/Service`.
- Сервисы без состояния объявляй `readonly class`.
- Исключения осмысленные и типизированные.
- Принципы SOLID, разделение доменной, прикладной и инфраструктурной логики.
- PHPStan level 5 должен оставаться зелёным.

---

## Сущности

`Contracts` — центральная сущность, дело о банкротстве целиком. Все данные
должника, паспорт, семья, работа, суд, кредиторы и реквизиты процедуры лежат в
ней.

Связи `Contracts`:

- `ManyToOne` — `Court`, `FinancialManager`, `Fns`, `Mchs`, `Rosgvardia`,
  `Gostekhnadzor`, `Bailiff`, `User` (автор);
- `OneToMany` с `cascade: ['persist', 'remove']` и `orphanRemoval: true` —
  `ContractsPreCourtCreditor` (кредиторы досудебной стадии) и
  `ContractsCreditorsClaim` (требования кредиторов в реестре).

`BaseEntity` — mapped superclass с `createdAt` / `updatedAt` и
`#[ORM\PreUpdate]`. Наследуй от него новые сущности; `DocumentTemplate` — это
исключение, у него нет временных меток.

### Обязательное правило при добавлении поля в Contracts

Проставь `#[Groups([BankruptcyStage::<СТАДИЯ>->value])]`. Без атрибута поле не
попадёт в ответ API, потому что `ContractorService::serializeContractByStages()`
сериализует по группам. Затем добавь поле в соответствующую вкладку `ClientCard`
на фронтенде и сгенерируй миграцию (`make db-diff`).

---

## Слой сервисов

| Сервис | Ответственность |
|---|---|
| `ContractorService` | Сериализация дела по стадиям, обновление полей и коллекций (кредиторы, требования). |
| `CacheService` | Кэширование справочников, отдельный пул на каждый справочник, TTL 1 час, ключи по md5 поисковой строки. Инвалидация при изменении справочника обязательна. |
| `Serializer` | Обёртка над Symfony Serializer с `DateTimeNormalizer`, `BackedEnumNormalizer` и обработкой циклических ссылок через `getId()`. |
| `DateHelperService` | Русские названия месяцев, в том числе в родительном падеже. |

---

## Генерация документов

`DocumentTemplate` хранит путь к загруженному `.docx` / `.xlsx` и
категорию-стадию. Обработчик — `Service\Templates\DocumentTemplateProcessor`.

| Конструкция | Пример | Кто обрабатывает |
|---|---|---|
| Путь к данным | `{{ court.name }}` | `EntityDataResolver` через `PropertyAccessor`, точечная нотация от `Contracts`. Отсутствующее значение — пустая строка. |
| Функция | `{{ ТЕКУЩАЯ_ДАТА('дд.ММ.гггг') }}` | `CustomFunction`. Имена заглавными кириллицей. Маски: `дд`, `ММ`, `ММММ`, `гггг`, `гг`. |
| Блок `FOR` | списки кредиторов и требований | `DocumentTemplateProcessor::handeFORVariables()` |

Сложные формулировки, зависящие от пола, семейного положения и склонений,
собираются статическими методами `Templates\PreCourt\PreCourtMethods` и
`Templates\ProcedureInitiation\ProcedureInitiationMethods`. Новая формулировка
для документа — это новый метод там, а не логика в контроллере.

---

## Контроллеры и API

Префикс `/api/v1`, маршруты объявляются атрибутами `#[Route]`,
`config/routes.yaml` подхватывает `src/Controller/` целиком.

Текущие конвенции (следуй им ради единообразия):

- ответы собираются вручную массивами и отдаются через `$this->json()`;
- списки справочников возвращают `items`, `total`, `page`, `limit`, `pages`;
- список дел возвращает `data`, `pagination`, `counts`;
- коды: 201 на создание, 204 на удаление, 400 на некорректный ввод,
  401 без авторизации, 404 если сущность не найдена;
- у справочников проставлены атрибуты OpenAPI (`#[OA\Get]`, `#[OA\Post]` и т. д.) —
  новые эндпоинты справочников документируй так же.

Восемь справочников имеют идентичный набор действий: список с пагинацией и
поиском, show, create, update, delete. Правишь один — проверь остальные.

---

## Авторизация

JWT (Lexik), firewall `api` stateless на `^/api`. Иерархия ролей:

```
ROLE_ADMIN -> ROLE_FINANCIAL_MANAGER -> ROLE_MANAGER -> ROLE_USER
```

Единственный реально существующий публичный маршрут — `POST /api/v1/login`, плюс
любые `OPTIONS`. Остальное — `IS_AUTHENTICATED_FULLY`. `/api/doc` требует
`ROLE_ADMIN` и HTTP Basic.

В `security.yaml` есть правила `PUBLIC_ACCESS` для `/api/v1/test` и
`/api/v1/health`, но **контроллеров для них нет** — это мёртвые правила.
Не полагайся на них и не считай, что health-check существует.

Ключи JWT (`config/jwt/*.pem`) не в git, генерируются `make jwt-gen`.

---

## Тесты

PHPUnit 12, отдельная база SQLite `var/test.db`, пересоздаётся целью
`test-prepare`. `phpunit.dist.xml` форсирует `APP_ENV=test` и `DATABASE_URL`.

`App\Tests\BaseTestCase` (наследник `WebTestCase`) даёт изоляцию через
транзакцию и хелпер `getAuthToken()`. Фикстуры пользователей: `user1`, `user2`,
`manager`, `admin`.

Тестов пока мало — контроллеры дел и шаблонов документов. Новую логику
контроллеров и сервисов покрывай функциональным тестом по образцу
`tests/Controller/ContractsControllerTest.php`.

### Ловушка: устаревший контейнер тестового окружения

`tests/bootstrap.php` выставляет `APP_DEBUG=0`, поэтому тесты используют
non-debug контейнер. В этом режиме Symfony **не проверяет свежесть скомпилированного
контейнера**, и `var/cache/test` может месяцами оставаться устаревшим. Симптом —
массовые 500 с сообщением вида

```
Controller "App\Controller\ContractsController::list" requires that you provide
a value for the "$contractsRepository" argument.
```

`php bin/console cache:clear` это **не чинит**: он пересобирает только debug-вариант.
Лечится удалением `var/cache/test`, что теперь делает цель `test-prepare`.

---

## Технический долг

Знай о нём, но не переделывай без отдельного предложения:

- `Entity/Contracts.php` — god-object на ~1500 строк, поля всех пяти стадий в
  одной таблице.
- `ContractorService::updateContractFields()` обновляет поля через Reflection и
  списки имён полей вместо типизированных DTO.
- Валидация написана руками в контроллерах, Symfony Validator и Request DTO не
  используются.
- Атрибуты OpenAPI есть у справочников, но отсутствуют в `ContractsController`.
- Единого формата ошибок нет, ответы собираются вручную.
- `ContractsCreditorsClaim::$inclusion` по описанию OpenAPI означает «требование
  получено», а не «включено в реестр» — имя поля вводит в заблуждение.
