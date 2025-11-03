// Моки пользователей
export const mockUsers = [
  {
    id: 1,
    username: 'admin',
    password: 'admin123',
    fullName: 'Иванов И.О.',
    role: 'admin'
  },
  {
    id: 2,
    username: 'manager1',
    password: 'pass123',
    fullName: 'Петров П.С.',
    role: 'manager'
  },
  {
    id: 3,
    username: 'manager2',
    password: 'pass123',
    fullName: 'Сидорова А.В.',
    role: 'manager'
  }
]

// Моки договоров
export const mockContracts = [
  {
    id: 1,
    contractNumber: '2024/001',
    clientData: {
      lastName: 'Смирнов',
      firstName: 'Алексей',
      middleName: 'Иванович',
      gender: 'male',
      birthDate: '1985-05-15',
      passportSeries: '4510',
      passportNumber: '123456',
      inn: '771234567890',
      snils: '123-456-789 00',
      address: 'г. Москва, ул. Ленина, д. 10, кв. 5',
      phone: '+7 (999) 123-45-67',
      email: 'smirnov@example.com',
      maritalStatus: 'married',
      spouseData: {
        lastName: 'Смирнова',
        firstName: 'Мария',
        middleName: 'Петровна',
        birthDate: '1987-03-20'
      },
      children: [
        { name: 'Смирнов Иван Алексеевич', birthDate: '2010-08-12', age: 14 }
      ]
    },
    contractDate: '2024-01-15',
    caseManager: 'Петров П.С.',
    createdBy: 'Иванов И.О.',
    status: 'active',
    stage: 'pre-trial',
    createdAt: '2024-01-15T10:00:00Z',
    updatedAt: '2024-01-20T15:30:00Z',
    preTrialData: {
      bankruptcyApplication: {
        creditorType: 'tax',
        creditorName: 'ФНС России',
        debtAmount: 500000,
        debtBasis: 'Налог',
        applicationDate: '2024-01-20'
      }
    },
    trialData: {
      courtName: 'Арбитражный суд г. Москвы',
      caseNumber: 'А40-12345/2024',
      judgeName: 'Сергеева О.П.',
      procedureIntroductionDate: '2024-02-01'
    },
    creditors: [
      {
        id: 1,
        name: 'ФНС России',
        inn: '7707329152',
        ogrn: '1047707030513',
        debtAmount: 500000,
        basis: [{ number: '123', date: '2023-12-01' }]
      },
      {
        id: 2,
        name: 'Банк ВТБ',
        inn: '7702070139',
        ogrn: '1027700186610',
        debtAmount: 1500000,
        basis: [
          { number: 'КД-001', date: '2020-05-15' },
          { number: 'КД-002', date: '2021-03-10' }
        ]
      }
    ],
    executionProceedings: [
      {
        number: '12345/23/77001-ИП',
        date: '2023-06-15',
        bailiffName: 'Иванов А.С.',
        department: 'УФССП по г. Москве',
        address: 'г. Москва, ул. Тверская, д. 13'
      }
    ],
    bankAccount: '40817810100001234567',
    specialAccount: '40817810200001234567',
    assets: {
      realEstate: [],
      movableProperty: [],
      cash: 0
    },
    reports: []
  },
  {
    id: 2,
    contractNumber: '2024/002',
    clientData: {
      lastName: 'Козлова',
      firstName: 'Елена',
      middleName: 'Сергеевна',
      gender: 'female',
      birthDate: '1990-09-25',
      passportSeries: '4512',
      passportNumber: '654321',
      inn: '771234567891',
      snils: '987-654-321 00',
      address: 'г. Москва, ул. Пушкина, д. 20, кв. 15',
      phone: '+7 (999) 765-43-21',
      email: 'kozlova@example.com',
      maritalStatus: 'single'
    },
    contractDate: '2024-01-20',
    caseManager: 'Сидорова А.В.',
    createdBy: 'Иванов И.О.',
    status: 'active',
    stage: 'trial',
    createdAt: '2024-01-20T14:00:00Z',
    updatedAt: '2024-01-25T11:15:00Z',
    preTrialData: {},
    trialData: {
      courtName: 'Арбитражный суд г. Москвы',
      caseNumber: 'А40-23456/2024',
      judgeName: 'Морозов В.И.'
    },
    creditors: [
      {
        id: 1,
        name: 'Сбербанк',
        inn: '7707083893',
        ogrn: '1027700132195',
        debtAmount: 2000000,
        basis: [{ number: 'КР-12345', date: '2019-11-20' }]
      }
    ],
    executionProceedings: [],
    assets: {
      realEstate: [],
      movableProperty: [],
      cash: 0
    }
  },
  {
    id: 3,
    contractNumber: '2024/003',
    clientData: {
      lastName: 'Волков',
      firstName: 'Дмитрий',
      middleName: 'Александрович',
      gender: 'male',
      birthDate: '1978-12-10',
      passportSeries: '4515',
      passportNumber: '789012',
      inn: '771234567892',
      snils: '456-789-012 34',
      address: 'г. Москва, пр-т Мира, д. 45, кв. 78',
      phone: '+7 (999) 111-22-33',
      email: 'volkov@example.com',
      maritalStatus: 'divorced',
      divorceDate: '2023-06-15'
    },
    contractDate: '2024-02-01',
    caseManager: 'Петров П.С.',
    createdBy: 'Иванов И.О.',
    status: 'completed',
    stage: 'completed',
    createdAt: '2024-02-01T09:00:00Z',
    updatedAt: '2024-10-15T16:45:00Z',
    preTrialData: {},
    trialData: {},
    creditors: [],
    executionProceedings: []
  }
]

