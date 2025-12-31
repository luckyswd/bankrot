import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC, useMemo, useEffect, useRef } from "react";
import { DatePickerInput } from "@/components/ui/DatePickerInput";
import { SelectField, SelectOption } from "@/components/shared/SelectFields";
import { Controller, useFieldArray } from "react-hook-form"
import { Plus, Trash2 } from "lucide-react"
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { ChildInfo, FormValues } from "../types";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { notify } from "@/components/ui/toast";
interface Props {
  register: any;
  control: any;
  useWatch: any;
  watch: any;
}
export const FamilyInfo: FC<Props> = ({
  register,
  control,
  useWatch,
  watch,
}) => {
  const maritalStatus = useWatch({
    control,
    name: "basic_info.maritalStatus",
  }) as string | undefined;

  const shouldShowSpouseFields =
    maritalStatus === "married" || maritalStatus === "married_3y_ago";

  const isDivorcedWithin3Years = maritalStatus === "married_3y_ago";

  const hasMinorChildren = useWatch({
    control,
    name: "basic_info.hasMinorChildren",
  }) as boolean | undefined;


  const yesNoOptions: SelectOption[] = [
  { value: true, label: "Да" },
  { value: false, label: "Нет" },
]

  // Управление списком детей
  const { fields, append, remove } = useFieldArray<
    FormValues,
    "basic_info.children"
  >({
    control,
    name: "basic_info.children",
  });


  const marriageOptions: SelectOption[] = [
    { value: "married", label: "состоит в браке" },
    { value: "single", label: "не состоит в браке" },
    { value: "married_3y_ago", label: "состоял в течение 3 лет" },
  ]
  
  
  const createEmptyChild = (): ChildInfo => ({
    firstName: "",
    lastName: "",
    middleName: null,
    birthDate: "",
  })

  // Отслеживаем изменения для всех детей сразу
  const childrenValues = watch("basic_info.children") ?? [];

  // Вычисляет количество полных лет на основе даты рождения
  const calculateFullAge = (birthDate: string): number | null => {
    if (!birthDate) {
      return null
    }

    try {
      const birth = new Date(birthDate)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      birth.setHours(0, 0, 0, 0)

      // Если дата рождения в будущем, возвращаем null
      if (birth > today) {
        return null
      }

      const age = today.getFullYear() - birth.getFullYear()
      const monthDiff = today.getMonth() - birth.getMonth()

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        return age - 1
      }

      return age
    } catch (error) {
      return null
    }
  }

  // Вычисляем возраст для каждого ребенка
  const childrenAges = useMemo(() => {
    return childrenValues.map((child: ChildInfo) => {
      if (!child.birthDate) {
        return null
      }
      return calculateFullAge(child.birthDate)
    })
  }, [childrenValues])

  // Функция для правильного склонения возраста
  const getAgeText = (age: number): string => {
    const lastDigit = age % 10
    const lastTwoDigits = age % 100

    // Исключения для 11-14
    if (lastTwoDigits >= 11 && lastTwoDigits <= 14) {
      return `${age} лет`
    }

    // 1, 21, 31, 41... год
    if (lastDigit === 1) {
      return `${age} год`
    }

    // 2, 3, 4, 22, 23, 24... года
    if (lastDigit >= 2 && lastDigit <= 4) {
      return `${age} года`
    }

    // Остальные - лет
    return `${age} лет`
  }

  // Отслеживаем уже показанные уведомления, чтобы не показывать повторно
  const shownNotificationsRef = useRef<Set<string>>(new Set())

  // Проверяет детей на возраст >= 18 и показывает уведомления
  const checkChildrenAge = (children: ChildInfo[], ages: (number | null)[]) => {
    children.forEach((child, index) => {
      const age = ages[index]
      if (age !== null && age >= 18) {
        const childName = [child?.lastName, child?.firstName, child?.middleName]
          .filter(Boolean)
          .join(' ') || 'Ребенок'
        
        // Создаем уникальный ключ для уведомления
        const notificationKey = `${child.birthDate}-${index}`
        
        // Показываем уведомление только если еще не показывали
        if (!shownNotificationsRef.current.has(notificationKey)) {
          shownNotificationsRef.current.add(notificationKey)
          notify({
            message: `Внимание! ${childName} достиг(ла) возраста ${getAgeText(age)}. Пожалуйста, проверьте данные.`,
            type: 'error',
            duration: 8000,
          })
        }
      }
    })
  }

  // Проверяем возраст при загрузке/изменении данных
  useEffect(() => {
    if (childrenValues.length > 0 && childrenAges.length > 0) {
      checkChildrenAge(childrenValues, childrenAges)
    }
  }, [childrenValues, childrenAges])

  // Проверяем возраст при изменении даты рождения и показываем уведомление
  const handleBirthDateChange = (index: number, value: string) => {
    const age = calculateFullAge(value)
    
    if (age !== null && age >= 18) {
      const child = childrenValues[index]
      const childName = [child?.lastName, child?.firstName, child?.middleName]
        .filter(Boolean)
        .join(' ') || 'Ребенок'
      
      // Сбрасываем ключ для этого ребенка, чтобы показать уведомление снова
      const notificationKey = `${value}-${index}`
      shownNotificationsRef.current.delete(notificationKey)
      
      notify({
        message: `Внимание! ${childName} достиг(ла) возраста ${getAgeText(age)}. Пожалуйста, проверьте данные.`,
        type: 'error',
        duration: 8000,
      })
    }
  }

  return (
    <AccordionItem value="familyInfo">
      <AccordionTrigger><h3 className="text-xl font-semibold">Семейное положение</h3></AccordionTrigger>
      <AccordionContent>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-2">
          <Label htmlFor="basic_info.maritalStatus">Семейное положение</Label>
          <Controller
            control={control}
            name="basic_info.maritalStatus"
            render={({ field }) => (
              <SelectField
                id="basic_info.maritalStatus"
                value={field.value}
                onChange={(value) => field.onChange(value)}
                options={marriageOptions}
              />
            )}
          />
        </div>

        {shouldShowSpouseFields && (
          <>
            <div className="space-y-1">
              <Label htmlFor="basic_info.spouseFullName">ФИО супруга</Label>
              <Input
                id="basic_info.spouseFullName"
                placeholder="Петрова Елена Сергеевна"
                {...register("basic_info.spouseFullName")}
              />
            </div>

            <Controller
              control={control}
              name="basic_info.spouseBirthDate"
              render={({ field }) => (
                <DatePickerInput
                  id="basic_info.spouseBirthDate"
                  name="basic_info.spouseBirthDate"
                  label="Дата рождения супруга"
                  value={
                    field.value
                      ? typeof field.value === "string"
                        ? field.value
                        : (field.value as any)?.toString()
                      : ""
                  }
                  onChange={field.onChange}
                />
              )}
            />
            {isDivorcedWithin3Years && (
              <Controller
                control={control}
                name="basic_info.marriageTerminationDate"
                render={({ field }) => (
                  <DatePickerInput
                    id="basic_info.marriageTerminationDate"
                    name="basic_info.marriageTerminationDate"
                    label="Дата расторжения брака"
                    value={
                      field.value
                        ? typeof field.value === "string"
                          ? field.value
                          : (field.value as any)?.toString()
                        : ""
                    }
                    onChange={field.onChange}
                  />
                )}
              />
            )}
          </>
        )}

        <div className="space-y-2">
          <Label htmlFor="basic_info.hasMinorChildren">Наличие несовершеннолетних детей</Label>
          <Controller
            control={control}
            name="basic_info.hasMinorChildren"
            render={({ field }) => (
              <SelectField
                id="basic_info.hasMinorChildren"
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
            <h4 className="text-sm font-medium">
              Список несовершеннолетних детей
            </h4>
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
            return (
              <Card key={field.id} className="p-4">
                <div className="flex items-center justify-between mb-4">
                  <h5 className="text-sm font-medium">Ребенок {index + 1}</h5>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="text-red-400"
                    onClick={() => remove(index)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div className="space-y-1">
                    <Label htmlFor={`basic_info.children.${index}.lastName`}>
                      Фамилия *
                    </Label>
                    <Input
                      id={`basic_info.children.${index}.lastName`}
                      placeholder="Петров"
                      {...register(`basic_info.children.${index}.lastName`)}
                    />
                  </div>

                  <div className="space-y-1">
                    <Label htmlFor={`basic_info.children.${index}.firstName`}>
                      Имя *
                    </Label>
                    <Input
                      id={`basic_info.children.${index}.firstName`}
                      placeholder="Дмитрий"
                      {...register(`basic_info.children.${index}.firstName`)}
                    />
                  </div>

                  <div className="space-y-1">
                    <Label htmlFor={`basic_info.children.${index}.middleName`}>
                      Отчество
                    </Label>
                    <Input
                      id={`basic_info.children.${index}.middleName`}
                      placeholder="Александрович"
                      {...register(`basic_info.children.${index}.middleName`)}
                    />
                  </div>

                  <Controller
                    control={control}
                    name={`basic_info.children.${index}.birthDate`}
                    render={({ field }) => (
                      <DatePickerInput
                        id={`basic_info.children.${index}.birthDate`}
                        name={`basic_info.children.${index}.birthDate`}
                        label="Дата рождения *"
                        value={
                          field.value
                            ? typeof field.value === "string"
                              ? field.value
                              : (field.value as any)?.toString()
                            : ""
                        }
                        onChange={(value) => {
                          field.onChange(value)
                          handleBirthDateChange(index, value)
                        }}
                      />
                    )}
                  />

                  <div className="space-y-1">
                    <Label htmlFor={`basic_info.children.${index}.fullAge`}>
                      Количество полных лет
                    </Label>
                    <Input
                      id={`basic_info.children.${index}.fullAge`}
                      value={childrenAges[index] !== null ? childrenAges[index] : ""}
                      readOnly
                      className="bg-muted cursor-not-allowed"
                      placeholder="Вычисляется автоматически"
                    />
                  </div>
                </div>
              </Card>
            );
          })}
        </div>
      )}
      </AccordionContent>
    </AccordionItem>
  );
};
