import { useEffect, useMemo, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { ArrowLeft, Save } from "lucide-react"

import { useApp } from "@/context/AppContext"
import { Badge } from "@ui/badge"
import { Button } from "@ui/button"
import { Card } from "@ui/card"
import { Tabs, TabsList, TabsTrigger } from "@ui/tabs"

import { GeneralTab } from "./General"
import { IntroductionTab } from "./Introduction"
import { PretrialTab } from "./Pretrial"
import { ProcedureTab } from "./Procedure"

type FormData = Record<string, unknown>

function ClientCard() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const { contracts, updateContract, databases } = useApp()

  const contract = useMemo(() => contracts.find((c) => c.id === Number(id)), [contracts, id])
  const [formData, setFormData] = useState<FormData>(contract ?? {})
  const [saveStatus, setSaveStatus] = useState<"" | "saving" | "saved">("")

  useEffect(() => {
    setFormData(contract ?? {})
  }, [contract])

  useEffect(() => {
    if (!contract) return

    const timer = window.setTimeout(() => {
      if (JSON.stringify(formData) !== JSON.stringify(contract)) {
        setSaveStatus("saving")
        updateContract(contract.id, formData)
        window.setTimeout(() => {
          setSaveStatus("saved")
          window.setTimeout(() => setSaveStatus(""), 2000)
        }, 500)
      }
    }, 1000)

    return () => window.clearTimeout(timer)
  }, [formData, contract, updateContract])

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
    const keys = path.split(".")
    setFormData((prev) => {
      const newData: FormData = structuredClone(prev)
      let current = newData

      for (let i = 0; i < keys.length - 1; i += 1) {
        const key = keys[i]
        if (typeof current[key] !== "object" || current[key] === null) {
          current[key] = {}
        }
        current = current[key] as FormData
      }

      current[keys[keys.length - 1]] = value
      return newData
    })
  }

  const getValue = (path: string) => {
    const keys = path.split(".")
    let current: any = formData

    for (const key of keys) {
      if (typeof current !== "object" || current === null || !(key in current)) {
        return ""
      }
      current = (current as Record<string, unknown>)[key]
    }

    return current
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
