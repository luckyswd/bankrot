import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import { Controller } from "react-hook-form";
import InputMask from "react-input-mask";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
interface Props {
  register: any;
  control: any;
}
export const ContactInfo: FC<Props> = ({ register, control }) => {
  return (
    <AccordionItem value="contactInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Контакты</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <Controller
          control={control}
          name="basic_info.phone"
          render={({ field }) => (
            <div className="space-y-1">
              <Label htmlFor="basic_info.phone">Телефон</Label>
              <InputMask
                mask="+7(999)-999-99-99"
                value={field.value ?? ""}
                onChange={field.onChange}
                onBlur={field.onBlur}
                maskChar={null}
              >
                {(inputProps: any) => (
                  <Input
                    id="basic_info.phone"
                    type="tel"
                    placeholder="+7(999)-999-99-99"
                    {...inputProps}
                  />
                )}
              </InputMask>
            </div>
          )}
        />

        <div className="space-y-1">
          <Label htmlFor="basic_info.email">Электронная почта</Label>
          <Input
            id="basic_info.email"
            type="email"
            placeholder="ivan.petrov@yandex.ru"
            {...register("basic_info.email")}
          />
        </div>

        <div className="space-y-1 lg:col-span-3">
          <Label htmlFor="basic_info.mailingAddress">
            Адрес для направления корреспонденции
          </Label>
          <Input
            id="basic_info.mailingAddress"
            placeholder="191186, г. Санкт-Петербург, Невский проспект, д. 28, кв. 45"
            {...register("basic_info.mailingAddress")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
