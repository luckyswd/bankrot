import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller, useWatch } from "react-hook-form";
import { SelectField, SelectOption } from "@/components/shared/SelectFields";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Switch } from "@/components/ui/switch";

interface Props {
  register: any;
  control: any;
}
const yesNoOptions: SelectOption[] = [
  { value: true, label: "Да" },
  { value: false, label: "Нет" },
];
export const WorkInfo: FC<Props> = ({ register, control }) => {
  const isWorking =
    useWatch({
      control,
      name: "primaryInfo.work",
    }) ?? false;

  return (
    <AccordionItem value="workInfo">
      <AccordionTrigger>
        <div className="flex items-center gap-3">
          <h3 className="text-xl font-semibold">Работа и образование</h3>
          <Controller
            name="primaryInfo.work"
            control={control}
            render={({ field }) => (
                            <div
                onClick={(e) => e.stopPropagation()}
                onPointerDown={(e) => e.stopPropagation()}
              >
              <Switch
                checked={Boolean(field.value)}
                onCheckedChange={field.onChange}
                aria-label="Работает"
              />
              </div>
            )}
          />
        </div>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
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
                disabled={!isWorking}
              />
            )}
          />
        </div>

        <div className="space-y-2 lg:col-span-2">
          <Label htmlFor="primaryInfo.employerName">
            Наименование работодателя
          </Label>
          <Input
            id="primaryInfo.employerName"
            placeholder='ООО "Рога и Копыта"'
            {...register("primaryInfo.employerName")}
            disabled={!isWorking}
          />
        </div>

        <div className="space-y-2 lg:col-span-2">
          <Label htmlFor="primaryInfo.employerAddress">
            Адрес работодателя
          </Label>
          <Input
            id="primaryInfo.employerAddress"
            placeholder="г. Москва, ул. Ленина, д. 1"
            {...register("primaryInfo.employerAddress")}
            disabled={!isWorking}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.employerInn">ИНН работодателя</Label>
          <Input
            id="primaryInfo.employerInn"
            placeholder="1234567890"
            maxLength={12}
            {...register("primaryInfo.employerInn")}
            disabled={!isWorking}
          />
        </div>

        <div className="space-y-2 lg:col-span-3">
          <Label htmlFor="primaryInfo.socialBenefits">
            Пенсии и социальные выплаты
          </Label>
          <Input
            id="primaryInfo.socialBenefits"
            placeholder="Алименты, пособие, ЕДВ, прочее"
            {...register("primaryInfo.socialBenefits")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
