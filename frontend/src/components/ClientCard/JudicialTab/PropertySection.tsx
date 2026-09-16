import { Controller, useFieldArray, useFormContext } from "react-hook-form";
import { Trash2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";

import { FormValues, PropertyItem, PropertyKind, PropertySubtype } from "../types";

const SUBTYPES: Record<PropertyKind, Array<{ value: PropertySubtype; label: string }>> = {
  real_estate: [
    { value: "land_plot", label: "Земельные участки" },
    { value: "house", label: "Жилые дома, дачи" },
    { value: "apartment", label: "Квартиры" },
    { value: "garage", label: "Гаражи" },
    { value: "other_real_estate", label: "Иное недвижимое имущество" },
  ],
  movable: [
    { value: "car", label: "Автомобили легковые" },
    { value: "truck", label: "Автомобили грузовые" },
    { value: "motorcycle", label: "Мототранспортные средства" },
    { value: "agricultural_machinery", label: "Сельскохозяйственная техника" },
    { value: "water_transport", label: "Водный транспорт" },
    { value: "air_transport", label: "Воздушный транспорт" },
    { value: "other_vehicle", label: "Иные транспортные средства" },
  ],
};

const KIND_TITLES: Record<PropertyKind, string> = {
  real_estate: "Недвижимое имущество",
  movable: "Движимое имущество",
};

const NAME_LABELS: Record<PropertyKind, string> = {
  real_estate: "Вид и наименование имущества",
  movable: "Вид, марка, модель, год изготовления",
};

const kindOfSubtype = (subtype: PropertySubtype): PropertyKind =>
  SUBTYPES.real_estate.some((option) => option.value === subtype) ? "real_estate" : "movable";

const emptyItem = (kind: PropertyKind): PropertyItem => ({
  subtype: SUBTYPES[kind][0].value,
  name: "",
  ownershipType: null,
  location: null,
  area: null,
  identificationNumber: null,
  pledgeInfo: null,
  managerValuation: null,
  appraiserValuation: null,
  isExcludedFromEstate: null,
  exclusionReason: null,
  excludedValuation: null,
});

export const PropertySection = (): JSX.Element => {
  const { register, control, watch } = useFormContext<FormValues>();
  const { fields, append, remove } = useFieldArray<FormValues, "judicial_procedure.property">({
    control,
    name: "judicial_procedure.property",
  });

  const items = watch("judicial_procedure.property") ?? [];

  const renderKind = (kind: PropertyKind): JSX.Element => (
    <div className="space-y-4" key={kind}>
      <div className="flex items-center justify-between">
        <Label className="text-base font-semibold">{KIND_TITLES[kind]}</Label>
        <Button type="button" variant="outline" onClick={() => append(emptyItem(kind))}>
          Добавить
        </Button>
      </div>

      {fields.map((field, index) => {
        const item = items[index];

        if (!item || kindOfSubtype(item.subtype) !== kind) {
          return null;
        }

        return (
          <Card key={field.id}>
            <CardContent className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.subtype`}>Подвид</Label>
                <Controller
                  control={control}
                  name={`judicial_procedure.property.${index}.subtype`}
                  render={({ field: subtypeField }) => (
                    <Select value={subtypeField.value} onValueChange={subtypeField.onChange}>
                      <SelectTrigger id={`judicial_procedure.property.${index}.subtype`}>
                        <SelectValue placeholder="Выберите подвид" />
                      </SelectTrigger>
                      <SelectContent>
                        {SUBTYPES[kind].map((option) => (
                          <SelectItem key={option.value} value={option.value}>
                            {option.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  )}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.name`}>{NAME_LABELS[kind]}</Label>
                <Input
                  id={`judicial_procedure.property.${index}.name`}
                  {...register(`judicial_procedure.property.${index}.name`)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.ownershipType`}>Вид собственности</Label>
                <Input
                  id={`judicial_procedure.property.${index}.ownershipType`}
                  placeholder="общая долевая собственность, доля в праве ½"
                  {...register(`judicial_procedure.property.${index}.ownershipType`)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.location`}>
                  {kind === "real_estate" ? "Местонахождение (адрес)" : "Место нахождения"}
                </Label>
                <Input
                  id={`judicial_procedure.property.${index}.location`}
                  {...register(`judicial_procedure.property.${index}.location`)}
                />
              </div>

              {kind === "real_estate" ? (
                <div className="space-y-2">
                  <Label htmlFor={`judicial_procedure.property.${index}.area`}>Площадь, кв. м</Label>
                  <Input
                    id={`judicial_procedure.property.${index}.area`}
                    placeholder="78,8"
                    {...register(`judicial_procedure.property.${index}.area`)}
                  />
                </div>
              ) : (
                <div className="space-y-2">
                  <Label htmlFor={`judicial_procedure.property.${index}.identificationNumber`}>
                    Идентификационный номер
                  </Label>
                  <Input
                    id={`judicial_procedure.property.${index}.identificationNumber`}
                    placeholder="XTA219010K0512345"
                    {...register(`judicial_procedure.property.${index}.identificationNumber`)}
                  />
                </div>
              )}

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.pledgeInfo`}>
                  Сведения о залоге и залогодержателе
                </Label>
                <Input
                  id={`judicial_procedure.property.${index}.pledgeInfo`}
                  {...register(`judicial_procedure.property.${index}.pledgeInfo`)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.managerValuation`}>
                  Стоимость по оценке финансового управляющего, руб.
                </Label>
                <Input
                  id={`judicial_procedure.property.${index}.managerValuation`}
                  placeholder="1 000 000,00"
                  {...register(`judicial_procedure.property.${index}.managerValuation`)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor={`judicial_procedure.property.${index}.appraiserValuation`}>
                  Стоимость по оценке оценщика, руб.
                </Label>
                <Input
                  id={`judicial_procedure.property.${index}.appraiserValuation`}
                  placeholder="1 200 000,00"
                  {...register(`judicial_procedure.property.${index}.appraiserValuation`)}
                />
              </div>

              <div className="flex items-center gap-3">
                <Controller
                  control={control}
                  name={`judicial_procedure.property.${index}.isExcludedFromEstate`}
                  render={({ field: excludedField }) => (
                    <Switch
                      id={`judicial_procedure.property.${index}.isExcludedFromEstate`}
                      checked={Boolean(excludedField.value)}
                      onCheckedChange={excludedField.onChange}
                    />
                  )}
                />
                <Label htmlFor={`judicial_procedure.property.${index}.isExcludedFromEstate`}>
                  Исключается из конкурсной массы
                </Label>
              </div>

              {item.isExcludedFromEstate ? (
                <>
                  <div className="space-y-2">
                    <Label htmlFor={`judicial_procedure.property.${index}.excludedValuation`}>
                      Стоимость исключаемого имущества, руб.
                    </Label>
                    <Input
                      id={`judicial_procedure.property.${index}.excludedValuation`}
                      placeholder="1 000 000,00"
                      {...register(`judicial_procedure.property.${index}.excludedValuation`)}
                    />
                  </div>
                  <div className="space-y-2 md:col-span-2">
                    <Label htmlFor={`judicial_procedure.property.${index}.exclusionReason`}>
                      Основание исключения
                    </Label>
                    <Input
                      id={`judicial_procedure.property.${index}.exclusionReason`}
                      placeholder="единственное пригодное для проживания жильё"
                      {...register(`judicial_procedure.property.${index}.exclusionReason`)}
                    />
                  </div>
                </>
              ) : null}

              <div className="md:col-span-2">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className="ml-auto block text-destructive"
                  onClick={() => remove(index)}
                  title="Удалить запись"
                >
                  <Trash2 className="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        );
      })}
    </div>
  );

  return (
    <div className="mt-6 space-y-6">
      <Label className="text-lg font-semibold">Имущество</Label>
      {(["real_estate", "movable"] as PropertyKind[]).map((kind) => renderKind(kind))}
    </div>
  );
};
