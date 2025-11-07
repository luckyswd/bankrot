import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { TabsContent } from "@/components/ui/tabs"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { DatePickerInput } from "@/components/ui/DatePickerInput"

type FieldAccessor = (path: string) => unknown
type FieldUpdater = (path: string, value: unknown) => void

interface GeneralTabProps {
  getValue: FieldAccessor
  handleChange: FieldUpdater
}

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
  { value: "yes", label: "Да (нет, но состоял в течение 3 лет)" },
  { value: "no", label: "Нет (состоял в течении 3 лет)" },
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

export const GeneralTab = ({ handleChange, getValue }: GeneralTabProps) => {
  const getStringValue = (path: string) => (getValue(path) as string) || ""

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
              <Label>1. ФИО *</Label>
              <Input
                placeholder="Фамилия Имя Отчество"
                value={getStringValue("primaryInfo.fullName")}
                onChange={(e) => handleChange("primaryInfo.fullName", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>2. Изменялось ли ФИО</Label>
              <SelectField
                value={getStringValue("primaryInfo.nameChanged")}
                onChange={(value) => handleChange("primaryInfo.nameChanged", value)}
                options={yesNoOptions}
              />
            </div>

            <DatePickerInput
              label="3. Дата рождения *"
              value={getStringValue("primaryInfo.birthDate")}
              onChange={(next) => handleChange("primaryInfo.birthDate", next)}
            />

            <div className="space-y-2">
              <Label>4. Место рождения</Label>
              <Input
                placeholder="Город, страна"
                value={getStringValue("primaryInfo.birthPlace")}
                onChange={(e) => handleChange("primaryInfo.birthPlace", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>5. СНИЛС</Label>
              <Input
                placeholder="123-456-789 00"
                value={getStringValue("primaryInfo.snils")}
                onChange={(e) => handleChange("primaryInfo.snils", e.target.value)}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label>6. Адрес регистрации</Label>
              <Input
                placeholder="Субъект РФ, район, город, населенный пункт, улица, дом, корпус, квартира"
                value={getStringValue("primaryInfo.registrationAddress")}
                onChange={(e) => handleChange("primaryInfo.registrationAddress", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>7. Паспорт (серия и номер)</Label>
              <Input
                placeholder="1234 567890"
                value={getStringValue("primaryInfo.passport")}
                onChange={(e) => handleChange("primaryInfo.passport", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>8. Состоит в браке</Label>
              <SelectField
                value={getStringValue("primaryInfo.married")}
                onChange={(value) => handleChange("primaryInfo.married", value)}
                options={marriageOptions}
              />
            </div>

            <div className="space-y-2">
              <Label>9. Супруг(а) (опционально)</Label>
              <Input
                placeholder="ФИО супруга/супруги"
                value={getStringValue("primaryInfo.spouseName")}
                onChange={(e) => handleChange("primaryInfo.spouseName", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>10. Несовершеннолетние дети</Label>
              <SelectField
                value={getStringValue("primaryInfo.hasMinorChildren")}
                onChange={(value) => handleChange("primaryInfo.hasMinorChildren", value)}
                options={yesNoOptions}
              />
            </div>

            <div className="space-y-2">
              <Label>11. ФИО (ребёнка)</Label>
              <Input
                placeholder="ФИО ребенка"
                value={getStringValue("primaryInfo.childName")}
                onChange={(e) => handleChange("primaryInfo.childName", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>12. Студент</Label>
              <SelectField
                value={getStringValue("primaryInfo.isStudent")}
                onChange={(value) => handleChange("primaryInfo.isStudent", value)}
                options={yesNoOptions}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label>13. Работа</Label>
              <Input
                placeholder="Наименование, адрес, ИНН..."
                value={getStringValue("primaryInfo.work")}
                onChange={(e) => handleChange("primaryInfo.work", e.target.value)}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label>14. Пенсии и соц.выплаты</Label>
              <Input
                placeholder="Алименты, пособие, ЕДВ, прочее"
                value={getStringValue("primaryInfo.socialPayments")}
                onChange={(e) => handleChange("primaryInfo.socialPayments", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>15. Телефон</Label>
              <Input
                type="tel"
                placeholder="+7 (999) 123-45-67"
                value={getStringValue("primaryInfo.phone")}
                onChange={(e) => handleChange("primaryInfo.phone", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>16. Электронная почта</Label>
              <Input
                type="email"
                placeholder="email@example.com"
                value={getStringValue("primaryInfo.email")}
                onChange={(e) => handleChange("primaryInfo.email", e.target.value)}
              />
            </div>

            <div className="space-y-2 lg:col-span-3">
              <Label>17. Адрес для направления корреспонденции</Label>
              <Input
                placeholder="196084, г. Санкт-Петербург, ул. Смоленская, 9-418"
                value={getStringValue("primaryInfo.correspondenceAddress")}
                onChange={(e) => handleChange("primaryInfo.correspondenceAddress", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>18. Сумма долга</Label>
              <Input
                type="number"
                placeholder="в руб"
                value={getStringValue("primaryInfo.debtAmount")}
                onChange={(e) => handleChange("primaryInfo.debtAmount", e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label>19. Исполнительные производства</Label>
              <SelectField
                value={getStringValue("primaryInfo.hasExecutions")}
                onChange={(value) => handleChange("primaryInfo.hasExecutions", value)}
                options={yesNoOptions}
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}
