import { Controller, useFormContext } from "react-hook-form"
import { FileText } from "lucide-react"

import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { TabsContent } from "@/components/ui/tabs"

import type { FormValues } from "./index"

interface ReferenceItem {
  id: number | string
  name: string
}

interface IntroductionTabProps {
  openDocument: (docType: string) => void
  databases?: {
    fns?: ReferenceItem[]
  }
}

export const IntroductionTab = ({ openDocument, databases }: IntroductionTabProps) => {
  const { register, control } = useFormContext<FormValues>()

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
              <Controller
                name="introduction.courtDecisionDate"
                control={control}
                render={({ field }) => (
                  <DatePickerInput label="1. Дата решения суда" value={(field.value as string) ?? ""} onChange={field.onChange} />
                )}
              />
              <p className="text-xs text-muted-foreground">
                Если ставится дата суда, то информация не будет отображена в документе
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.gims">2. ГИМС</Label>
              <Input id="introduction.gims" placeholder="Выбор из списка" {...register("introduction.gims")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.gostechnadzor">3. Гостехнадзор</Label>
              <Input id="introduction.gostechnadzor" placeholder="Выбор из списка" {...register("introduction.gostechnadzor")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.fns">4. ФНС</Label>
              <Input id="introduction.fns" placeholder="Выбор из списка" list="fns-list" {...register("introduction.fns")} />
              <datalist id="fns-list">
                {databases?.fns?.map((item) => (
                  <option key={item.id} value={item.name} />
                ))}
              </datalist>
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.documentNumber">5. Номер документа</Label>
              <Input id="introduction.documentNumber" placeholder="Укажите номер" {...register("introduction.documentNumber")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.caseNumber">6. Номер дела</Label>
              <Input id="introduction.caseNumber" {...register("introduction.caseNumber")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.rosaviation">7. Росавиация</Label>
              <Input id="introduction.rosaviation" placeholder="Выбор из списка" {...register("introduction.rosaviation")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.caseNumber2">8. Номер дела</Label>
              <Input id="introduction.caseNumber2" {...register("introduction.caseNumber2")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.judge">9. Судья</Label>
              <Input id="introduction.judge" {...register("introduction.judge")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.bailiff">10. Судебный пристав</Label>
              <Input id="introduction.bailiff" placeholder="Выбор из списка" {...register("introduction.bailiff")} />
            </div>

            <div className="space-y-2 md:col-span-2">
              <Label>12. Окончание исполнительных производств</Label>
              <div className="flex gap-2">
                <Input placeholder="Номер" {...register("introduction.executionNumber")} />
                <Controller
                  name="introduction.executionDate"
                  control={control}
                  render={({ field }) => (
                    <DatePickerInput label="Дата" value={(field.value as string) ?? ""} onChange={field.onChange} className="space-y-1 flex-1" />
                  )}
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="introduction.specialAccountNumber">13. Номер спец счёта</Label>
              <Input id="introduction.specialAccountNumber" {...register("introduction.specialAccountNumber")} />
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
              <Button onClick={() => openDocument("kommersantPublication")} variant="outline" className="justify-start" size="sm">
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
