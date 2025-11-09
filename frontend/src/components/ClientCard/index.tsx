import { useCallback, useEffect, useRef, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { FormProvider, useForm, useWatch } from "react-hook-form"
import { ArrowLeft, Save } from "lucide-react"
import { Toast } from "@/components/ui/toast"

import { useApp } from "@/context/AppContext"
import { apiRequest } from "@/config/api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs"
import Loading from "@/components/Loading"

import { GeneralTab, SaveContext } from "./General"
import { PretrialTab } from "./Pretrial"
import { JudicialTab } from "./JudicialTab"

type PrimaryInfoFields = {
  lastName?: string | null
  firstName?: string | null
  middleName?: string | null
  isLastNameChanged?: boolean | null
  changedLastName?: string | null
  birthDate?: string | null
  birthPlace?: string | null
  snils?: string | null
  registrationRegion?: string | null
  registrationDistrict?: string | null
  registrationCity?: string | null
  registrationSettlement?: string | null
  registrationStreet?: string | null
  registrationHouse?: string | null
  registrationBuilding?: string | null
  registrationApartment?: string | null
  passportSeries?: string | null
  passportNumber?: string | null
  passportIssuedBy?: string | null
  passportIssuedDate?: string | null
  passportDepartmentCode?: string | null
  maritalStatus?: string | null
  spouseFullName?: string | null
  spouseBirthDate?: string | null
  hasMinorChildren?: boolean | null
  children?: Array<{
    firstName: string
    lastName: string
    middleName?: string | null
    isLastNameChanged: boolean
    changedLastName?: string | null
    birthDate: string
  }> | null
  isStudent?: boolean | null
  employerName?: string | null
  employerAddress?: string | null
  employerInn?: string | null
  socialBenefits?: string | null
  phone?: string | null
  email?: string | null
  mailingAddress?: string | null
  debtAmount?: string | null
  hasEnforcementProceedings?: boolean | null
  contractNumber?: string | null
  contractDate?: string | null
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
  lastName: null,
  firstName: null,
  middleName: null,
  isLastNameChanged: null,
  changedLastName: null,
  birthDate: null,
  birthPlace: null,
  snils: null,
  registrationRegion: null,
  registrationDistrict: null,
  registrationCity: null,
  registrationSettlement: null,
  registrationStreet: null,
  registrationHouse: null,
  registrationBuilding: null,
  registrationApartment: null,
  passportSeries: null,
  passportNumber: null,
  passportIssuedBy: null,
  passportIssuedDate: null,
  passportDepartmentCode: null,
  maritalStatus: null,
  spouseFullName: null,
  spouseBirthDate: null,
  hasMinorChildren: null,
  children: null,
  isStudent: null,
  employerName: null,
  employerAddress: null,
  employerInn: null,
  socialBenefits: null,
  phone: null,
  email: null,
  mailingAddress: null,
  debtAmount: null,
  hasEnforcementProceedings: null,
  contractNumber: null,
  contractDate: null,
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

  const basicInfo = apiData.basic_info as Record<string, unknown> | undefined ?? {}
  
  // Преобразуем даты из формата API в строки
  const formatDate = (date: unknown): string | null => {
    if (!date) return null
    if (typeof date === 'string') return date
    if (date instanceof Date) return date.toISOString().split('T')[0]
    return String(date)
  }

  return {
    ...defaults,
    primaryInfo: {
      ...defaults.primaryInfo,
      lastName: basicInfo.lastName ?? null,
      firstName: basicInfo.firstName ?? null,
      middleName: basicInfo.middleName ?? null,
      isLastNameChanged: basicInfo.isLastNameChanged ?? null,
      changedLastName: basicInfo.changedLastName ?? null,
      birthDate: formatDate(basicInfo.birthDate),
      birthPlace: basicInfo.birthPlace ?? null,
      snils: basicInfo.snils ?? null,
      registrationRegion: basicInfo.registrationRegion ?? null,
      registrationDistrict: basicInfo.registrationDistrict ?? null,
      registrationCity: basicInfo.registrationCity ?? null,
      registrationSettlement: basicInfo.registrationSettlement ?? null,
      registrationStreet: basicInfo.registrationStreet ?? null,
      registrationHouse: basicInfo.registrationHouse ?? null,
      registrationBuilding: basicInfo.registrationBuilding ?? null,
      registrationApartment: basicInfo.registrationApartment ?? null,
      passportSeries: basicInfo.passportSeries ?? null,
      passportNumber: basicInfo.passportNumber ?? null,
      passportIssuedBy: basicInfo.passportIssuedBy ?? null,
      passportIssuedDate: formatDate(basicInfo.passportIssuedDate),
      passportDepartmentCode: basicInfo.passportDepartmentCode ?? null,
      maritalStatus: basicInfo.maritalStatus ?? null,
      spouseFullName: basicInfo.spouseFullName ?? null,
      spouseBirthDate: formatDate(basicInfo.spouseBirthDate),
      hasMinorChildren: basicInfo.hasMinorChildren ?? null,
      children: basicInfo.children ?? null,
      isStudent: basicInfo.isStudent ?? null,
      employerName: basicInfo.employerName ?? null,
      employerAddress: basicInfo.employerAddress ?? null,
      employerInn: basicInfo.employerInn ?? null,
      socialBenefits: basicInfo.socialBenefits ?? null,
      phone: basicInfo.phone ?? null,
      email: basicInfo.email ?? null,
      mailingAddress: basicInfo.mailingAddress ?? null,
      debtAmount: basicInfo.debtAmount ? String(basicInfo.debtAmount) : null,
      hasEnforcementProceedings: basicInfo.hasEnforcementProceedings ?? null,
      contractNumber: basicInfo.contractNumber ?? null,
      contractDate: formatDate(basicInfo.contractDate),
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
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)

  // Функция для ручного сохранения
  const saveContract = useCallback(async () => {
    if (!contract || loading) return

    const currentValues = form.getValues()
    const serialized = JSON.stringify(currentValues)
    
    if (serialized === savedSnapshotRef.current) {
      setToast({ message: "Нет изменений для сохранения", type: "info" })
      setTimeout(() => setToast(null), 3000)
      return // Нет изменений
    }

    const apiData: Record<string, unknown> = {
      basic_info: currentValues.primaryInfo || {},
      pre_court: currentValues.pretrial || {},
      procedure_initiation: currentValues.introduction || {},
      procedure: currentValues.procedure || {},
    }

    try {
      await apiRequest(`/api/v1/contracts/${contract.id}`, {
        method: 'PUT',
        body: JSON.stringify(apiData),
      })
      savedSnapshotRef.current = serialized
      // Сбрасываем состояние формы, чтобы isDirty стал false
      form.reset(currentValues, { keepValues: true })
      setToast({ message: "Изменения успешно сохранены", type: "success" })
      setTimeout(() => {
        setToast(null)
      }, 3000)
    } catch (err) {
      console.error('Ошибка при сохранении:', err)
      setToast({ 
        message: err instanceof Error ? err.message : "Не удалось сохранить изменения", 
        type: "error" 
      })
      setTimeout(() => setToast(null), 5000)
    }
  }, [contract, loading, form])

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

  // Автосохранение отключено - сохранение только по кнопке

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
  const fullName = [
    primaryInfo.lastName,
    primaryInfo.firstName,
    primaryInfo.middleName
  ].filter(Boolean).join(" ") || contract?.contractNumber || "Новый договор"

  const isDirty = form.formState.isDirty

  return (
    <SaveContext.Provider value={saveContract}>
      <FormProvider {...form}>
        {toast && (
          <Toast
            message={toast.message}
            type={toast.type}
            onClose={() => setToast(null)}
            duration={toast.type === "error" ? 5000 : 3000}
          />
        )}
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
          <Badge variant={contract.status === "active" || contract.status === "В работе" ? "blue" : "green"}>
            {contract.status === "active" || contract.status === "В работе" ? "В работе" : "Завершено"}
          </Badge>
        </div>
      </div>

      <Tabs defaultValue="primary">
        <div className="flex flex-col">
          <TabsList className="grid w-full grid-cols-3 h-11 rounded-b-none border-b-0">
            <TabsTrigger value="primary" className="text-sm font-medium">Основная информация</TabsTrigger>
            <TabsTrigger value="pretrial" className="text-sm font-medium">Досудебка</TabsTrigger>
            <TabsTrigger value="judicial" className="text-sm font-medium">Судебка</TabsTrigger>
          </TabsList>
          <TabsContent value="judicial" className="mt-0 p-0 border-0">
            <JudicialTab openDocument={openDocument} databases={databases} />
          </TabsContent>
        </div>

        <div className="mt-6 pb-24">
          <GeneralTab />
          <PretrialTab openDocument={openDocument} databases={databases} />
        </div>
      </Tabs>

      {/* Фиксированная кнопка сохранения внизу справа */}
      <div className="fixed bottom-4 z-50" style={{ right: '2rem' }}>
        <Button type="button" onClick={saveContract} disabled={!isDirty || loading} size="lg" className="shadow-lg text-base px-6 py-6">
          <Save className="h-5 w-5 mr-2" />
          Сохранить
        </Button>
      </div>
        </div>
      </FormProvider>
    </SaveContext.Provider>
  )
}

export default ClientCard
