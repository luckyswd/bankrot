import { createContext, useContext, useState, useEffect } from 'react'
import { mockUsers, mockContracts, mockDatabases } from '../data/mockData'

const AppContext = createContext()

export const useApp = () => {
  const context = useContext(AppContext)
  if (!context) {
    throw new Error('useApp must be used within AppProvider')
  }
  return context
}

// Моковые ШАБЛОНЫ документов
const mockTemplates = [
  {
    id: 1,
    name: 'Шаблон - Заявление о признании банкротом.docx',
    description: 'Основное заявление в суд',
    size: 28400,
    category: 'Досудебка',
    variables: ['{ФИО}', '{ДАТА_РОЖДЕНИЯ}', '{АДРЕС}', '{ИНН}', '{СУММА_ДОЛГА}'],
    uploadedAt: '2024-10-20T09:00:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 2,
    name: 'Шаблон - Публикация ЕФРСБ.docx',
    description: 'Публикация в ЕФРСБ',
    size: 22100,
    category: 'Введение процедуры',
    variables: ['{ФИО}', '{НОМЕР_ДЕЛА}', '{ДАТА_РЕШЕНИЯ}', '{СУД}', '{СУДЬЯ}'],
    uploadedAt: '2024-10-20T09:15:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 3,
    name: 'Шаблон - Уведомление супруга о РИ.docx',
    description: 'Уведомление супругу о введении реализации имущества',
    size: 19800,
    category: 'Введение процедуры',
    variables: ['{ФИО_СУПРУГА}', '{ФИО_ДОЛЖНИКА}', '{ДАТА_РЕШЕНИЯ}', '{НОМЕР_ДЕЛА}'],
    uploadedAt: '2024-10-20T09:30:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 4,
    name: 'Шаблон - Запрос в ГИБДД.docx',
    description: 'Запрос информации о транспортных средствах',
    size: 18200,
    category: 'Введение процедуры',
    variables: ['{ФИО}', '{ПАСПОРТ}', '{ДАТА_РОЖДЕНИЯ}'],
    uploadedAt: '2024-10-20T10:00:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 5,
    name: 'Шаблон - Отчет финансового управляющего.docx',
    description: 'Отчет о результатах реализации имущества',
    size: 45600,
    category: 'Отчёты',
    variables: ['{ФИО}', '{НОМЕР_ДЕЛА}', '{ДАТА_ПРОЦЕДУРЫ}', '{КРЕДИТОРЫ}', '{КОНКУРСНАЯ_МАССА}'],
    uploadedAt: '2024-10-20T10:30:00',
    uploadedBy: 'Администратор'
  }
]

export const AppProvider = ({ children }) => {
  const [currentUser, setCurrentUser] = useState(null)
  const [contracts, setContracts] = useState(mockContracts)
  const [databases, setDatabases] = useState(mockDatabases)
  const [templates, setTemplates] = useState(mockTemplates)
  const [actionLog, setActionLog] = useState([])

  // Автосохранение в localStorage
  useEffect(() => {
    const savedContracts = localStorage.getItem('contracts')
    const savedTemplates = localStorage.getItem('templates')
    if (savedContracts) {
      setContracts(JSON.parse(savedContracts))
    }
    if (savedTemplates) {
      setTemplates(JSON.parse(savedTemplates))
    }
  }, [])

  useEffect(() => {
    if (contracts.length > 0) {
      localStorage.setItem('contracts', JSON.stringify(contracts))
    }
  }, [contracts])

  useEffect(() => {
    if (templates.length > 0) {
      localStorage.setItem('templates', JSON.stringify(templates))
    }
  }, [templates])

  const login = (username, password) => {
    const user = mockUsers.find(
      u => u.username === username && u.password === password
    )
    if (user) {
      setCurrentUser(user)
      logAction('login', `Вход пользователя ${user.fullName}`)
      return true
    }
    return false
  }

  const logout = () => {
    logAction('logout', `Выход пользователя ${currentUser.fullName}`)
    setCurrentUser(null)
  }

  const logAction = (action, description) => {
    const log = {
      id: Date.now(),
      userId: currentUser?.id,
      userName: currentUser?.fullName,
      action,
      description,
      timestamp: new Date().toISOString()
    }
    setActionLog(prev => [log, ...prev])
  }

  const updateContract = (contractId, updates) => {
    setContracts(prev =>
      prev.map(contract =>
        contract.id === contractId
          ? { ...contract, ...updates, updatedAt: new Date().toISOString() }
          : contract
      )
    )
    logAction('update_contract', `Обновление договора №${contractId}`)
  }

  const createContract = (contractData) => {
    const newContract = {
      id: Date.now(),
      ...contractData,
      createdBy: currentUser.fullName,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      status: 'active'
    }
    setContracts(prev => [newContract, ...prev])
    logAction('create_contract', `Создание нового договора №${newContract.contractNumber}`)
    return newContract
  }

  const addToDatabase = (dbName, item) => {
    setDatabases(prev => ({
      ...prev,
      [dbName]: [...(prev[dbName] || []), { ...item, id: Date.now() }]
    }))
    logAction('add_to_database', `Добавление в базу ${dbName}`)
  }

  // Templates functions
  const addTemplate = (template) => {
    setTemplates(prev => [template, ...prev])
    logAction('add_template', `Загружен шаблон ${template.name}`)
  }

  const deleteTemplate = (templateId) => {
    setTemplates(prev => prev.filter(t => t.id !== templateId))
    logAction('delete_template', `Удалён шаблон`)
  }

  const value = {
    currentUser,
    contracts,
    databases,
    templates,
    actionLog,
    login,
    logout,
    updateContract,
    createContract,
    addToDatabase,
    logAction,
    addTemplate,
    deleteTemplate
  }

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>
}
