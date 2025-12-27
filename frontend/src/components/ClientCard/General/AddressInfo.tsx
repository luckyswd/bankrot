import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FC, useMemo } from "react";
import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

interface Props {
  register: any;
  control: any;
  useWatch: any;
}

export const AddressInfo: FC<Props> = ({ register, control, useWatch }) => {
  const registrationRegion = useWatch({ control, name: "basic_info.registrationRegion" })
  const registrationDistrict = useWatch({ control, name: "basic_info.registrationDistrict" })
  const registrationCity = useWatch({ control, name: "basic_info.registrationCity" })
  const registrationSettlement = useWatch({ control, name: "basic_info.registrationSettlement" })
  const registrationStreet = useWatch({ control, name: "basic_info.registrationStreet" })
  const registrationHouse = useWatch({ control, name: "basic_info.registrationHouse" })
  const registrationBuilding = useWatch({ control, name: "basic_info.registrationBuilding" })
  const registrationApartment = useWatch({ control, name: "basic_info.registrationApartment" })

  const fullAddress = useMemo(() => {
    const parts: string[] = []
    
    if (registrationRegion) {
      parts.push(registrationRegion)
    }
    if (registrationDistrict) {
      parts.push(registrationDistrict + " район")
    }
    if (registrationCity) {
      parts.push("г. " + registrationCity)
    }
    if (registrationSettlement) {
      parts.push(registrationSettlement)
    }
    if (registrationStreet) {
      parts.push("ул. " + registrationStreet)
    }
    if (registrationHouse) {
      parts.push("д. " + registrationHouse)
    }
    if (registrationBuilding) {
      parts.push("корп. " + registrationBuilding)
    }
    if (registrationApartment) {
      parts.push("кв. " + registrationApartment)
    }
    
    return parts.length > 0 ? parts.join(", ") : ""
  }, [
    registrationRegion,
    registrationDistrict,
    registrationCity,
    registrationSettlement,
    registrationStreet,
    registrationHouse,
    registrationBuilding,
    registrationApartment,
  ])
  return (
    <AccordionItem value="addressInfo">
      <AccordionTrigger>
        <h3 className="text-xl font-semibold">Адрес регистрации</h3>
      </AccordionTrigger>
      <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 p-2">
        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationRegion">
            Субъект РФ (регион)
          </Label>
          <Input
            id="basic_info.registrationRegion"
            placeholder="Ленинградская область"
            {...register("basic_info.registrationRegion")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationDistrict">Район</Label>
          <Input
            id="basic_info.registrationDistrict"
            placeholder="Московский район"
            {...register("basic_info.registrationDistrict")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationCity">Город</Label>
          <Input
            id="basic_info.registrationCity"
            placeholder="Санкт-Петербург"
            {...register("basic_info.registrationCity")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationSettlement">
            Населенный пункт
          </Label>
          <Input
            id="basic_info.registrationSettlement"
            placeholder="пос. Парголово"
            {...register("basic_info.registrationSettlement")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationStreet">Улица</Label>
          <Input
            id="basic_info.registrationStreet"
            placeholder="Невский проспект"
            {...register("basic_info.registrationStreet")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationHouse">Дом</Label>
          <Input
            id="basic_info.registrationHouse"
            placeholder="28"
            {...register("basic_info.registrationHouse")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationBuilding">Корпус</Label>
          <Input
            id="basic_info.registrationBuilding"
            placeholder="2"
            {...register("basic_info.registrationBuilding")}
          />
        </div>

        <div className="space-y-1">
          <Label htmlFor="basic_info.registrationApartment">Квартира</Label>
          <Input
            id="basic_info.registrationApartment"
            placeholder="45"
            {...register("basic_info.registrationApartment")}
          />
        </div>
        <div className="space-y-1">
          <Label htmlFor="basic_info.postalCode">Почтовый индекс</Label>
          <Input
            id="basic_info.postalCode"
            placeholder="191186"
            {...register("basic_info.postalCode")}
          />
        </div>
        <div className="space-y-1 col-span-3">
          <Label htmlFor="basic_info.registrationFullAddress">
            Адрес регистрации
          </Label>
          <Input
            id="basic_info.registrationFullAddress"
            value={fullAddress}
            readOnly
            className="bg-muted"
          />
        </div>
        <div className="space-y-1 col-span-3">
          <Label htmlFor="basic_info.actualPlaceResidence">
            Фактическое место проживания
          </Label>
          <Input
            id="basic_info.actualPlaceResidence"
            placeholder="Воронеж, Район 20 район, г. Воронеж, ул. Центральная, д. 69, кв. 257"
            {...register("basic_info.actualPlaceResidence")}
          />
        </div>
      </AccordionContent>
    </AccordionItem>
  );
};
