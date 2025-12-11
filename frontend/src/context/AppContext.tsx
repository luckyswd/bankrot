import { createContext, useContext, useEffect, useState, type ReactNode } from "react"
import { useQuery } from "@tanstack/react-query"
import { useRecoilState } from "recoil"

import { useAuth } from "./AuthContext"
import { apiRequest } from "../config/api"
import { referenceDataAtom } from "@/state/referenceData"
import type { ReferenceData, ReferenceItem } from "@/types/reference"

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
  basic_info?: Record<string, unknown>
  pre_court?: Record<string, unknown>
  judicial_procedure_initiation?: Record<string, unknown>
  judicial_procedure?: Record<string, unknown>
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
  const { token } = useAuth()
  const [currentUser, setCurrentUser] = useState<User | null>(null)
  const [contracts, setContracts] = useState<Contract[]>([])
  const [referenceData, setReferenceData] = useRecoilState(referenceDataAtom)
  const [templates, setTemplates] = useState<Template[]>([])
  const [actionLog, setActionLog] = useState<any[]>([])
  const [reports, setReports] = useState<Report[]>([])

  useEffect(() => {
    if (!token) {
      setReferenceData({})
    }
  }, [token, setReferenceData])

  const { data: loadedReferences } = useQuery<ReferenceData>({
    queryKey: ["references", "all"],
    enabled: Boolean(token),
    queryFn: async (): Promise<ReferenceData> => {
      const params = new URLSearchParams({ page: "1", limit: "1000" })
      const fetchList = async (endpoint: string) => {
        const data = await apiRequest(`${endpoint}?${params.toString()}`)

        if (Array.isArray((data as any)?.items)) return (data as any).items
        if (Array.isArray((data as any)?.data)) return (data as any).data
        if (Array.isArray(data)) return data as any[]
        return []
      }

      const normalizeItem = (item: any) => {
        const base = item && typeof item === "object" ? item : { value: item }

        return {
          name:
            (base as any).name ||
            (base as any).department ||
            (base as any).title ||
            (base as any).code ||
            String((base as any).value ?? "Без названия"),
          ...base,
        }
      }

      const endpoints = [
        { key: "courts", path: "/courts" },
        { key: "creditors", path: "/creditors" },
        { key: "fns", path: "/fns" },
        { key: "bailiffs", path: "/bailiffs" },
        { key: "mchs", path: "/mchs" },
        { key: "rosgvardia", path: "/rosgvardia" },
        { key: "gostekhnadzor", path: "/gostekhnadzor" },
        { key: "users", path: "/users" },
      ] as const

      const responses = await Promise.allSettled(endpoints.map(({ path }) => fetchList(path)))
      const loaded: ReferenceData = {}

      responses.forEach((result, index) => {
        const { key } = endpoints[index]

        if (result.status === "fulfilled") {
          loaded[key] = result.value.map(normalizeItem)
        } else {
          console.error(`Не удалось загрузить справочник ${key}:`, result.reason)
        }
      })

      return loaded
    },
  })

  useEffect(() => {
    if (loadedReferences) {
      setReferenceData(loadedReferences)
    }
  }, [loadedReferences, setReferenceData])

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
      basic_info: contractData.basic_info ?? {},
      pre_court: contractData.pre_court ?? {},
      judicial_procedure_initiation: contractData.judicial_procedure_initiation ?? {},
      judicial_procedure: contractData.judicial_procedure ?? {},
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
