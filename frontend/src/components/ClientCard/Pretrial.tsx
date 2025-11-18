import { Controller, useFormContext } from "react-hook-form"

import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { TabsContent } from "@/components/ui/tabs"
import { FormValues } from "./types"
import { DocumentsList } from "./DocumentsList"


import { ReferenceData } from "@/context/AppContext"

interface PretrialTabProps {
  openDocument: (document: { id: number; name: string }) => void
  onDownload: (document: { id: number; name: string }) => void
  referenceData?: ReferenceData
  contractData?: Record<string, unknown> | null
}

export const PretrialTab = ({ openDocument, onDownload, referenceData, contractData }: PretrialTabProps): JSX.Element => {
  const { register, control } = useFormContext<FormValues>()
  
  const documents = (contractData?.pre_court as { documents?: Array<{ id: number; name: string }> })?.documents || []

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
                {referenceData?.courts?.map((court) => (
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

          <DocumentsList
            documents={documents}
            title="Документы досудебного этапа:"
            onDocumentClick={openDocument}
            onDownload={onDownload}
          />
        </CardContent>
      </Card>
    </TabsContent>
  )
}
