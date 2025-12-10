import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import { DatePickerInput } from "@/components/ui/DatePickerInput";
import { SelectField, SelectOption } from "@/components/shared/SelectFields";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

interface Props {
  register: any;
  useWatch: any;
  control: any;
}
export const MainInfo: FC<Props> = ({ register, useWatch, control }) => {
  const isLastNameChanged = useWatch({
    control,
    name: "basic_info.isLastNameChanged",
  }) as boolean | undefined;

  const yesNoOptions: SelectOption[] = [
    { value: true, label: "Да" },
    { value: false, label: "Нет" },
  ];

  const genderOptions: SelectOption[] = [
    { value: "male", label: "Мужской" },
    { value: "female", label: "Женский" },
  ];

  return (
    <AccordionItem value="mainInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Личные данные</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <p className="col-span-3 text-xs text-blue-500">
          В именительном падаже
        </p>
        <div className="space-y-1">
          <Label htmlFor="basic_info.lastName" required>
            Фамилия
          </Label>
          <Input
            id="basic_info.lastName"
            placeholder="Петров"
            {...register("basic_info.lastName")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.firstName" required>
            Имя
          </Label>
          <Input
            id="basic_info.firstName"
            placeholder="Александр"
            {...register("basic_info.firstName")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.middleName" required>
            Отчество
          </Label>
          <Input
            id="basic_info.middleName"
            placeholder="Сергеевич"
            {...register("basic_info.middleName")}
          />
        </div>
        <p className="col-span-3 text-xs text-blue-500">В родительном падеже</p>
        <div className="space-y-1">
          <Label htmlFor="basic_info.lastNameGenitive" required>
            Фамилия
          </Label>
          <Input
            id="basic_info.lastNameGenitive"
            placeholder="Петрова"
            {...register("basic_info.lastNameGenitive")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.firstNameGenitive" required>
            Имя
          </Label>
          <Input
            id="basic_info.firstNameGenitive"
            placeholder="Александра"
            {...register("basic_info.firstNameGenitive")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.middleNameGenitive" required>
            Отчество
          </Label>
          <Input
            id="basic_info.middleNameGenitive"
            placeholder="Сергеевича"
            {...register("basic_info.middleNameGenitive")}
          />
        </div>

        <div className="space-y-2">
          <Label>Изменялось ли ФИО</Label>
          <Controller
            control={control}
            name="basic_info.isLastNameChanged"
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
          <div className="space-y-1 lg:col-span-2">
            <Label htmlFor="basic_info.changedLastName">Предыдущее ФИО</Label>
            <Input
              id="basic_info.changedLastName"
              placeholder="Сидоров Иван Петрович"
              {...register("basic_info.changedLastName")}
            />
          </div>
        )}
        <Controller
          control={control}
          name="basic_info.gender"
          render={({ field }) => (
            <div className="space-y-2">
              <Label htmlFor="basic_info.gender">Пол</Label>
              <SelectField
                value={field.value}
                onChange={(value) => field.onChange(value)}
                options={genderOptions}
              />
            </div>
          )}
        />

        <Controller
          control={control}
          name="basic_info.birthDate"
          render={({ field }) => (
            <DatePickerInput
              id="basic_info.birthDate"
              name="basic_info.birthDate"
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

        <div className="space-y-1">
          <Label htmlFor="basic_info.birthPlace">Место рождения</Label>
          <Input
            id="basic_info.birthPlace"
            placeholder="г. Санкт-Петербург, Ленинградская область"
            {...register("basic_info.birthPlace")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.snils">СНИЛС</Label>
          <Input
            id="basic_info.snils"
            placeholder="12345678901"
            {...register("basic_info.snils")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
