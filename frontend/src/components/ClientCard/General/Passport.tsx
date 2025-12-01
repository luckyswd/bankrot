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
        <div className="space-y-2">
          <Label htmlFor="basic_info.passportSeries">Серия паспорта</Label>
          <Input
            id="basic_info.passportSeries"
            placeholder="4016"
            maxLength={10}
            {...register("basic_info.passportSeries")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="basic_info.passportNumber">Номер паспорта</Label>
          <Input
            id="basic_info.passportNumber"
            placeholder="123456"
            maxLength={20}
            {...register("basic_info.passportNumber")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="basic_info.passportDepartmentCode">
            Код подразделения
          </Label>
          <Input
            id="basic_info.passportDepartmentCode"
            placeholder="780-089"
            maxLength={20}
            {...register("basic_info.passportDepartmentCode")}
          />
        </div>

        <div className="space-y-2 lg:col-span-3">
          <Label htmlFor="basic_info.passportIssuedBy">
            Кем выдан паспорт
          </Label>
          <Input
            id="basic_info.passportIssuedBy"
            placeholder="ОУФМС России по СПб и ЛО в Московском районе"
            {...register("basic_info.passportIssuedBy")}
          />
        </div>

        <Controller
          control={control}
          name="basic_info.passportIssuedDate"
          render={({ field }) => (
            <DatePickerInput
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
