import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import { SelectField, SelectOption } from "@/components/shared/SelectFields";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
interface Props {
  register: any;
  control: any;
}
export const DeptsInfo: FC<Props> = ({ register, control }) => {
  const yesNoOptions: SelectOption[] = [
    { value: true, label: "Да" },
    { value: false, label: "Нет" },
  ];

  return (
    <AccordionItem value="deptsInfo">
      <AccordionTrigger><h3 className="text-sm font-semibold">
        Долг и исполнительные производства
      </h3></AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.debtAmount">Сумма долга</Label>
          <Input
            id="primaryInfo.debtAmount"
            type="number"
            step="0.01"
            placeholder="1500000.50"
            {...register("primaryInfo.debtAmount")}
          />
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
      </AccordionContent>
    </AccordionItem>
  );
};