// Моки баз данных
export const mockDatabases = {
  creditors: [
    {
      id: 1,
      name: 'ФНС России',
      inn: '7707329152',
      ogrn: '1047707030513',
      address: 'г. Москва, ул. Неглинная, д. 23',
      type: 'tax'
    },
    {
      id: 2,
      name: 'Банк ВТБ',
      inn: '7702070139',
      ogrn: '1027700186610',
      address: 'г. Москва, ул. Воронцовская, д. 43',
      type: 'bank'
    },
    {
      id: 3,
      name: 'Сбербанк',
      inn: '7707083893',
      ogrn: '1027700132195',
      address: 'г. Москва, ул. Вавилова, д. 19',
      type: 'bank'
    },
    {
      id: 4,
      name: 'Альфа-Банк',
      inn: '7728168971',
      ogrn: '1027700067328',
      address: 'г. Москва, ул. Каланчевская, д. 27',
      type: 'bank'
    }
  ],
  courts: [
    {
      id: 1,
      name: 'Арбитражный суд г. Москвы',
      address: 'г. Москва, ул. Большая Тульская, д. 17',
      phone: '+7 (495) 870-18-18'
    },
    {
      id: 2,
      name: 'Арбитражный суд Московской области',
      address: 'г. Москва, Проспект Мира, д. 150',
      phone: '+7 (495) 609-01-09'
    },
    {
      id: 3,
      name: 'Арбитражный суд г. Санкт-Петербурга и Ленинградской области',
      address: 'г. Санкт-Петербург, ул. Смольного, д. 6',
      phone: '+7 (812) 274-88-88'
    }
  ],
  bailiffs: [
    {
      id: 1,
      department: 'УФССП по г. Москве',
      address: 'г. Москва, ул. Тверская, д. 13',
      phone: '+7 (495) 987-65-43'
    },
    {
      id: 2,
      department: 'УФССП по Московской области',
      address: 'г. Москва, ул. Садовая-Спасская, д. 20',
      phone: '+7 (495) 123-45-67'
    }
  ],
  fns: [
    {
      id: 1,
      name: 'ИФНС № 1 по г. Москве',
      address: 'г. Москва, ул. Неглинная, д. 23, корп. 1',
      code: '7701'
    },
    {
      id: 2,
      name: 'ИФНС № 2 по г. Москве',
      address: 'г. Москва, ул. Садовая-Черногрязская, д. 8',
      code: '7702'
    }
  ],
  mchs: [
    {
      id: 1,
      name: 'ГИМС МЧС России по г. Москве',
      address: 'г. Москва, Симоновский вал, д. 12А',
      phone: '+7 (495) 111-22-33'
    }
  ],
  rosgvardia: [
    {
      id: 1,
      name: 'Управление Росгвардии по г. Москве',
      address: 'г. Москва, ул. Народного Ополчения, д. 43',
      phone: '+7 (495) 444-55-66'
    }
  ]
}

