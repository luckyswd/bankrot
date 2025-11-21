import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC } from "react";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
interface Props {
  register: any;
}
export const ContactInfo: FC<Props> = ({ register }) => {
  return (
    <AccordionItem value="contactInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Контакты</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.phone">Телефон</Label>
          <Input
            id="primaryInfo.phone"
            type="tel"
            placeholder="+7 (999) 123-45-67"
            {...register("primaryInfo.phone")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.email">Электронная почта</Label>
          <Input
            id="primaryInfo.email"
            type="email"
            placeholder="example@mail.ru"
            {...register("primaryInfo.email")}
          />
        </div>

        <div className="space-y-2 lg:col-span-3">
          <Label htmlFor="primaryInfo.mailingAddress">
            Адрес для направления корреспонденции
          </Label>
          <Input
            id="primaryInfo.mailingAddress"
            placeholder="196084, г. Санкт-Петербург, ул. Смоленская, 9-418"
            {...register("primaryInfo.mailingAddress")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
