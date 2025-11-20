import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { SelectField, SelectOption } from "@/components/shared/SelectFields";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion"

interface Props {
  register: any;
  useWatch: any;
  control: any;
}
export const MainInfo: FC<Props> = ({ register, useWatch, control }) => {
  const isLastNameChanged = useWatch({
    control,
    name: "primaryInfo.isLastNameChanged",
  }) as boolean | undefined;

  const yesNoOptions: SelectOption[] = [
    { value: true, label: "Да" },
    { value: false, label: "Нет" },
  ]
  
  return (
    <AccordionItem value="mainInfo">
      <AccordionTrigger><h3 className="text-xl font-semibold">Личные данные</h3></AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.lastName">Фамилия *</Label>
          <Input
            id="primaryInfo.lastName"
            placeholder="Иванов"
            {...register("primaryInfo.lastName")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.firstName">Имя *</Label>
          <Input
            id="primaryInfo.firstName"
            placeholder="Иван"
            {...register("primaryInfo.firstName")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.middleName">Отчество</Label>
          <Input
            id="primaryInfo.middleName"
            placeholder="Иванович"
            {...register("primaryInfo.middleName")}
          />
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
            <Input
              id="primaryInfo.changedLastName"
              placeholder="Петров Петр Петрович"
              {...register("primaryInfo.changedLastName")}
            />
          </div>
        )}

        <Controller
          control={control}
          name="primaryInfo.birthDate"
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

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.birthPlace">Место рождения</Label>
          <Input
            id="primaryInfo.birthPlace"
            placeholder="г. Москва"
            {...register("primaryInfo.birthPlace")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.snils">СНИЛС</Label>
          <Input
            id="primaryInfo.snils"
            placeholder="123-456-789 00"
            {...register("primaryInfo.snils")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
