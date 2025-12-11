import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import type { ReferenceData } from "@/types/reference";

interface Props {
  control: any;
  referenceData?: ReferenceData;
}

export const ManagerInfo: FC<Props> = ({ control, referenceData }) => {
  const toIdString = (value: unknown) => {
    if (typeof value === "string" || typeof value === "number") {
      return String(value);
    }
    if (value && typeof value === "object" && "id" in (value as any)) {
      return String((value as any).id);
    }
    return "";
  };

  return (
    <AccordionItem value="managerInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Администрирование</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <Controller
          name="basic_info.manager"
          control={control}
          render={({ field }) => (
            <div className="space-y-2">
              <Label htmlFor="basic_info.manager">
                Управляющий
              </Label>
              <Select
                value={field.value ? toIdString(field.value) : "none"}
                onValueChange={(value) => {
                  field.onChange(value === "none" ? null : value);
                }}
              >
                <SelectTrigger id="basic_info.manager">
                  <SelectValue placeholder="Выберите управляющего" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">Не указано</SelectItem>
                  {referenceData?.users?.map((item) => (
                    <SelectItem key={item.id} value={String(item.id)}>
                      {item.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          )}
        />
      </AccordionContent>
    </AccordionItem>
  );
};

