import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import { DatePickerInput } from "@/components/ui/DatePickerInput";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
interface Props {
  register: any;
  control: any;
}
export const PassportInfo: FC<Props> = ({ register, control }) => {
  return (
    <AccordionItem value="passportInfo">
      <AccordionTrigger><h3 className="text-xl font-semibold">Паспорт</h3></AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-1">
          <Label htmlFor="basic_info.passportSeries">Серия паспорта</Label>
          <Input
            id="basic_info.passportSeries"
            placeholder="4512"
            maxLength={10}
            {...register("basic_info.passportSeries")}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: 4512</p>
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.passportNumber">Номер паспорта</Label>
          <Input
            id="basic_info.passportNumber"
            placeholder="878508"
            maxLength={20}
            {...register("basic_info.passportNumber")}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: 878508</p>
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.passportDepartmentCode">
            Код подразделения
          </Label>
          <Input
            id="basic_info.passportDepartmentCode"
            placeholder="780-001"
            maxLength={20}
            {...register("basic_info.passportDepartmentCode")}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: 780-001</p>
        </div>

        <div className="space-y-1 lg:col-span-3">
          <Label htmlFor="basic_info.passportIssuedBy">
            Кем выдан паспорт
          </Label>
          <Input
            id="basic_info.passportIssuedBy"
            placeholder="Отделом УФМС России по Санкт-Петербургу и Ленинградской области в Центральном районе"
            {...register("basic_info.passportIssuedBy")}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: Отделом УФМС России по Санкт-Петербургу и Ленинградской области в Центральном районе</p>
        </div>

        <Controller
          control={control}
          name="basic_info.passportIssuedDate"
          render={({ field }) => (
            <DatePickerInput
              id="basic_info.passportIssuedDate"
              name="basic_info.passportIssuedDate"
              label="Дата выдачи паспорта"
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
      </AccordionContent>
    </AccordionItem>
  );
};
