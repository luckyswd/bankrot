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
      name: "basic_info.work",
    }) ?? false;

  return (
    <AccordionItem value="workInfo">
      <AccordionTrigger>
        <div className="flex items-center gap-3">
          <h3 className="text-xl font-semibold">Работа и образование</h3>
          <Controller
            name="basic_info.work"
            control={control}
            render={({ field }) => {
              return (
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
              );
            }}
          />
        </div>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-2">
          <Label>Является ли студентом</Label>
          <Controller
            control={control}
            name="basic_info.isStudent"
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

        <div className="space-y-1 lg:col-span-2">
          <Label htmlFor="basic_info.employerName">
            Наименование работодателя
          </Label>
          <Input
            id="basic_info.employerName"
            placeholder='ООО "ТехноСтрой"'
            {...register("basic_info.employerName")}
            disabled={!isWorking}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: ООО "ТехноСтрой"</p>
        </div>

        <div className="space-y-1 lg:col-span-2">
          <Label htmlFor="basic_info.employerAddress">
            Адрес работодателя
          </Label>
          <Input
            id="basic_info.employerAddress"
            placeholder="г. Санкт-Петербург, ул. Лиговский проспект, д. 50, офис 301"
            {...register("basic_info.employerAddress")}
            disabled={!isWorking}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: г. Санкт-Петербург, ул. Лиговский проспект, д. 50, офис 301</p>
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.employerInn">ИНН работодателя</Label>
          <Input
            id="basic_info.employerInn"
            placeholder="7812345678"
            maxLength={12}
            {...register("basic_info.employerInn")}
            disabled={!isWorking}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: 7812345678</p>
        </div>

        <div className="space-y-1 lg:col-span-3">
          <Label htmlFor="basic_info.socialBenefits">
            Пенсии и социальные выплаты
          </Label>
          <Input
            id="basic_info.socialBenefits"
            placeholder="Пенсия по старости, ЕДВ инвалида III группы"
            {...register("basic_info.socialBenefits")}
          />
          <p className="text-xs text-muted-foreground/70 italic">Пример: Пенсия по старости, ЕДВ инвалида III группы</p>
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
