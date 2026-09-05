# frontend/CLAUDE.md

React SPA для системы ведения дел о банкротстве.
Общие правила проекта — в корневом `CLAUDE.md`, глоссарий — в `CONTEXT.md`.

**Комментарии в коде не пишем.** Никаких поясняющих `//` и `/* */`, никакого
закомментированного кода, никаких `TODO`. JSDoc не используем — типы описываются
в TypeScript. Подробности — в корневом `CLAUDE.md`.

---

## Стек

React 18.2, TypeScript 5.9 (strict), Vite 5.

UI: Tailwind CSS 3.4, shadcn/ui (стиль `new-york`, базовый цвет `neutral`,
CSS-переменные), Radix UI, lucide-react, `tailwindcss-animate`, `next-themes`.

Данные и состояние: TanStack Query 5, Recoil 0.7, react-router-dom 6,
react-hook-form 7.

Прочее: date-fns, react-day-picker, sonner (тосты), docx-preview
(предпросмотр документов), react-input-mask.

**ESLint и Prettier не настроены.** Стиль поддерживается вручную — ориентируйся
на соседние файлы: двойные кавычки, без точек с запятой, отступ 2 пробела.

---

## Команды

```bash
npm run dev      # vite, порт 5173
npm run build    # сборка в dist
npm run preview  # предпросмотр сборки
```

Из корня репозитория: `make sh-frontend` — shell в контейнере
`bankruptcy-frontend`.

Компилятор строгий: `strict`, `noUnusedLocals`, `noUnusedParameters`,
`noImplicitReturns`, `noFallthroughCasesInSwitch`. **Мёртвый код и неиспользуемые
переменные ломают сборку** — не оставляй их.

---

## Структура

```
src/
  components/
    ui/           примитивы shadcn/ui
    shared/       переиспользуемые компоненты (Loading, StatusBadge, SelectFields)
    Modals/       модальные окна и ModalProvider
    databases/    экраны восьми справочников
    ClientCard/   карточка дела, вкладки по стадиям
      General/    вкладка basic_info
      JudicialTab/ вкладки судебных стадий
    Dashboard.tsx, DocumentsPage.tsx, Layout.tsx, Login.tsx
  context/        AuthContext, AppContext, ThemeContext
  state/          атомы Recoil
  config/api.js   apiRequest
  types/          общие типы
  lib/utils.js    cn
```

Компоненты и папки-фичи — `PascalCase`, входная точка фичи — `index.tsx`.

---

## Маршруты

```
/login                      Login, всегда светлая тема
/contracts                  Dashboard, список дел
/contract/:id               ClientCard, карточка дела
/documents                  DocumentsPage, шаблоны и генерация
/databases/creditors        справочники
/databases/courts
/databases/bailiffs
/databases/financial-managers
/databases/fns
/databases/mchs
/databases/rosgvardia
/databases/gostekhnadzor
```

Всё, кроме `/login`, обёрнуто в `ProtectedRoute` и `Layout`.

---

## Импорты

Только через алиасы, они настроены и в `tsconfig.json`, и в `vite.config.ts`:

```
@/  @components/  @ui/  @shared/  @pages/  @api/  @hooks/  @store/  @utils/  @assets/
```

Относительные пути вида `../../..` не пишем.

---

## Работа с API

Все запросы идут через `apiRequest()` из `src/config/api.js`. **Голый `fetch`
не используем.** `apiRequest` сам:

- подставляет `Bearer`-токен из `localStorage`;
- разбирает ответ как `json`, `blob` или `text` по опции `responseType`;
- определяет `FormData` и не ставит `Content-Type` вручную;
- при 401 чистит токен и редиректит на `/login`.

```js
await apiRequest("/contracts")
await apiRequest("/contracts", { method: "POST", body: JSON.stringify(payload) })
await apiRequest("/document-templates/1/generate", { method: "POST", responseType: "blob" })
```

Базовый URL — `API_URL`, собирается из `VITE_API_URL`. В dev-режиме Vite
проксирует `/api` на `VITE_API_PROXY_TARGET`.

