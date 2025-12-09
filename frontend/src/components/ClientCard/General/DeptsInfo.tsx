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
      <AccordionTrigger><h3 className="text-xl font-semibold">
        Долг и исполнительные производства
      </h3></AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-1">
          <Label htmlFor="basic_info.debtAmount">Сумма долга</Label>
          <Input
            id="basic_info.debtAmount"
            type="number"
            step="0.01"
            placeholder="2500000.75"
            {...register("basic_info.debtAmount")}
          />
        </div>

        <div className="space-y-2">
          <Label>Наличие возбужденных исполнительных производств</Label>
          <Controller
            control={control}
            name="basic_info.hasEnforcementProceedings"
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