// Шаблоны документов
export const documentTemplates = {
  bankruptcyApplication: {
    name: 'Заявление о признании банкротом',
    section: 'pre-trial',
    template: (data) => `
ЗАЯВЛЕНИЕ О ПРИЗНАНИИ БАНКРОТОМ

Дата: ${data.applicationDate || '[Дата]'}

В Арбитражный суд ${data.trialData?.courtName || '[Название суда]'}
Заявитель: ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName}
Адрес: ${data.clientData?.address}
ИНН: ${data.clientData?.inn}

Основание обязательства: ${data.preTrialData?.bankruptcyApplication?.debtBasis || '[Основание]'}
Кредитор: ${data.preTrialData?.bankruptcyApplication?.creditorName || '[Кредитор]'}
Сумма задолженности: ${data.preTrialData?.bankruptcyApplication?.debtAmount || 0} руб.

Прошу признать меня несостоятельным (банкротом) и ввести процедуру реализации имущества.

Подпись: ________________
Дата: ${data.applicationDate || '[Дата]'}
    `
  },
  efrsbPublication: {
    name: 'Публикация ЕФРСБ',
    section: 'realization-introduction',
    template: (data) => `
ПУБЛИКАЦИЯ В ЕФРСБ

Арбитражный суд: ${data.trialData?.courtName || '[Суд]'}
Номер дела: ${data.trialData?.caseNumber || '[Номер дела]'}
Судья: ${data.trialData?.judgeName || '[ФИО судьи]'}

Должник: ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName}
Дата рождения: ${data.clientData?.birthDate || '[Дата рождения]'}
Адрес: ${data.clientData?.address || '[Адрес]'}
ИНН: ${data.clientData?.inn || '[ИНН]'}
СНИЛС: ${data.clientData?.snils || '[СНИЛС]'}

${data.trialData?.resolutionDate ? `Дата оглашения резолютивной части: ${data.trialData.resolutionDate}` : ''}
${data.trialData?.fullDecisionDate ? `Дата вынесения судебного акта в полном объеме: ${data.trialData.fullDecisionDate}` : ''}

${data.trialData?.courtHearing ? `
Судебное заседание:
Дата: ${data.trialData.courtHearing.date}
Время: ${data.trialData.courtHearing.time}
Зал: ${data.trialData.courtHearing.room}
` : ''}

Публикация подана: ${new Date().toLocaleDateString('ru-RU')}
    `
  },
  spouseNotification: {
    name: 'Уведомление о введении РИ супруга',
    section: 'realization-introduction',
    template: (data) => `
УВЕДОМЛЕНИЕ О ВВЕДЕНИИ ПРОЦЕДУРЫ РЕАЛИЗАЦИИ ИМУЩЕСТВА

${data.clientData?.spouseData ? `
Уважаем${data.clientData?.spouseData?.gender === 'female' ? 'ая' : 'ый'} ${data.clientData?.spouseData?.lastName} ${data.clientData?.spouseData?.firstName} ${data.clientData?.spouseData?.middleName}!

Настоящим уведомляем Вас о том, что в отношении Вашего супруга${data.clientData?.gender === 'female' ? 'и' : 'а'} ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName} введена процедура реализации имущества гражданина.

Решением ${data.trialData?.courtName || '[Суд]'} от ${data.trialData?.procedureIntroductionDate || '[Дата]'} по делу № ${data.trialData?.caseNumber || '[Номер дела]'}.

Финансовый управляющий: [ФИО управляющего]
Контактный телефон: [Телефон]

Дата уведомления: ${new Date().toLocaleDateString('ru-RU')}
` : 'Данный документ формируется только при наличии супруга'}
    `
  },
  gibddRequest: {
    name: 'Запрос ГИБДД, ГИМС, РОС и т.д.',
    section: 'realization-introduction',
    template: (data) => `
ЗАПРОС В ГИБДД/ГИМС/РОСТЕХНАДЗОР

Дата: ${new Date().toLocaleDateString('ru-RU')}
Исх. № ${Math.floor(Math.random() * 10000)}

${data.requestTo === 'gibdd' ? 'В ГИБДД МВД России' : ''}
${data.requestTo === 'gims' ? `В ${data.selectedGims?.name || '[ГИМС]'}` : ''}
${data.requestTo === 'rostehnadzor' ? 'В Ростехнадзор России' : ''}

Прошу предоставить информацию о наличии зарегистрированного имущества на имя:

ФИО: ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName}
Дата рождения: ${data.clientData?.birthDate || '[Дата]'}
Паспорт: ${data.clientData?.passportSeries} ${data.clientData?.passportNumber}

${data.clientData?.spouseData ? `
А также на имя супруг${data.clientData?.gender === 'male' ? 'и' : 'а'}:
ФИО: ${data.clientData.spouseData.lastName} ${data.clientData.spouseData.firstName} ${data.clientData.spouseData.middleName}
Дата рождения: ${data.clientData.spouseData.birthDate}
` : ''}

Основание: Решение ${data.trialData?.courtName || '[Суд]'} от ${data.trialData?.procedureIntroductionDate || '[Дата]'} по делу № ${data.trialData?.caseNumber || '[Номер]'}

Финансовый управляющий: [ФИО]
Подпись: ________________
    `
  },
  creditorRequirement: {
    name: 'Публикация о получении требования кредитора',
    section: 'procedure',
    template: (data) => `
ПУБЛИКАЦИЯ О ПОЛУЧЕНИИ ТРЕБОВАНИЯ КРЕДИТОРА

Дело № ${data.trialData?.caseNumber || '[Номер дела]'}
Должник: ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName}

${data.creditors && data.creditors.length > 0 ? data.creditors.map(creditor => `
Кредитор: ${creditor.name}
ОГРН: ${creditor.ogrn || '[ОГРН]'}
ИНН: ${creditor.inn || '[ИНН]'}
Сумма требования: ${creditor.debtAmount || 0} руб.

Основания требования:
${creditor.basis && creditor.basis.length > 0 ? creditor.basis.map(b => `- № ${b.number} от ${b.date}`).join('\n') : '[Основания]'}
`).join('\n---\n') : 'Требования кредиторов отсутствуют'}

Дата получения требования: ${new Date().toLocaleDateString('ru-RU')}
    `
  },
  financialReport: {
    name: 'Отчет финансового управляющего',
    section: 'report',
    template: (data) => `
ОТЧЕТ ФИНАНСОВОГО УПРАВЛЯЮЩЕГО
О результатах проведения реализации имущества гражданина

Дата составления: ${data.reportDate || new Date().toLocaleDateString('ru-RU')}

Дело № ${data.trialData?.caseNumber || '[Номер дела]'}
Должник: ${data.clientData?.lastName} ${data.clientData?.firstName} ${data.clientData?.middleName}
Дата введения процедуры: ${data.trialData?.procedureIntroductionDate || '[Дата]'}

${data.insuranceContractDate ? `Дата страхового договора: ${data.insuranceContractDate}` : 'Страховой договор: не заключен'}

${data.procedureExtensions && data.procedureExtensions.length > 0 ? `
Процедура реализации продлевалась:
${data.procedureExtensions.map(ext => `- до ${ext.date}`).join('\n')}
` : 'Процедура реализации не продлевалась'}

Отчетный период: с ${data.trialData?.procedureIntroductionDate || '[Дата начала]'} по ${data.reportDate || '[Дата окончания]'}

ИНФОРМАЦИЯ О БАНКОВСКИХ СЧЕТАХ:
Счет заблокирован

ИМУЩЕСТВО ДОЛЖНИКА:
${data.assets?.realEstate?.length > 0 ? `Недвижимое имущество: ${data.assets.realEstate.length} объект(ов)` : 'Недвижимое имущество: отсутствует'}
${data.assets?.movableProperty?.length > 0 ? `Движимое имущество: ${data.assets.movableProperty.length} объект(ов)` : 'Движимое имущество: отсутствует'}
Денежные средства: ${data.assets?.cash || 0} руб.

${data.assets?.totalValue > 0 ? 'Опись имущества произведена' : 'Опись имущества не производилась'}
${data.appraisalDate ? `Оценка имущества проведена: ${data.appraisalDate}` : 'Оценка имущества в ходе процедуры не проводилась'}

СЕМЕЙНОЕ ПОЛОЖЕНИЕ:
${data.clientData?.maritalStatus === 'married' ? `Должник состоит в браке с ${data.clientData?.spouseData?.lastName} ${data.clientData?.spouseData?.firstName} ${data.clientData?.spouseData?.middleName}` : ''}
${data.clientData?.maritalStatus === 'divorced' && data.clientData?.divorceDate ? `Должник был в браке, развод менее 3 лет назад (${data.clientData.divorceDate})` : ''}
${data.clientData?.maritalStatus === 'single' ? 'Должник не состоял в браке' : ''}

НЕСОВЕРШЕННОЛЕТНИЕ ДЕТИ:
${data.clientData?.children && data.clientData.children.length > 0 ? 
  data.clientData.children.map(child => `${child.name}, ${child.birthDate} (${child.age} лет)`).join('\n') : 
  'Несовершеннолетние дети отсутствуют'}

РЕЕСТР ТРЕБОВАНИЙ КРЕДИТОРОВ:
${data.creditors && data.creditors.length > 0 ? data.creditors.map((creditor, index) => `
${index + 1}. ${creditor.name}
   ИНН: ${creditor.inn}
   Сумма требования: ${creditor.debtAmount} руб.
`).join('\n') : 'Кредиторы отсутствуют'}

${data.creditors && data.creditors.length > 0 ? `
Всего требований: ${data.creditors.reduce((sum, c) => sum + (c.debtAmount || 0), 0)} руб.
` : ''}

${data.bankruptcyEstate ? `
КОНКУРСНАЯ МАССА:
Общая стоимость: ${data.bankruptcyEstate} руб.

РАСЧЕТЫ С КРЕДИТОРАМИ:
${data.creditors && data.creditors.length > 0 ? 
  data.creditors.map(c => {
    const share = data.bankruptcyEstate / data.creditors.reduce((sum, cr) => sum + (cr.debtAmount || 0), 0);
    const payment = Math.round(c.debtAmount * share);
    return `${c.name}: ${payment} руб.`;
  }).join('\n') : ''}
` : ''}

Финансовый управляющий: ________________

Дата: ${data.reportDate || new Date().toLocaleDateString('ru-RU')}
    `
  }
}


