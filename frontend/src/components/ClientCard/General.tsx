import { Controller, useFormContext, useWatch } from "react-hook-form"

import type { FormValues } from "./index"
import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { TabsContent } from "@/components/ui/tabs"

interface SelectFieldProps {
  value: string
  onChange: (value: string) => void
  options: { value: string; label: string }[]
  placeholder?: string
}

const yesNoOptions = [
  { value: "yes", label: "Да" },
  { value: "no", label: "Нет" },
]

const marriageOptions = [
  { value: "yes", label: "Да" },
  { value: "no", label: "Нет" },
  { value: "no_3", label: "Нет (но состоял в течении 3 лет)" },
]

const CLEAR_VALUE = "__clear__"

const SelectField = ({ value, onChange, options, placeholder = "Выбрать" }: SelectFieldProps) => (
  <Select
    value={value ? String(value) : undefined}
    onValueChange={(selected) => {
      if (selected === CLEAR_VALUE) {
        onChange("")
        return
      }
      onChange(selected)
    }}
  >
    <SelectTrigger>
      <SelectValue placeholder={placeholder} />
    </SelectTrigger>
    <SelectContent>
      <SelectItem value={CLEAR_VALUE}>--</SelectItem>
      {options.map((option) => (
        <SelectItem key={option.value} value={option.value}>
          {option.label}
        </SelectItem>
      ))}
    </SelectContent>
  </Select>
)

export const GeneralTab = () => {
  const { register, control } = useFormContext<FormValues>()
  const marriedValue = useWatch({
    control,
    name: "primaryInfo.married",
  }) as string | undefined

  return (
    <TabsContent value="primary" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Основная информация</CardTitle>
          <CardDescription>Заполните все необходимые поля</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div className="space-y-2">
              <Label htmlFor="primaryInfo.fullName">1. ФИО *</Label>
              <Input id="primaryInfo.fullName" placeholder="Фамилия Имя Отчество" {...register("primaryInfo.fullName")} />
            </div>

            <div className="space-y-2">
              <Label>2. Изменялось ли ФИО</Label>
              <Controller
                control={control}
                name="primaryInfo.nameChanged"
                render={({ field }) => (
                  <SelectField
                    value={(field.value as string) ?? ""}
                    onChange={(value) => field.onChange(value)}
                    options={yesNoOptions}
                  />
                )}
              />
            </div>

            <Controller
              control={control}
              name="primaryInfo.birthDate"
              render={({ field }) => (
                <DatePickerInput label="3. Дата рождения *" value={(field.value as string) ?? ""} onChange={field.onChange} />
              )}
            />

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.birthPlace">4. Место рождения</Label>
              <Input id="primaryInfo.birthPlace" placeholder="Город, страна" {...register("primaryInfo.birthPlace")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.snils">5. СНИЛС</Label>
              <Input id="primaryInfo.snils" placeholder="123-456-789 00" {...register("primaryInfo.snils")} />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label htmlFor="primaryInfo.registrationAddress">6. Адрес регистрации</Label>
              <Input
                id="primaryInfo.registrationAddress"
                placeholder="Субъект РФ, район, город, населенный пункт, улица, дом, корпус, квартира"
                {...register("primaryInfo.registrationAddress")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.passport">7. Паспорт (серия и номер)</Label>
              <Input id="primaryInfo.passport" placeholder="1234 567890" {...register("primaryInfo.passport")} />
            </div>

            <div className="space-y-2">
              <Label>8. Состоит в браке</Label>
              <Controller
                control={control}
                name="primaryInfo.married"
                render={({ field }) => (
                  <SelectField
                    value={(field.value as string) ?? ""}
                    onChange={(value) => field.onChange(value)}
                    options={marriageOptions}
                  />
                )}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.spouseName">9. Супруг(а) (опционально)</Label>
              <Input
                id="primaryInfo.spouseName"
                placeholder="ФИО супруга/супруги"
                disabled={!marriedValue || marriedValue === "no" || marriedValue === "no_3"}
                {...register("primaryInfo.spouseName")}
              />
            </div>

            <div className="space-y-2">
              <Label>10. Несовершеннолетние дети</Label>
              <Controller
                control={control}
                name="primaryInfo.hasMinorChildren"
                render={({ field }) => (
                  <SelectField
                    value={(field.value as string) ?? ""}
                    onChange={(value) => field.onChange(value)}
                    options={yesNoOptions}
                  />
                )}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.childName">11. ФИО (ребёнка)</Label>
              <Input id="primaryInfo.childName" placeholder="ФИО ребенка" {...register("primaryInfo.childName")} />
            </div>

            <div className="space-y-2">
              <Label>12. Студент</Label>
              <Controller
                control={control}
                name="primaryInfo.isStudent"
                render={({ field }) => (
                  <SelectField
                    value={(field.value as string) ?? ""}
                    onChange={(value) => field.onChange(value)}
                    options={yesNoOptions}
                  />
                )}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label htmlFor="primaryInfo.work">13. Работа</Label>
              <Input
                id="primaryInfo.work"
                placeholder="Наименование, адрес, ИНН..."
                {...register("primaryInfo.work")}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label htmlFor="primaryInfo.socialPayments">14. Пенсии и соц.выплаты</Label>
              <Input
                id="primaryInfo.socialPayments"
                placeholder="Алименты, пособие, ЕДВ, прочее"
                {...register("primaryInfo.socialPayments")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.phone">15. Телефон</Label>
              <Input id="primaryInfo.phone" type="tel" placeholder="+7 (999) 123-45-67" {...register("primaryInfo.phone")} />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.email">16. Электронная почта</Label>
              <Input id="primaryInfo.email" type="email" placeholder="email@example.com" {...register("primaryInfo.email")} />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label htmlFor="primaryInfo.correspondenceAddress">17. Адрес для направления корреспонденции</Label>
              <Input
                id="primaryInfo.correspondenceAddress"
                placeholder="196084, г. Санкт-Петербург, ул. Смоленская, 9-418"
                {...register("primaryInfo.correspondenceAddress")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="primaryInfo.debtAmount">18. Сумма долга</Label>
              <Input id="primaryInfo.debtAmount" type="number" placeholder="в руб" {...register("primaryInfo.debtAmount")} />
            </div>

            <div className="space-y-2">
              <Label>19. Исполнительные производства</Label>
              <Controller
                control={control}
                name="primaryInfo.hasExecutions"
                render={({ field }) => (
                  <SelectField
                    value={(field.value as string) ?? ""}
                    onChange={(value) => field.onChange(value)}
                    options={yesNoOptions}
                  />
                )}
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}
