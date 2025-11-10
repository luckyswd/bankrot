import { useFormContext } from "react-hook-form"
import { FileText } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { TabsContent } from "@/components/ui/tabs"
import { FormValues } from "../types"


interface ProcedureTabProps {
  openDocument: (docType: string) => void
}

export const ProcedureTab = ({ openDocument }: ProcedureTabProps) => {
  const { register } = useFormContext<FormValues>()

  return (
    <TabsContent value="procedure" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>2. Процедура</CardTitle>
          <CardDescription>Данные процедуры реализации</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="procedure.creditorRequirement">1. Требование кредитора</Label>
              <Input id="procedure.creditorRequirement" placeholder="Выбор из списка" {...register("procedure.creditorRequirement")} />
              <p className="text-xs text-muted-foreground">Наименование кредитора, ОГРН, ИНН, адрес</p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="procedure.receivedRequirements">2. Полученные требования кредитора</Label>
              <Input id="procedure.receivedRequirements" placeholder="Выбор из списка" {...register("procedure.receivedRequirements")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="procedure.principalAmount">3. Основная сумма</Label>
              <Input id="procedure.principalAmount" type="number" {...register("procedure.principalAmount")} />
            </div>
          </div>

          <Separator className="my-6" />

          <div className="space-y-2">
            <h4 className="font-semibold">Документы процедуры:</h4>
            <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
              <Button onClick={() => openDocument("receivedRequirement")} variant="outline" className="justify-start" size="sm">
                <FileText className="mr-2 h-4 w-4" />
                Публикация о получении требования
              </Button>
              <Button onClick={() => openDocument("includedRequirement")} variant="outline" className="justify-start" size="sm">
                <FileText className="mr-2 h-4 w-4" />
                Публикация о включении в реестр
              </Button>
              <Button onClick={() => openDocument("financialReport")} variant="outline" className="justify-start" size="sm">
                <FileText className="mr-2 h-4 w-4" />
                Отчет финансового управляющего
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}
