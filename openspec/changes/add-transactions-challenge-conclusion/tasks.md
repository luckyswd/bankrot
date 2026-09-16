## 1. Backend: даты движения дела

- [x] 1.1 В `src/Entity/Contracts.php` добавить поля `courtApplicationSubmissionDate` (DATE, `#[Groups([BankruptcyStage::PRE_COURT->value])]`) и `caseInitiationDate` (DATE, `#[Groups([BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION->value])]`) с геттерами и сеттерами. Проверка: `make stan` зелёный, поля видны в ответе API после задачи 1.2.
- [x] 1.2 Сгенерировать миграцию Doctrine на две новые колонки и применить её (`make db-diff`, `make db-migrate`). Проверка: миграция добавляет ровно две колонки типа DATE и не трогает существующие; `make db-migrate` проходит на локальной базе.
- [x] 1.3 Добавить оба поля в список `dateFields` в `ContractorService::updateContractFields()`. Проверка: `PUT /api/v1/contracts/{id}` с новыми датами сохраняет их, значения возвращаются в группах `pre_court` и `judicial_procedure_initiation`.
- [x] 1.4 Добавить текстовые обёртки в `Contracts`: `getCourtApplicationSubmissionDateText()`, `getCaseInitiationDateText()` (обе через `DateHelperService::formatGenitive()`) и `getTransactionsAnalysisStartDateText()` — дата подачи заявления минус три года тем же форматом, пустая строка при незаполненной дате. Логику трёхлетнего периода вынести в `src/Service/Templates/JudicialReport/`. Проверка: юнит-тест на три случая — обычная дата, 29 февраля, пустая дата.

## 2. Frontend: поля на вкладках

- [x] 2.1 В `src/components/ClientCard/Pretrial.tsx` добавить поле даты «Дата подачи заявления в арбитражный суд» (`pre_court.courtApplicationSubmissionDate`) по образцу соседних дат. Проверка: `npx tsc --noEmit`, поле сохраняется и перечитывается.
- [x] 2.2 В `src/components/ClientCard/JudicialTab/Introduction.tsx` добавить поле даты «Дата возбуждения дела о банкротстве» (`judicial_procedure_initiation.caseInitiationDate`). Проверка: `npx tsc --noEmit`, поле сохраняется и перечитывается.

## 3. Шаблон

- [x] 3.1 Сконвертировать `~/Downloads/new_docs/4_Заключение_о_наличии_или_об_отсутствии_оснований_для_оспаривания.doc` в `.docx` и скриптом по `word/document.xml` заменить окрашенные прогоны на плейсхолдеры, сняв цвет: дата составления — `{{ ТЕКУЩАЯ_ДАТА('«дд» ММММР гггг') }}`, ФИО должника — `{{ fullName }}`, суд — `{{ court.name }}` вместо статического «Арбитражный суд » вместе с выделенным хвостом, номер дела — `{{ caseNumber }}`, даты — `{{ courtApplicationSubmissionDateText }}`, `{{ caseInitiationDateText }}`, `{{ procedureInitiationDateForPublication }}` (судебный акт, назначение управляющего и конец периода), страхование — `{{ financialManager.insuranceContractDescription }}`, сведения о должнике — `{{ birthDate }}`, `{{ birthPlace }}`, `{{ inn }}`, `{{ snils }}`, `{{ fullRegistrationAddress }}`, начало периода — `{{ transactionsAnalysisStartDateText }}`, подпись — `{{ fullNameGenitive }}`. Проверка: в шаблоне не осталось окрашенных прогонов и цветных тегов, XML валиден, каждый плейсхолдер лежит в одном прогоне.
- [x] 3.2 Положить файл как `backend/src/document-templates/judicial_report/4. Заключение о наличии или об отсутствии оснований для оспаривания сделок должника.docx` и выполнить `make templates-sync`. Проверка: «Записей создано: 1», повторный запуск — нули, запись с нужным названием и категорией `judicial_report`.

## 4. Тест шаблона

- [x] 4.1 Добавить `backend/tests/Service/Templates/TransactionsChallengeConclusionTemplateTest.php`: дело с судом, номером дела, данными должника, датами подачи, возбуждения и введения процедуры, управляющим с договором страхования. Проверка: тест утверждает, что в документе нет `{{` и `${`, XML валиден, окрашенных прогонов нет, подставлены ФИО, наименование суда одной строкой без задвоенного «Арбитражный суд», номер дела, все четыре даты и период «с «01» апреля 2022 г. по «05» мая 2025 г.».
- [x] 4.2 Проверить документ на деле без дат: незаполненные даты и пустой договор страхования не оставляют маркеров. Проверка: отдельный тест, `make test` зелёный.

## 5. Проверки и приёмка

- [x] 5.1 Прогнать в контейнере `bankruptcy-php` `make stan`, `make lint`, `make test`, во фронтенде — `npx tsc --noEmit` и `npm run build`; исправить все ошибки. Проверка: все команды завершаются без ошибок.
- [x] 5.2 Пройти сквозную проверку по `openspec/specs/documents/end-to-end-check`: заполнить через интерфейс моковыми данными новые поля («Дата подачи заявления в арбитражный суд» на «Досудебке», «Дата возбуждения дела о банкротстве» во «Введении процедуры»), сохранить дело без ошибок, открыть вкладку «Судебка → Отчёт», убедиться, что документ есть в списке со счётчиком заполненности, скачать его и сверить с карточкой дела и исходником заказчика. Проверка: в файле нет `{{` и `${`, значения и период совпадают с введёнными, вёрстка совпадает с исходником, ошибок в консоли и сети нет. Моковые данные не откатывать.
