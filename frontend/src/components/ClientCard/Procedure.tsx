import { FileText } from "lucide-react"

import { Button } from "@ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@ui/card"
import { Input } from "@ui/input"
import { Label } from "@ui/label"
import { Separator } from "@ui/separator"
import { TabsContent } from "@ui/tabs"

type FieldAccessor = (path: string) => unknown
type FieldUpdater = (path: string, value: unknown) => void

interface ProcedureTabProps {
  handleChange: FieldUpdater
  getValue: FieldAccessor
  openDocument: (docType: string) => void
}

export const ProcedureTab = ({ handleChange, getValue, openDocument }: ProcedureTabProps) => {
  const getStringValue = (path: string) => (getValue(path) as string) || ""

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
              <Label>1. Требование кредитора</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("procedure.creditorRequirement")}
                onChange={(e) => handleChange("procedure.creditorRequirement", e.target.value)}
              />
              <p className="text-xs text-muted-foreground">Наименование кредитора, ОГРН, ИНН, адрес</p>
            </div>

            <div className="space-y-2">
              <Label>2. Полученные требования кредитора</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("procedure.receivedRequirements")}
                onChange={(e) => handleChange("procedure.receivedRequirements", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>3. Основная сумма</Label>
              <Input
                type="number"
                value={getStringValue("procedure.principalAmount")}
                onChange={(e) => handleChange("procedure.principalAmount", e.target.value)}
              />
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
