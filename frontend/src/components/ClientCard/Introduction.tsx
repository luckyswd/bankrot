import { FileText } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { TabsContent } from "@/components/ui/tabs"
import { DatePickerInput } from "@/components/ui/DatePickerInput"

type FieldAccessor = (path: string) => unknown
type FieldUpdater = (path: string, value: unknown) => void

interface ReferenceItem {
  id: number | string
  name: string
}

interface IntroductionTabProps {
  handleChange: FieldUpdater
  getValue: FieldAccessor
  openDocument: (docType: string) => void
  databases?: {
    fns?: ReferenceItem[]
  }
}

export const IntroductionTab = ({
  getValue,
  handleChange,
  openDocument,
  databases,
}: IntroductionTabProps) => {
  const getStringValue = (path: string) => (getValue(path) as string) || ""

  return (
    <TabsContent value="introduction" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>1. Введение процедуры</CardTitle>
          <CardDescription>Данные для введения процедуры банкротства</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <DatePickerInput
                label="1. Дата решения суда"
                value={getStringValue("introduction.courtDecisionDate")}
                onChange={(next) => handleChange("introduction.courtDecisionDate", next)}
              />
              <p className="text-xs text-muted-foreground">
                Если ставится дата суда, то информация не будет отображена в документе
              </p>
            </div>

            <div className="space-y-2">
              <Label>2. ГИМС</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("introduction.gims")}
                onChange={(e) => handleChange("introduction.gims", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>3. Гостехнадзор</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("introduction.gostechnadzor")}
                onChange={(e) => handleChange("introduction.gostechnadzor", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>4. ФНС</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("introduction.fns")}
                onChange={(e) => handleChange("introduction.fns", e.target.value)}
                list="fns-list"
              />
              <datalist id="fns-list">
                {databases?.fns?.map((item) => (
                  <option key={item.id} value={item.name} />
                ))}
              </datalist>
            </div>

            <div className="space-y-2">
              <Label>5. Номер документа</Label>
              <Input
                placeholder="Укажите номер"
                value={getStringValue("introduction.documentNumber")}
                onChange={(e) => handleChange("introduction.documentNumber", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>6. Номер дела</Label>
              <Input
                value={getStringValue("introduction.caseNumber")}
                onChange={(e) => handleChange("introduction.caseNumber", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>7. Росавиация</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("introduction.rosaviation")}
                onChange={(e) => handleChange("introduction.rosaviation", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>8. Номер дела</Label>
              <Input
                value={getStringValue("introduction.caseNumber2")}
                onChange={(e) => handleChange("introduction.caseNumber2", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>9. Судья</Label>
              <Input
                value={getStringValue("introduction.judge")}
                onChange={(e) => handleChange("introduction.judge", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>10. Судебный пристав</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("introduction.bailiff")}
                onChange={(e) => handleChange("introduction.bailiff", e.target.value)}
              />
            </div>

            <div className="space-y-2 md:col-span-2">
              <Label>12. Окончание исполнительных производств</Label>
              <div className="flex gap-2">
                <Input
                  placeholder="Номер"
                  value={getStringValue("introduction.executionNumber")}
                  onChange={(e) => handleChange("introduction.executionNumber", e.target.value)}
                />
                <DatePickerInput
                  label="Дата"
                  value={getStringValue("introduction.executionDate")}
                  onChange={(next) => handleChange("introduction.executionDate", next)}
                  className="space-y-1 flex-1"
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label>13. Номер спец счёта</Label>
              <Input
                value={getStringValue("introduction.specialAccountNumber")}
                onChange={(e) => handleChange("introduction.specialAccountNumber", e.target.value)}
              />
            </div>
          </div>

          <Separator className="my-6" />

          <div className="space-y-2">
            <h4 className="font-semibold">Документы этапа введения:</h4>
            <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
              <Button onClick={() => openDocument("efrsbPublication")} variant="outline" className="justify-start" size="sm">
                <FileText className="mr-2 h-4 w-4" />
                Публикация ЕФРСБ
              </Button>
              <Button
                onClick={() => openDocument("kommersantPublication")}
                variant="outline"
                className="justify-start"
                size="sm"
              >
                <FileText className="mr-2 h-4 w-4" />
                Заявка Коммерсантъ
              </Button>
              <Button onClick={() => openDocument("spouseNotification")} variant="outline" className="justify-start" size="sm">
                <FileText className="mr-2 h-4 w-4" />
                Уведомление супругу
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}
