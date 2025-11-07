import { useEffect, useMemo, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { useForm, useWatch } from "react-hook-form"
import { ArrowLeft, Save } from "lucide-react"

import { useApp } from "@/context/AppContext"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"

import { GeneralTab } from "./General"
import { IntroductionTab } from "./Introduction"
import { PretrialTab } from "./Pretrial"
import { ProcedureTab } from "./Procedure"

type FormValues = Record<string, unknown>

function ClientCard() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const { contracts, updateContract, databases } = useApp()

  const contract = useMemo(() => contracts.find((c) => c.id === Number(id)), [contracts, id])
  const form = useForm<FormValues>({
    mode: "onChange",
    defaultValues: contract ?? {},
  })
  const watchedValues =
    useWatch<FormValues>({
      control: form.control,
    }) ?? form.getValues()
  const [saveStatus, setSaveStatus] = useState<"" | "saving" | "saved">("")

  useEffect(() => {
    if (contract) {
      form.reset(contract)
    }
  }, [contract, form])

  useEffect(() => {
    if (!contract || !watchedValues) return

    const timer = window.setTimeout(() => {
      if (JSON.stringify(watchedValues) !== JSON.stringify(contract)) {
        setSaveStatus("saving")
        updateContract(contract.id, watchedValues)
        window.setTimeout(() => {
          setSaveStatus("saved")
          window.setTimeout(() => setSaveStatus(""), 2000)
        }, 500)
      }
    }, 1000)

    return () => window.clearTimeout(timer)
  }, [watchedValues, contract, updateContract])

  if (!contract) {
    return (
      <div className="flex h-full items-center justify-center">
        <Card className="p-8 text-center">
          <p className="mb-4 text-lg">Договор не найден</p>
          <Button onClick={() => navigate("/contracts")}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Вернуться к списку
          </Button>
        </Card>
      </div>
    )
  }

  const handleChange = (path: string, value: unknown) => {
    form.setValue(path as any, value, { shouldDirty: true, shouldTouch: true })
  }

  const getValue = (path: string): string => {
    const currentValue = form.getValues(path as any) as string
    return currentValue ?? ""
  }

  const openDocument = (docType: string) => {
    navigate(`/document/${contract.id}/${docType}`)
  }

  return (
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
              {getValue("primaryInfo.lastName")} {getValue("primaryInfo.firstName")} {getValue("primaryInfo.middleName")}
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
          <Badge variant={contract.status === "active" ? "default" : "success"}>
            {contract.status === "active" ? "В работе" : "Завершено"}
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

        <GeneralTab handleChange={handleChange} getValue={getValue} />
        <PretrialTab handleChange={handleChange} getValue={getValue} openDocument={openDocument} databases={databases} />
        <IntroductionTab handleChange={handleChange} getValue={getValue} openDocument={openDocument} databases={databases} />
        <ProcedureTab handleChange={handleChange} getValue={getValue} openDocument={openDocument} />
      </Tabs>
    </div>
  )
}

export default ClientCard
