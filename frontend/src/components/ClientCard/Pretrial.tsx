import { Controller, useFormContext } from "react-hook-form"
import { useMemo, useState } from "react"

import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { TabsContent } from "@/components/ui/tabs"
import { FormValues } from "./types"
import { DocumentsList } from "./DocumentsList"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"


import type { ReferenceData } from "@/types/reference"

interface PretrialTabProps {
  openDocument: (document: { id: number; name: string }) => void
  onDownload: (document: { id: number; name: string }) => void
  referenceData?: ReferenceData
  contractData?: Record<string, unknown> | null
}

export const PretrialTab = ({ openDocument, onDownload, referenceData, contractData }: PretrialTabProps): JSX.Element => {
  const { register, control } = useFormContext<FormValues>()
  const [creditorsOpen, setCreditorsOpen] = useState(false)
  const creditorsNameById = useMemo(() => {
    const map = new Map<string, string>()
    referenceData?.creditors?.forEach((c) => map.set(String(c.id), c.name))
    return map
  }, [referenceData?.creditors])
  
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
              <Controller
                name="pretrial.court"
                control={control}
                render={({ field }) => (
                  <Select value={field.value ? String(field.value) : ""} onValueChange={field.onChange}>
                    <SelectTrigger>
                      <SelectValue placeholder="Выберите суд" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.courts?.map((court) => (
                        <SelectItem key={court.id} value={String(court.id)}>
                          {court.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="pretrial.creditors">2. Кредиторы (можно несколько)</Label>
              <Controller
                name="pretrial.creditors"
                control={control}
                render={({ field }) => {
                  const selectedIds = useMemo(
                    () =>
                      Array.isArray(field.value)
                        ? field.value.map((v) => String(v))
                        : field.value
                          ? String(field.value).split(',').map((v) => v.trim()).filter(Boolean)
                          : [],
                    [field.value]
                  )

                  const toggle = (id: string) => {
                    const next = selectedIds.includes(id)
                      ? selectedIds.filter((item) => item !== id)
                      : [...selectedIds, id]
                    field.onChange(next)
                    setTimeout(() => setCreditorsOpen(true), 0) // держим список открытым
                  }

                  const valueForSelect = selectedIds[selectedIds.length - 1] ?? ""
                  const selectedLabels = selectedIds
                    .map((id) => creditorsNameById.get(id) || id)
                    .filter(Boolean)
                  const label = selectedLabels.length ? `Выбрано: ${selectedLabels.join(', ')}` : "Выберите кредиторов"

                  return (
                    <Select
                      open={creditorsOpen}
                      onOpenChange={setCreditorsOpen}
                      value={valueForSelect}
                      onValueChange={toggle}
                    >
                      <SelectTrigger>
                        <span className="line-clamp-1 text-left">{label}</span>
                      </SelectTrigger>
                      <SelectContent>
                        {referenceData?.creditors?.map((item) => (
                          <SelectItem key={item.id} value={String(item.id)}>
                            <div className="flex items-center gap-2">
                              <span className="flex h-4 w-4 items-center justify-center rounded border border-muted">
                                {selectedIds.includes(String(item.id)) && <span className="h-2.5 w-2.5 rounded-sm bg-primary" />}
                              </span>
                              <span className="line-clamp-1">{item.name}</span>
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  )
                }}
              />
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
              <Controller
                name="pretrial.creditor"
                control={control}
                render={({ field }) => (
                  <Select value={field.value ? String(field.value) : ""} onValueChange={field.onChange}>
                    <SelectTrigger>
                      <SelectValue placeholder="Выберите кредитора" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.creditors?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
              />
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
