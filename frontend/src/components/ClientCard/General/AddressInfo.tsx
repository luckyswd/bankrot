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

export const AddressInfo: FC<Props> = ({ register }) => {
  return (
    <AccordionItem value="addressInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Адрес регистрации</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationRegion">
            Субъект РФ (регион)
          </Label>
          <Input
            id="primaryInfo.registrationRegion"
            placeholder="Санкт-Петербург"
            {...register("primaryInfo.registrationRegion")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationDistrict">Район</Label>
          <Input
            id="primaryInfo.registrationDistrict"
            placeholder="Московский"
            {...register("primaryInfo.registrationDistrict")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationCity">Город</Label>
          <Input
            id="primaryInfo.registrationCity"
            placeholder="Санкт-Петербург"
            {...register("primaryInfo.registrationCity")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationSettlement">
            Населенный пункт
          </Label>
          <Input
            id="primaryInfo.registrationSettlement"
            placeholder="пос. Ленинский"
            {...register("primaryInfo.registrationSettlement")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationStreet">Улица</Label>
          <Input
            id="primaryInfo.registrationStreet"
            placeholder="Смоленская"
            {...register("primaryInfo.registrationStreet")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationHouse">Дом</Label>
          <Input
            id="primaryInfo.registrationHouse"
            placeholder="9"
            {...register("primaryInfo.registrationHouse")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationBuilding">Корпус</Label>
          <Input
            id="primaryInfo.registrationBuilding"
            placeholder="1"
            {...register("primaryInfo.registrationBuilding")}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="primaryInfo.registrationApartment">Квартира</Label>
          <Input
            id="primaryInfo.registrationApartment"
            placeholder="418"
            {...register("primaryInfo.registrationApartment")}
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="primaryInfo.postalCode">Почтовый индекс</Label>
          <Input
            id="primaryInfo.postalCode"
            placeholder="418"
            {...register("primaryInfo.postalCode")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
