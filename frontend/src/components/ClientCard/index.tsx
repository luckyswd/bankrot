import { useEffect, useRef, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { FormProvider, useForm, useWatch } from "react-hook-form"
import { ArrowLeft, Save } from "lucide-react"

import { useApp } from "@/context/AppContext"
import { apiRequest } from "@/config/api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import Loading from "@/components/Loading"

import { GeneralTab } from "./General"
import { IntroductionTab } from "./Introduction"
import { PretrialTab } from "./Pretrial"
import { ProcedureTab } from "./Procedure"

type PrimaryInfoFields = {
  lastName: string
  firstName: string
  middleName: string
  fullName: string
  nameChanged: string
  birthDate: string
  birthPlace: string
  snils: string
  registrationAddress: string
  passport: string
  married: string
  spouseName: string
  hasMinorChildren: string
  childName: string
  isStudent: string
  work: string
  socialPayments: string
  phone: string
  email: string
  correspondenceAddress: string
  debtAmount: string
  hasExecutions: string
}

type PretrialFields = {
  court: string
  creditors: string
  powerOfAttorneyNumber: string
  powerOfAttorneyDate: string
  creditor: string
  caseNumber: string
  hearingDate: string
  hearingTime: string
}

type IntroductionFields = {
  courtDecisionDate: string
  gims: string
  gostechnadzor: string
  fns: string
  documentNumber: string
  caseNumber: string
  rosaviation: string
  caseNumber2: string
  judge: string
  bailiff: string
  executionNumber: string
  executionDate: string
  specialAccountNumber: string
}

type ProcedureFields = {
  creditorRequirement: string
  receivedRequirements: string
  principalAmount: string
}

type FormSections = {
  primaryInfo: PrimaryInfoFields
  pretrial: PretrialFields
  introduction: IntroductionFields
  procedure: ProcedureFields
}

export type FormValues = FormSections & Record<string, unknown>

const defaultPrimaryInfo: PrimaryInfoFields = {
  lastName: "",
  firstName: "",
  middleName: "",
  fullName: "",
  nameChanged: "",
  birthDate: "",
  birthPlace: "",
  snils: "",
  registrationAddress: "",
  passport: "",
  married: "",
  spouseName: "",
  hasMinorChildren: "",
  childName: "",
  isStudent: "",
  work: "",
  socialPayments: "",
  phone: "",
  email: "",
  correspondenceAddress: "",
  debtAmount: "",
  hasExecutions: "",
}

const defaultPretrial: PretrialFields = {
  court: "",
  creditors: "",
  powerOfAttorneyNumber: "",
  powerOfAttorneyDate: "",
  creditor: "",
  caseNumber: "",
  hearingDate: "",
  hearingTime: "",
}

const defaultIntroduction: IntroductionFields = {
  courtDecisionDate: "",
  gims: "",
  gostechnadzor: "",
  fns: "",
  documentNumber: "",
  caseNumber: "",
  rosaviation: "",
  caseNumber2: "",
  judge: "",
  bailiff: "",
  executionNumber: "",
  executionDate: "",
  specialAccountNumber: "",
}

const defaultProcedure: ProcedureFields = {
  creditorRequirement: "",
  receivedRequirements: "",
  principalAmount: "",
}

const createDefaultFormValues = (): FormValues => ({
  primaryInfo: { ...defaultPrimaryInfo },
  pretrial: { ...defaultPretrial },
  introduction: { ...defaultIntroduction },
  procedure: { ...defaultProcedure },
})

// Преобразование данных из API формата в формат формы
const convertApiDataToFormValues = (apiData?: Record<string, unknown>): FormValues => {
  const defaults = createDefaultFormValues()
  if (!apiData) {
    return defaults
  }

  return {
    ...defaults,
    primaryInfo: {
      ...defaults.primaryInfo,
      ...(apiData.basic_info as Partial<PrimaryInfoFields> ?? {}),
    },
    pretrial: {
      ...defaults.pretrial,
      ...(apiData.pre_court as Partial<PretrialFields> ?? {}),
    },
    introduction: {
      ...defaults.introduction,
      ...(apiData.procedure_initiation as Partial<IntroductionFields> ?? {}),
    },
    procedure: {
      ...defaults.procedure,
      ...(apiData.procedure as Partial<ProcedureFields> ?? {}),
    },
  }
}

const buildFormValues = (contract?: Record<string, unknown>): FormValues => {
  const defaults = createDefaultFormValues()
  if (!contract) {
    return defaults
  }

  // Если данные пришли из API (с basic_info, pre_court и т.д.)
  if (contract.basic_info || contract.pre_court || contract.procedure_initiation || contract.procedure) {
    return convertApiDataToFormValues(contract)
  }

  // Если данные в старом формате (primaryInfo, pretrial и т.д.)
  const overrides = contract as Partial<FormSections>

  return {
    ...defaults,
    ...contract,
    primaryInfo: {
      ...defaults.primaryInfo,
      ...(overrides.primaryInfo ?? {}),
    },
    pretrial: {
      ...defaults.pretrial,
      ...(overrides.pretrial ?? {}),
    },
    introduction: {
      ...defaults.introduction,
      ...(overrides.introduction ?? {}),
    },
    procedure: {
      ...defaults.procedure,
      ...(overrides.procedure ?? {}),
    },
  }
}

function ClientCard() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const { databases } = useApp()
  const [contract, setContract] = useState<{ id: number; contractNumber: string; status: string } | null>(null)
  const [contractData, setContractData] = useState<Record<string, unknown> | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const form = useForm<FormValues>({
    mode: "onChange",
    defaultValues: createDefaultFormValues(),
  })
  const savedSnapshotRef = useRef<string>(JSON.stringify(createDefaultFormValues()))
  const watchedValues =
    useWatch<FormValues>({
      control: form.control,
    }) ?? form.getValues()
  const [saveStatus, setSaveStatus] = useState<"" | "saving" | "saved">("")

  // Загрузка контракта из API
  useEffect(() => {
    const loadContract = async () => {
      if (!id || id === 'new') {
        setLoading(false)
        setContract(null)
        setContractData(null)
        const defaults = createDefaultFormValues()
        form.reset(defaults)
        savedSnapshotRef.current = JSON.stringify(defaults)
        return
      }

      try {
        setLoading(true)
        setError(null)
        const data = await apiRequest(`/api/v1/contracts/${id}`)
        
        if (data && typeof data === 'object') {
          // Преобразуем данные из API формата
          const formValues = convertApiDataToFormValues(data)
          form.reset(formValues)
          savedSnapshotRef.current = JSON.stringify(formValues)
          
          // Сохраняем базовую информацию о контракте
          setContract({
            id: Number(id),
            contractNumber: (data.basic_info as any)?.contractNumber || `ДГ-${id}`,
            status: (data.basic_info as any)?.status || 'active',
          })
          setContractData(data)
        } else {
          setError('Контракт не найден')
        }
      } catch (err) {
        console.error('Ошибка при загрузке контракта:', err)
        setError('Не удалось загрузить контракт')
      } finally {
        setLoading(false)
      }
    }

    loadContract()
  }, [id, form])

  // Автосохранение изменений
  useEffect(() => {
    if (!contract || !watchedValues || loading) return

    const timer = setTimeout(() => {
      const serialized = JSON.stringify(watchedValues)
      if (serialized !== savedSnapshotRef.current) {
        setSaveStatus("saving")
        
        // Преобразуем данные формы в формат API
        const apiData: Record<string, unknown> = {
          basic_info: watchedValues.primaryInfo || {},
          pre_court: watchedValues.pretrial || {},
          procedure_initiation: watchedValues.introduction || {},
          procedure: watchedValues.procedure || {},
        }

        // Отправляем на сервер
        apiRequest(`/api/v1/contracts/${contract.id}`, {
          method: 'PUT',
          body: JSON.stringify(apiData),
        })
          .then(() => {
            savedSnapshotRef.current = serialized
            setSaveStatus("saved")
            setTimeout(() => setSaveStatus(""), 2000)
          })
          .catch((err) => {
            console.error('Ошибка при сохранении:', err)
            setSaveStatus("")
          })
      }
    }, 1000)

    return () => clearTimeout(timer)
  }, [watchedValues, contract, loading])

  if (loading) {
    return <Loading fullScreen text="Загрузка контракта..." />
  }

  if (error || !contract) {
    return (
      <div className="flex h-full items-center justify-center">
        <Card className="p-8 text-center">
          <p className="mb-4 text-lg">{error || 'Договор не найден'}</p>
          <Button onClick={() => navigate("/contracts")}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Вернуться к списку
          </Button>
        </Card>
      </div>
    )
  }

  const openDocument = (docType: string) => {
    navigate(`/document/${contract.id}/${docType}`)
  }

  const primaryInfo = (watchedValues.primaryInfo ?? {}) as Partial<PrimaryInfoFields>
  const fullName = [primaryInfo.lastName, primaryInfo.firstName, primaryInfo.middleName].filter(Boolean).join(" ")

  return (
    <FormProvider {...form}>
      <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="outline" onClick={() => navigate("/contracts")}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Назад
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Договор № {contract.contractNumber}</h1>
            <p className="text-sm text-muted-foreground">
              {fullName}
            </p>
          </div>
        </div>
        <div className="flex items-center gap-3">
          {saveStatus && (
            <div
              className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm ${
                saveStatus === "saving"
                  ? "bg-yellow-500/10 text-yellow-600 dark:text-yellow-500"
                  : "bg-green-500/10 text-green-600 dark:text-green-500"
              }`}
            >
              <Save className="h-4 w-4" />
              {saveStatus === "saving" ? "Сохранение..." : "Сохранено"}
            </div>
          )}
          <Badge variant={contract.status === "active" || contract.status === "В работе" ? "blue" : "green"}>
            {contract.status === "active" || contract.status === "В работе" ? "В работе" : "Завершено"}
          </Badge>
        </div>
      </div>

      <Tabs defaultValue="primary" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="primary">Основная информация</TabsTrigger>
          <TabsTrigger value="pretrial">Досудебка</TabsTrigger>
          <TabsTrigger value="introduction">Введение процедуры</TabsTrigger>
          <TabsTrigger value="procedure">Процедура</TabsTrigger>
        </TabsList>

        <GeneralTab />
        <PretrialTab openDocument={openDocument} databases={databases} />
        <IntroductionTab openDocument={openDocument} databases={databases} />
        <ProcedureTab openDocument={openDocument} />
      </Tabs>
    </div>
    </FormProvider>
  )
}

export default ClientCard