Асинхронные данные — через TanStack Query (`useQuery` / `useMutation`), а не
`useState` + `useEffect`. Клиент настроен в `main.tsx` с
`refetchOnWindowFocus: false` и `retry: false`. После мутации инвалидируй
соответствующие ключи.

---

## Состояние

Провайдеры оборачивают приложение в `main.tsx`:
`RecoilRoot` -> `QueryClientProvider` -> `ModalProvider` -> `App`,
внутри `App` — `AuthProvider` -> `AppProvider` -> `ThemeProvider`.

| Инструмент | Что хранит |
|---|---|
| Recoil, `referenceDataAtom` | Только глобальные справочники: суды, кредиторы, ФНС, приставы, МЧС, Росгвардия, Гостехнадзор, пользователи, финуправляющие. |
| `AuthContext` | Текущий пользователь, JWT-токен, `login`, `logout`, `hasRole`. |
| `AppContext` | Состояние приложения: дела, шаблоны, отчёты, справочники через Recoil. |
| `ThemeContext` | Тёмная и светлая темы (`next-themes`). Экран логина всегда светлый. |
| `ModalProvider` | Реестр модальных окон. |

Новое глобальное состояние по умолчанию — в TanStack Query, если это серверные
данные. Recoil — только для справочников.

---

## Модальные окна

Модалки открываются через реестр по ключу, а не локальным флагом `isOpen`:

```tsx
const { openModal } = useModalStore()
openModal("creditorForm", { creditorId: id })
```

Ключи: `confirm`, `createContract`, `creditorForm`, `bailiffForm`, `courtForm`,
`financialManagerForm`, `fnsForm`, `mchsForm`, `rosgvardiaForm`,
`previewDocumentModal`, `unsavedChanges`.

Новая модалка — это компонент в `components/Modals/`, запись в
`ModalComponentMap` и новый ключ в `ModalKey`. Компонент получает `isOpen`,
`onClose` и переданные пропсы.

---

## UI

Примитивы берём из `src/components/ui` (shadcn/ui). Новые примитивы добавляем
туда же и в том же стиле, иконки — только `lucide-react`. Классы объединяем
через `cn()` из `src/lib/utils.js`.

Тосты — `sonner`. Даты — `DatePickerInput` поверх `react-day-picker`, форматы
через `date-fns`.

Тексты интерфейса — на русском.

---

## Карточка дела

`ClientCard` разбит на вкладки, соответствующие стадиям `BankruptcyStage`
(см. `CONTEXT.md`). Активная вкладка хранится в search-параметрах URL.

| Вкладка | Стадия |
|---|---|
| `General/` | `basic_info` |
| `Pretrial.tsx` | `pre_court` |
| `JudicialTab/Introduction.tsx` | `judicial_procedure_initiation` |
| `JudicialTab/Procedure.tsx` | `judicial_procedure` |
| `JudicialTab/Report.tsx` | `judicial_report` |

API отдаёт данные дела **сгруппированными по стадиям**. Если бэкенд добавил поле
в `Contracts`, но не проставил `#[Groups]`, поле до фронтенда не дойдёт — это
первое, что стоит проверить, когда новое поле «не приходит».

Типы форм описаны в `ClientCard/types.ts` (`PrimaryInfoFields`,
`PretrialFields`, `IntroductionFields`, `ProcedureFields` и объединение
`FormSections`). Добавляя поле, обнови соответствующий интерфейс.

Есть механика несохранённых изменений: при уходе со страницы с правками
показывается модалка `unsavedChanges`.

---

## Справочники

Восемь экранов в `components/databases/` построены поверх `GenericDatabase` и
работают одинаково: список с поиском и пагинацией плюс модалка формы.
Добавляешь возможность в один справочник — проверь, нужна ли она остальным.

---

## Технический долг

- Нет ESLint и Prettier.
- Доменные типы описаны частично: `types/reference.ts` содержит только
  `ReferenceItem` и `ReferenceData`, тип `Contract` живёт в `AppContext.tsx` и
  имеет более 60 полей с индексной сигнатурой.
- `src/config/api.js` и `src/lib/utils.js` до сих пор на JavaScript, а не TypeScript.
- `AppContext` совмещает несколько зон ответственности и частично дублирует
  TanStack Query.
