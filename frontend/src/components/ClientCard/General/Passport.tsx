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
      <AccordionTrigger><h3 className="text-sm font-semibold">Паспорт</h3></AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.passportSeries">Серия паспорта</Label>
          <Input
            id="primaryInfo.passportSeries"
            placeholder="4016"
            maxLength={10}
            {...register("primaryInfo.passportSeries")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.passportNumber">Номер паспорта</Label>
          <Input
            id="primaryInfo.passportNumber"
            placeholder="123456"
            maxLength={20}
            {...register("primaryInfo.passportNumber")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.passportDepartmentCode">
            Код подразделения
          </Label>
          <Input
            id="primaryInfo.passportDepartmentCode"
            placeholder="780-089"
            maxLength={20}
            {...register("primaryInfo.passportDepartmentCode")}
          />
        </div>

        <div className="space-y-2 lg:col-span-3">
          <Label htmlFor="primaryInfo.passportIssuedBy">
            Кем выдан паспорт
          </Label>
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
