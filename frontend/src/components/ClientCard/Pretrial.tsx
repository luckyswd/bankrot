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

interface PretrialTabProps {
  openDocument: (docType: string) => void
  databases?: {
    courts?: ReferenceItem[]
  }
}

export const PretrialTab = ({ openDocument, databases }: PretrialTabProps) => {
  const { register, control } = useFormContext<FormValues>()

  return (
    <TabsContent value="pretrial" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Досудебка</CardTitle>
          <CardDescription>Информация о досудебном этапе</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="pretrial.court">1. Арбитражный суд</Label>
              <Input
                id="pretrial.court"
                placeholder="Выбор из списка"
                list="courts-list"
                {...register("pretrial.court")}
              />
              <datalist id="courts-list">
                {databases?.courts?.map((court) => (
                  <option key={court.id} value={court.name} />
                ))}
              </datalist>
            </div>

            <div className="space-y-2">
              <Label htmlFor="pretrial.creditors">2. Кредиторы</Label>
              <Input id="pretrial.creditors" placeholder="Выбор из списка" {...register("pretrial.creditors")} />
            </div>

            <div className="space-y-2">
              <Label>3. Доверенность</Label>
              <div className="grid grid-cols-2 gap-2">
                <Input
                  type="text"
                  placeholder="Номер"
                  {...register("pretrial.powerOfAttorneyNumber")}
                />
                <Controller
                  name="pretrial.powerOfAttorneyDate"
                  control={control}
                  render={({ field }) => (
                    <DatePickerInput
                      value={(field.value as string) ?? ""}
                      onChange={field.onChange}
                      className="space-y-1"
                    />
                  )}
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="pretrial.creditor">4. Кредитор</Label>
              <Input id="pretrial.creditor" placeholder="Выбор из списка" {...register("pretrial.creditor")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="pretrial.caseNumber">5. № Дела</Label>
              <Input id="pretrial.caseNumber" {...register("pretrial.caseNumber")} />
            </div>

            <div className="space-y-2">
              <Label>6. Дата и время заседания</Label>
              <div className="grid grid-cols-2 gap-2">
                <Controller
                  name="pretrial.hearingDate"
                  control={control}
                  render={({ field }) => (
                    <DatePickerInput value={(field.value as string) ?? ""} onChange={field.onChange} className="space-y-1" />
                  )}
                />
                <div className="space-y-1">
                  <Input type="time" {...register("pretrial.hearingTime")} />
                </div>
              </div>
            </div>
          </div>

          <Separator className="my-6" />

          <div className="space-y-3">
            <h4 className="font-semibold">Документы досудебного этапа:</h4>
            <Button onClick={() => openDocument("bankruptcyApplication")} variant="outline" className="w-full justify-start">
              <FileText className="mr-2 h-4 w-4" />
              Заявление о признании банкротом
            </Button>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}
