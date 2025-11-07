import { createContext, useContext, useEffect, useState, type ReactNode } from "react"

import { mockContracts, mockDatabases, mockUsers } from "../data/mockData"

type Contract = (typeof mockContracts)[number]
type DatabaseState = typeof mockDatabases
type User = (typeof mockUsers)[number] | null

interface Template {
  id: number
  name: string
  description: string
  size: number
  category: string
  variables: string[]
  uploadedAt: string
  uploadedBy: string
  data?: string
}

interface AppContextValue {
  currentUser: User
  contracts: Contract[]
  databases: DatabaseState
  templates: Template[]
  actionLog: any[]
  login: (username: string, password: string) => boolean
  logout: () => void
  updateContract: (contractId: number, updates: Partial<Contract>) => void
  createContract: (contractData: Partial<Contract>) => Contract
  addToDatabase: (dbName: keyof DatabaseState, item: Record<string, unknown>) => void
  logAction: (action: string, description: string) => void
  addTemplate: (template: Template) => void
  deleteTemplate: (templateId: number) => void
}

const AppContext = createContext<AppContextValue | null>(null)

export const useApp = () => {
  const context = useContext(AppContext)
  if (!context) {
    throw new Error('useApp must be used within AppProvider')
  }
  return context
}

// Моковые ШАБЛОНЫ документов
const mockTemplates: Template[] = [
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

interface AppProviderProps {
  children: ReactNode
}

export const AppProvider = ({ children }: AppProviderProps) => {
  const [currentUser, setCurrentUser] = useState<User>(null)
  const [contracts, setContracts] = useState<Contract[]>(mockContracts)
  const [databases, setDatabases] = useState<DatabaseState>(mockDatabases)
  const [templates, setTemplates] = useState<Template[]>(mockTemplates)
  const [actionLog, setActionLog] = useState<any[]>([])

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

  const login = (username: string, password: string) => {
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
    if (currentUser) {
      logAction('logout', `Выход пользователя ${currentUser.fullName}`)
    }
    setCurrentUser(null)
  }

  const logAction = (action: string, description: string) => {
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

  const updateContract = (contractId: number, updates: Partial<Contract>) => {
    setContracts(prev =>
      prev.map(contract =>
        contract.id === contractId
          ? { ...contract, ...updates, updatedAt: new Date().toISOString() }
          : contract
      )
    )
    logAction('update_contract', `Обновление договора №${contractId}`)
  }

  const createContract = (contractData: Partial<Contract>) => {
    const newContract = {
      id: Date.now(),
      ...contractData,
      createdBy: currentUser?.fullName ?? 'Система',
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      status: 'active'
    }
    setContracts(prev => [newContract, ...prev])
    logAction('create_contract', `Создание нового договора №${newContract.contractNumber}`)
    return newContract
  }

  const addToDatabase = (dbName: keyof DatabaseState, item: Record<string, unknown>) => {
    setDatabases(prev => ({
      ...prev,
      [dbName]: [...(prev[dbName] || []), { ...item, id: Date.now() }]
    }))
    logAction('add_to_database', `Добавление в базу ${dbName}`)
  }

  // Templates functions
  const addTemplate = (template: Template) => {
    setTemplates(prev => [template, ...prev])
    logAction('add_template', `Загружен шаблон ${template.name}`)
  }

  const deleteTemplate = (templateId: number) => {
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
