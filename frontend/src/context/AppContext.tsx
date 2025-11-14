import { createContext, useContext, useEffect, useState, type ReactNode } from "react"

export interface ReferenceItem {
  id: number | string
  name: string
}

export interface ReferenceData {
  courts?: ReferenceItem[]
  creditors?: ReferenceItem[]
  fns?: ReferenceItem[]
  bailiffs?: ReferenceItem[]
  [key: string]: ReferenceItem[] | undefined
}

interface User {
  id: number
  username: string
  password: string
  fullName: string
  role: string
}

interface ClientData {
  lastName?: string
  firstName?: string
  middleName?: string
  gender?: string
  birthDate?: string
  passportSeries?: string
  passportNumber?: string
  inn?: string
  snils?: string
  address?: string
  phone?: string
  email?: string
  maritalStatus?: string
  spouseData?: Record<string, unknown>
  children?: Record<string, unknown>[]
  divorceDate?: string
}

export interface Contract {
  id: number
  contractNumber: string
  clientData: ClientData
  contractDate?: string
  caseManager?: string
  createdBy?: string
  status: "active" | "completed" | string
  stage?: string
  createdAt?: string
  updatedAt?: string
  primaryInfo?: Record<string, unknown>
  pretrial?: Record<string, unknown>
  introduction?: Record<string, unknown>
  procedure?: Record<string, unknown>
  preTrialData?: Record<string, unknown>
  trialData?: Record<string, unknown>
  creditors?: Record<string, unknown>[]
  executionProceedings?: Record<string, unknown>[]
  bankAccount?: string
  specialAccount?: string
  assets?: Record<string, unknown>
  reports?: Record<string, unknown>[]
  [key: string]: unknown
}

export interface Report {
  id: number
  name: string
  size: number
  type: string
  uploadedAt: string
  uploadedBy: string
  data: string | ArrayBuffer
}

interface Template {
  id: number
  name: string
  description: string
  size: number
  category: string
  variables: string[]
  uploadedAt: string
  uploadedBy: string
  data?: string | ArrayBuffer
}

interface AppContextValue {
  currentUser: User | null
  contracts: Contract[]
  referenceData: ReferenceData
  templates: Template[]
  actionLog: any[]
  reports: Report[]
  login: (username: string, password: string) => boolean
  logout: () => void
  updateContract: (contractId: number, updates: Partial<Contract>) => void
  createContract: (contractData: Partial<Contract>) => Contract
  addToReferenceData: (dbName: keyof ReferenceData, item: ReferenceItem) => void
  logAction: (action: string, description: string) => void
  addTemplate: (template: Template) => void
  deleteTemplate: (templateId: number) => void
  addReport: (report: Report) => void
  deleteReport: (reportId: number) => void
}

const AppContext = createContext<AppContextValue | null>(null)

export const useApp = () => {
  const context = useContext(AppContext)
  if (!context) {
    throw new Error('useApp must be used within AppProvider')
  }
  return context
}

interface AppProviderProps {
  children: ReactNode
}

const defaultClientData: ClientData = {
  lastName: "",
  firstName: "",
  middleName: "",
  gender: "",
  maritalStatus: "",
}

export const AppProvider = ({ children }: AppProviderProps) => {
  const [currentUser, setCurrentUser] = useState<User | null>(null)
  const [contracts, setContracts] = useState<Contract[]>([])
  const [referenceData, setReferenceData] = useState<ReferenceData>({})
  const [templates, setTemplates] = useState<Template[]>([])
  const [actionLog, setActionLog] = useState<any[]>([])
  const [reports, setReports] = useState<Report[]>([])

  // Автосохранение в localStorage
  useEffect(() => {
    const savedContracts = localStorage.getItem('contracts')
    const savedTemplates = localStorage.getItem('templates')
    const savedReports = localStorage.getItem('reports')
    if (savedContracts) {
      setContracts(JSON.parse(savedContracts) as Contract[])
    }
    if (savedTemplates) {
      setTemplates(JSON.parse(savedTemplates) as Template[])
    }
    if (savedReports) {
      setReports(JSON.parse(savedReports) as Report[])
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

  useEffect(() => {
    localStorage.setItem('reports', JSON.stringify(reports))
  }, [reports])

  const login = (_username: string, _password: string): boolean => {
    // TODO: Реализовать реальную авторизацию через API
    // Пока возвращаем false, так как моковые данные убраны
    return false
  }

  const logout = (): void => {
    if (currentUser) {
      logAction('logout', `Выход пользователя ${currentUser.fullName}`)
    }
    setCurrentUser(null)
  }

  const logAction = (action: string, description: string): void => {
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

  const updateContract = (contractId: number, updates: Partial<Contract>): void => {
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
    const now = new Date().toISOString()
    const newContract: Contract = {
      ...contractData,
      id: contractData.id ?? Date.now(),
      contractNumber: contractData.contractNumber ?? `TEMP-${Date.now()}`,
      status: contractData.status ?? 'active',
      createdBy: contractData.createdBy ?? currentUser?.fullName ?? 'Система',
      createdAt: contractData.createdAt ?? now,
      updatedAt: contractData.updatedAt ?? now,
      clientData: {
        ...defaultClientData,
        ...(contractData.clientData ?? {}),
      },
      primaryInfo: contractData.primaryInfo ?? {},
      pretrial: contractData.pretrial ?? {},
      introduction: contractData.introduction ?? {},
      procedure: contractData.procedure ?? {},
      preTrialData: contractData.preTrialData ?? {},
      trialData: contractData.trialData ?? {},
      creditors: contractData.creditors ?? [],
      executionProceedings: contractData.executionProceedings ?? [],
      bankAccount: contractData.bankAccount ?? '',
      specialAccount: contractData.specialAccount ?? '',
      assets: contractData.assets ?? {},
      reports: contractData.reports ?? [],
    }
    setContracts(prev => [newContract, ...prev])
    logAction('create_contract', `Создание нового договора №${newContract.contractNumber}`)
    return newContract
  }

  const addToReferenceData = (dbName: keyof ReferenceData, item: ReferenceItem): void => {
    setReferenceData(prev => ({
      ...prev,
      [dbName]: [...(prev[dbName] || []), { ...item, id: item.id || Date.now() }]
    }))
    logAction('add_to_reference_data', `Добавление в справочник ${String(dbName)}`)
  }

  // Templates functions
  const addTemplate = (template: Template): void => {
    setTemplates(prev => [template, ...prev])
    logAction('add_template', `Загружен шаблон ${template.name}`)
  }

  const deleteTemplate = (templateId: number): void => {
    setTemplates(prev => prev.filter(t => t.id !== templateId))
    logAction('delete_template', `Удалён шаблон`)
  }

  // Reports functions
  const addReport = (report: Report): void => {
    setReports(prev => [report, ...prev])
    logAction('add_report', `Загружен отчёт ${report.name}`)
  }

  const deleteReport = (reportId: number): void => {
    setReports(prev => prev.filter(report => report.id !== reportId))
    logAction('delete_report', `Удалён отчёт ${reportId}`)
  }

  const value: AppContextValue = {
    currentUser,
    contracts,
    referenceData,
    reports,
    templates,
    actionLog,
    login,
    logout,
    updateContract,
    createContract,
    addToReferenceData,
    logAction,
    addTemplate,
    deleteTemplate,
    addReport,
    deleteReport
  }

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>
}
