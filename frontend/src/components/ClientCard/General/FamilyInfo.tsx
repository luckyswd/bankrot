import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
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
    name: "primaryInfo.maritalStatus",
  }) as string | undefined;

  const shouldShowSpouseFields =
    maritalStatus === "married" || maritalStatus === "married_3y_ago";

  const isDivorcedWithin3Years = maritalStatus === "married_3y_ago";

  const hasMinorChildren = useWatch({
    control,
    name: "primaryInfo.hasMinorChildren",
  }) as boolean | undefined;


  const yesNoOptions: SelectOption[] = [
  { value: true, label: "Да" },
  { value: false, label: "Нет" },
]

  // Управление списком детей
  const { fields, append, remove } = useFieldArray<
    FormValues,
    "primaryInfo.children"
  >({
    control,
    name: "primaryInfo.children",
  });


  const marriageOptions: SelectOption[] = [
    { value: "married", label: "Да" },
    { value: "single", label: "Нет" },
    { value: "married_3y_ago", label: "Нет, но состоял в течение 3 лет" },
  ]
  
  
  const createEmptyChild = (): ChildInfo => ({
    firstName: "",
    lastName: "",
    middleName: null,
    isLastNameChanged: false,
    changedLastName: null,
    birthDate: "",
  })

  // Отслеживаем изменения для всех детей сразу
  const childrenValues = watch("primaryInfo.children") ?? [];

  return (
    <AccordionItem value="familyInfo">
      <AccordionTrigger><h3 className="text-xl font-semibold">Семейное положение</h3></AccordionTrigger>
      <AccordionContent>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
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
              <Input
                id="primaryInfo.spouseFullName"
                placeholder="Иванова Мария Петровна"
                {...register("primaryInfo.spouseFullName")}
              />
            </div>

            <Controller
              control={control}
              name="primaryInfo.spouseBirthDate"
              render={({ field }) => (
                <DatePickerInput
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
                name="primaryInfo.marriageTerminationDate"
                render={({ field }) => (
                  <DatePickerInput
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
            const childIsLastNameChanged =
              childrenValues[index]?.isLastNameChanged;

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
                    <Label htmlFor={`primaryInfo.children.${index}.lastName`}>
                      Фамилия *
                    </Label>
                    <Input
                      id={`primaryInfo.children.${index}.lastName`}
                      placeholder="Иванов"
                      {...register(`primaryInfo.children.${index}.lastName`)}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor={`primaryInfo.children.${index}.firstName`}>
                      Имя *
                    </Label>
                    <Input
                      id={`primaryInfo.children.${index}.firstName`}
                      placeholder="Александр"
                      {...register(`primaryInfo.children.${index}.firstName`)}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor={`primaryInfo.children.${index}.middleName`}>
                      Отчество
                    </Label>
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
                      <Label
                        htmlFor={`primaryInfo.children.${index}.changedLastName`}
                      >
                        Предыдущее ФИО
                      </Label>
                      <Input
                        id={`primaryInfo.children.${index}.changedLastName`}
                        placeholder="Петров Петр Петрович"
                        {...register(
                          `primaryInfo.children.${index}.changedLastName`
                        )}
                      />
                    </div>
                  )}

                  <Controller
                    control={control}
                    name={`primaryInfo.children.${index}.birthDate`}
                    render={({ field }) => (
                      <DatePickerInput
                        label="Дата рождения *"
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
