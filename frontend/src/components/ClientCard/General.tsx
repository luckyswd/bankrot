import { useContext } from "react"
import React from "react"
import { Controller, useFieldArray, useFormContext, useWatch } from "react-hook-form"
import { Save, Plus, Trash2 } from "lucide-react"

import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { TabsContent } from "@/components/ui/tabs"
import { Separator } from "@/components/ui/separator"
import { ChildInfo, FormValues } from "./types"

export const SaveContext = React.createContext<(() => Promise<void>) | null>(null)
export const useSaveContract = () => useContext(SaveContext)

type SelectOption = { value: string | boolean; label: string }

interface SelectFieldProps {
  value: string | boolean | null | undefined
  onChange: (value: string | boolean | null) => void
  options: SelectOption[]
  placeholder?: string
}

const yesNoOptions: SelectOption[] = [
  { value: true, label: "Да" },
  { value: false, label: "Нет" },
]

const marriageOptions: SelectOption[] = [
  { value: "married", label: "Да" },
  { value: "single", label: "Нет" },
  { value: "married_3y_ago", label: "Нет, но состоял в течение 3 лет" },
]

const CLEAR_VALUE = "__clear__"

const SelectField = ({ value, onChange, options, placeholder = "Не указано" }: SelectFieldProps) => {
  const stringValue = value === undefined || value === null ? undefined : String(value)
  const optionValueMap = options.reduce<Record<string, string | boolean>>((acc, option) => {
    acc[String(option.value)] = option.value
    return acc
  }, {})

  return (
    <Select
      value={stringValue || CLEAR_VALUE}
      onValueChange={(selected) => {
        if (selected === CLEAR_VALUE) {
          onChange(null)
          return
        }
        if (Object.prototype.hasOwnProperty.call(optionValueMap, selected)) {
          onChange(optionValueMap[selected])
          return
        }
        onChange(selected)
      }}
    >
      <SelectTrigger>
        <SelectValue placeholder={placeholder} />
      </SelectTrigger>
      <SelectContent>
        <SelectItem value={CLEAR_VALUE}>Не указано</SelectItem>
        {options.map((option) => (
          <SelectItem key={String(option.value)} value={String(option.value)}>
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}

const createEmptyChild = (): ChildInfo => ({
  firstName: "",
  lastName: "",
  middleName: null,
  isLastNameChanged: false,
  changedLastName: null,
  birthDate: "",
})

export const GeneralTab = () => {
  const { register, control, formState: { isDirty }, watch } = useFormContext<FormValues>()
  const saveContract = useSaveContract()

  const isLastNameChanged = useWatch({
    control,
    name: "primaryInfo.isLastNameChanged",
  }) as boolean | undefined

  const maritalStatus = useWatch({
    control,
    name: "primaryInfo.maritalStatus",
  }) as string | undefined
  const shouldShowSpouseFields = maritalStatus === "married" || maritalStatus === "married_3y_ago"

  const hasMinorChildren = useWatch({
    control,
    name: "primaryInfo.hasMinorChildren",
  }) as boolean | undefined

  // Управление списком детей
  const { fields, append, remove } = useFieldArray<FormValues, "primaryInfo.children">({
    control,
    name: "primaryInfo.children",
  })

  // Отслеживаем изменения для всех детей сразу
  const childrenValues = watch("primaryInfo.children") ?? []

  const handleSave = async () => {
    if (saveContract) {
      await saveContract()
    }
  }

  return (
    <TabsContent value="primary" className="space-y-6">
      <Card>
        <CardHeader>
          <div>
            <CardTitle>Основная информация</CardTitle>
            <CardDescription>Заполните все необходимые поля</CardDescription>
          </div>
        </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {/* ФИО и личные данные */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Личные данные</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.lastName">Фамилия *</Label>
                    <Input id="primaryInfo.lastName" placeholder="Иванов" {...register("primaryInfo.lastName")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.firstName">Имя *</Label>
                    <Input id="primaryInfo.firstName" placeholder="Иван" {...register("primaryInfo.firstName")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.middleName">Отчество</Label>
                    <Input id="primaryInfo.middleName" placeholder="Иванович" {...register("primaryInfo.middleName")} />
                  </div>

                  <div className="space-y-2">
                    <Label>Изменялось ли ФИО</Label>
                    <Controller
                      control={control}
                      name="primaryInfo.isLastNameChanged"
                      render={({ field }) => (
                        <SelectField
                          value={field.value}
                          onChange={(value) => field.onChange(value)}
                          options={yesNoOptions}
                        />
                      )}
                    />
                  </div>

                  {isLastNameChanged && (
                    <div className="space-y-2 lg:col-span-2">
                      <Label htmlFor="primaryInfo.changedLastName">Предыдущее ФИО</Label>
                      <Input id="primaryInfo.changedLastName" placeholder="Петров Петр Петрович" {...register("primaryInfo.changedLastName")} />
                    </div>
                  )}

                  <Controller
                    control={control}
                    name="primaryInfo.birthDate"
                    render={({ field }) => (
                      <DatePickerInput
                        label="Дата рождения *"
                        value={field.value ? (typeof field.value === 'string' ? field.value : (field.value as any)?.toString()) : ""}
                        onChange={field.onChange}
                      />
                    )}
                  />

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.birthPlace">Место рождения</Label>
                    <Input id="primaryInfo.birthPlace" placeholder="г. Москва" {...register("primaryInfo.birthPlace")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.snils">СНИЛС</Label>
                    <Input id="primaryInfo.snils" placeholder="123-456-789 00" {...register("primaryInfo.snils")} />
                  </div>
                </div>
              </div>

              <Separator />

              {/* Адрес регистрации */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Адрес регистрации</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationRegion">Субъект РФ (регион)</Label>
                    <Input id="primaryInfo.registrationRegion" placeholder="Санкт-Петербург" {...register("primaryInfo.registrationRegion")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationDistrict">Район</Label>
                    <Input id="primaryInfo.registrationDistrict" placeholder="Московский" {...register("primaryInfo.registrationDistrict")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationCity">Город</Label>
                    <Input id="primaryInfo.registrationCity" placeholder="Санкт-Петербург" {...register("primaryInfo.registrationCity")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationSettlement">Населенный пункт</Label>
                    <Input id="primaryInfo.registrationSettlement" placeholder="пос. Ленинский" {...register("primaryInfo.registrationSettlement")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationStreet">Улица</Label>
                    <Input id="primaryInfo.registrationStreet" placeholder="Смоленская" {...register("primaryInfo.registrationStreet")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationHouse">Дом</Label>
                    <Input id="primaryInfo.registrationHouse" placeholder="9" {...register("primaryInfo.registrationHouse")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationBuilding">Корпус</Label>
                    <Input id="primaryInfo.registrationBuilding" placeholder="1" {...register("primaryInfo.registrationBuilding")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.registrationApartment">Квартира</Label>
                    <Input id="primaryInfo.registrationApartment" placeholder="418" {...register("primaryInfo.registrationApartment")} />
                  </div>
                </div>
              </div>

              <Separator />

              {/* Паспорт */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Паспорт</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.passportSeries">Серия паспорта</Label>
                    <Input id="primaryInfo.passportSeries" placeholder="4016" maxLength={10} {...register("primaryInfo.passportSeries")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.passportNumber">Номер паспорта</Label>
                    <Input id="primaryInfo.passportNumber" placeholder="123456" maxLength={20} {...register("primaryInfo.passportNumber")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.passportDepartmentCode">Код подразделения</Label>
                    <Input id="primaryInfo.passportDepartmentCode" placeholder="780-089" maxLength={20} {...register("primaryInfo.passportDepartmentCode")} />
                  </div>

                  <div className="space-y-2 lg:col-span-3">
                    <Label htmlFor="primaryInfo.passportIssuedBy">Кем выдан паспорт</Label>
                    <Input
                      id="primaryInfo.passportIssuedBy"
                      placeholder="ОУФМС России по СПб и ЛО в Московском районе"
                      {...register("primaryInfo.passportIssuedBy")}
                    />
                  </div>

                  <Controller
                    control={control}
                    name="primaryInfo.passportIssuedDate"
                    render={({ field }) => (
                      <DatePickerInput
                        label="Дата выдачи паспорта"
                        value={field.value ? (typeof field.value === 'string' ? field.value : (field.value as any)?.toString()) : ""}
                        onChange={field.onChange}
                      />
                    )}
                  />
                </div>
              </div>

              <Separator />

              {/* Семейное положение */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Семейное положение</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label>Семейное положение</Label>
                    <Controller
                      control={control}
                      name="primaryInfo.maritalStatus"
                      render={({ field }) => (
                        <SelectField
                          value={field.value}
                          onChange={(value) => field.onChange(value)}
                          options={marriageOptions}
                        />
                      )}
                    />
                  </div>

                  {shouldShowSpouseFields && (
                    <>
                      <div className="space-y-2">
                        <Label htmlFor="primaryInfo.spouseFullName">ФИО супруга</Label>
                        <Input id="primaryInfo.spouseFullName" placeholder="Иванова Мария Петровна" {...register("primaryInfo.spouseFullName")} />
                      </div>

                      <Controller
                        control={control}
                        name="primaryInfo.spouseBirthDate"
                        render={({ field }) => (
                          <DatePickerInput
                            label="Дата рождения супруга"
                            value={field.value ? (typeof field.value === 'string' ? field.value : (field.value as any)?.toString()) : ""}
                            onChange={field.onChange}
                          />
                        )}
                      />
                    </>
                  )}

                  <div className="space-y-2">
                    <Label>Наличие несовершеннолетних детей</Label>
                    <Controller
                      control={control}
                      name="primaryInfo.hasMinorChildren"
                      render={({ field }) => (
                        <SelectField
                          value={field.value}
                          onChange={(value) => field.onChange(value)}
                          options={yesNoOptions}
                        />
                      )}
                    />
                  </div>
                </div>

                {/* Список детей */}
                {hasMinorChildren === true && (
                  <div className="mt-6 space-y-4">
                    <div className="flex items-center justify-between">
                      <h4 className="text-sm font-medium">Список несовершеннолетних детей</h4>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => append(createEmptyChild())}
                      >
                        <Plus className="h-4 w-4 mr-2" />
                        Добавить ребенка
                      </Button>
                    </div>

                    {fields.length === 0 && (
                      <div className="text-sm text-muted-foreground text-center py-4">
                        Список пуст. Нажмите "Добавить ребенка" для добавления.
                      </div>
                    )}

                    {fields.map((field, index) => {
                      const childIsLastNameChanged = childrenValues[index]?.isLastNameChanged

                      return (
                        <Card key={field.id} className="p-4">
                          <div className="flex items-center justify-between mb-4">
                            <h5 className="text-sm font-medium">Ребенок {index + 1}</h5>
                            <Button
                              type="button"
                              variant="ghost"
                              size="sm"
                              onClick={() => remove(index)}
                            >
                              <Trash2 className="h-4 w-4 text-destructive" />
                            </Button>
                          </div>

                          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div className="space-y-2">
                              <Label htmlFor={`primaryInfo.children.${index}.lastName`}>Фамилия *</Label>
                              <Input
                                id={`primaryInfo.children.${index}.lastName`}
                                placeholder="Иванов"
                                {...register(`primaryInfo.children.${index}.lastName`)}
                              />
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor={`primaryInfo.children.${index}.firstName`}>Имя *</Label>
                              <Input
                                id={`primaryInfo.children.${index}.firstName`}
                                placeholder="Александр"
                                {...register(`primaryInfo.children.${index}.firstName`)}
                              />
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor={`primaryInfo.children.${index}.middleName`}>Отчество</Label>
                              <Input
                                id={`primaryInfo.children.${index}.middleName`}
                                placeholder="Иванович"
                                {...register(`primaryInfo.children.${index}.middleName`)}
                              />
                            </div>

                            <div className="space-y-2">
                              <Label>Изменялось ли ФИО</Label>
                              <Controller
                                control={control}
                                name={`primaryInfo.children.${index}.isLastNameChanged`}
                                render={({ field }) => (
                                  <SelectField
                                    value={field.value}
                                    onChange={(value) => field.onChange(value)}
                                    options={yesNoOptions}
                                  />
                                )}
                              />
                            </div>

                            {childIsLastNameChanged === true && (
                              <div className="space-y-2 lg:col-span-2">
                                <Label htmlFor={`primaryInfo.children.${index}.changedLastName`}>Предыдущее ФИО</Label>
                                <Input
                                  id={`primaryInfo.children.${index}.changedLastName`}
                                  placeholder="Петров Петр Петрович"
                                  {...register(`primaryInfo.children.${index}.changedLastName`)}
                                />
                              </div>
                            )}

                            <Controller
                              control={control}
                              name={`primaryInfo.children.${index}.birthDate`}
                              render={({ field }) => (
                                <DatePickerInput
                                  label="Дата рождения *"
                                  value={field.value ? (typeof field.value === 'string' ? field.value : (field.value as any)?.toString()) : ""}
                                  onChange={field.onChange}
                                />
                              )}
                            />
                          </div>
                        </Card>
                      )
                    })}
                  </div>
                )}
              </div>

              <Separator />

              {/* Работа и образование */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Работа и образование</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label>Является ли студентом</Label>
                    <Controller
                      control={control}
                      name="primaryInfo.isStudent"
                      render={({ field }) => (
                        <SelectField
                          value={field.value}
                          onChange={(value) => field.onChange(value)}
                          options={yesNoOptions}
                        />
                      )}
                    />
                  </div>

                  <div className="space-y-2 lg:col-span-2">
                    <Label htmlFor="primaryInfo.employerName">Наименование работодателя</Label>
                    <Input id="primaryInfo.employerName" placeholder='ООО "Рога и Копыта"' {...register("primaryInfo.employerName")} />
                  </div>

                  <div className="space-y-2 lg:col-span-2">
                    <Label htmlFor="primaryInfo.employerAddress">Адрес работодателя</Label>
                    <Input id="primaryInfo.employerAddress" placeholder="г. Москва, ул. Ленина, д. 1" {...register("primaryInfo.employerAddress")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.employerInn">ИНН работодателя</Label>
                    <Input id="primaryInfo.employerInn" placeholder="1234567890" maxLength={12} {...register("primaryInfo.employerInn")} />
                  </div>

                  <div className="space-y-2 lg:col-span-3">
                    <Label htmlFor="primaryInfo.socialBenefits">Пенсии и социальные выплаты</Label>
                    <Input
                      id="primaryInfo.socialBenefits"
                      placeholder="Алименты, пособие, ЕДВ, прочее"
                      {...register("primaryInfo.socialBenefits")}
                    />
                  </div>
                </div>
              </div>

              <Separator />

              {/* Контакты */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Контакты</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.phone">Телефон</Label>
                    <Input id="primaryInfo.phone" type="tel" placeholder="+7 (999) 123-45-67" {...register("primaryInfo.phone")} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.email">Электронная почта</Label>
                    <Input id="primaryInfo.email" type="email" placeholder="example@mail.ru" {...register("primaryInfo.email")} />
                  </div>

                  <div className="space-y-2 lg:col-span-3">
                    <Label htmlFor="primaryInfo.mailingAddress">Адрес для направления корреспонденции</Label>
                    <Input
                      id="primaryInfo.mailingAddress"
                      placeholder="196084, г. Санкт-Петербург, ул. Смоленская, 9-418"
                      {...register("primaryInfo.mailingAddress")}
                    />
                  </div>
                </div>
              </div>

              <Separator />

              {/* Долг и исполнительные производства */}
              <div>
                <h3 className="text-sm font-semibold mb-4">Долг и исполнительные производства</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="primaryInfo.debtAmount">Сумма долга</Label>
                    <Input id="primaryInfo.debtAmount" type="number" step="0.01" placeholder="1500000.50" {...register("primaryInfo.debtAmount")} />
                  </div>

                  <div className="space-y-2">
                    <Label>Наличие возбужденных исполнительных производств</Label>
                    <Controller
                      control={control}
                      name="primaryInfo.hasEnforcementProceedings"
                      render={({ field }) => (
                        <SelectField
                          value={field.value}
                          onChange={(value) => field.onChange(value)}
                          options={yesNoOptions}
                        />
                      )}
                    />
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
    </TabsContent>
  )
}
