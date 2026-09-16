import { Controller, useFieldArray, useFormContext } from "react-hook-form";
import { Trash2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { DatePickerInput } from "@/components/ui/DatePickerInput";
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

type TextFieldName =
  | "ownershipType"
  | "location"
  | "area"
  | "identificationNumber"
  | "pledgeInfo"
  | "accountType"
  | "amount"
  | "currency"
  | "issuer"
  | "participationShare"
  | "quantity"
  | "obligationContent"
  | "basisText";

type TextField = {
  name: TextFieldName;
  label: string;
  placeholder?: string;
};

type KindConfig = {
  title: string;
  nameLabel: string;
  subtypes: Array<{ value: PropertySubtype; label: string }>;
  withSubtypeSelect: boolean;
  withOpenedAt?: boolean;
  fields: TextField[];
};

const KINDS: PropertyKind[] = [
  "real_estate",
  "movable",
  "bank_account",
  "cash",
  "shares",
  "securities",
  "valuables",
  "receivables",
  "exclusive_rights",
];

const CONFIG: Record<PropertyKind, KindConfig> = {
  real_estate: {
    title: "Недвижимое имущество",
    nameLabel: "Вид и наименование имущества",
    withSubtypeSelect: true,
    subtypes: [
      { value: "land_plot", label: "Земельные участки" },
      { value: "house", label: "Жилые дома, дачи" },
      { value: "apartment", label: "Квартиры" },
      { value: "garage", label: "Гаражи" },
      { value: "other_real_estate", label: "Иное недвижимое имущество" },
    ],
    fields: [
      { name: "ownershipType", label: "Вид собственности", placeholder: "общая долевая собственность, доля в праве ½" },
      { name: "location", label: "Местонахождение (адрес)" },
      { name: "area", label: "Площадь, кв. м", placeholder: "78,8" },
      { name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" },
    ],
  },
  movable: {
    title: "Движимое имущество",
    nameLabel: "Вид, марка, модель, год изготовления",
    withSubtypeSelect: true,
    subtypes: [
      { value: "car", label: "Автомобили легковые" },
      { value: "truck", label: "Автомобили грузовые" },
      { value: "motorcycle", label: "Мототранспортные средства" },
      { value: "agricultural_machinery", label: "Сельскохозяйственная техника" },
      { value: "water_transport", label: "Водный транспорт" },
      { value: "air_transport", label: "Воздушный транспорт" },
      { value: "other_vehicle", label: "Иные транспортные средства" },
    ],
    fields: [
      { name: "identificationNumber", label: "Идентификационный номер", placeholder: "XTA219010K0512345" },
      { name: "ownershipType", label: "Вид собственности" },
      { name: "location", label: "Место нахождения" },
      { name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" },
    ],
  },
  bank_account: {
    title: "Денежные средства на счетах",
    nameLabel: "Наименование банка и иной кредитной организации",
    withSubtypeSelect: false,
    subtypes: [{ value: "bank_account", label: "Денежные средства на счетах" }],
    withOpenedAt: true,
    fields: [
      { name: "accountType", label: "Вид и валюта счёта", placeholder: "текущий, рубли" },
      { name: "amount", label: "Остаток на счёте, руб.", placeholder: "50 000,00" },
    ],
  },
  cash: {
    title: "Наличные денежные средства",
    nameLabel: "Наименование",
    withSubtypeSelect: false,
    subtypes: [{ value: "cash", label: "Наличные денежные средства" }],
    fields: [
      { name: "amount", label: "Сумма", placeholder: "20 000,00" },
      { name: "currency", label: "Валюта", placeholder: "рубли" },
    ],
  },
  shares: {
    title: "Акции и иное участие в коммерческих организациях",
    nameLabel: "Наименование и организационно-правовая форма организации",
    withSubtypeSelect: false,
    subtypes: [{ value: "shares", label: "Акции и иное участие в коммерческих организациях" }],
    fields: [
      { name: "location", label: "Местонахождение организации (адрес)" },
      { name: "amount", label: "Уставный, складочный капитал, паевый фонд, руб." },
      { name: "participationShare", label: "Доля участия", placeholder: "25%" },
      { name: "basisText", label: "Основание участия" },
      { name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" },
    ],
  },
  securities: {
    title: "Ценные бумаги",
    nameLabel: "Вид ценной бумаги",
    withSubtypeSelect: false,
    subtypes: [{ value: "securities", label: "Ценные бумаги" }],
    fields: [
      { name: "issuer", label: "Лицо, выпустившее ценную бумагу" },
      { name: "amount", label: "Номинальная величина обязательства, руб." },
      { name: "quantity", label: "Общее количество", placeholder: "100" },
      { name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" },
    ],
  },
  valuables: {
    title: "Ценное имущество",
    nameLabel: "Вид и наименование имущества",
    withSubtypeSelect: true,
    subtypes: [
      { value: "jewelry", label: "Драгоценности, в том числе ювелирные украшения, и другие предметы роскоши" },
      { value: "art", label: "Предметы искусства" },
      { value: "professional_equipment", label: "Имущество, необходимое для профессиональных занятий" },
      { value: "other_valuables", label: "Иное ценное имущество" },
    ],
    fields: [
      { name: "location", label: "Место нахождения/место хранения" },
      { name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" },
    ],
  },
  receivables: {
    title: "Дебиторская задолженность",
    nameLabel: "Дебитор",
    withSubtypeSelect: false,
    subtypes: [{ value: "receivables", label: "Дебиторская задолженность" }],
    fields: [
      { name: "amount", label: "Сумма задолженности, руб.", placeholder: "30 000,00" },
      { name: "obligationContent", label: "Содержание обязательства", placeholder: "заём по расписке" },
      { name: "basisText", label: "Основание возникновения", placeholder: "расписка от 01.02.2020 г." },
    ],
  },
  exclusive_rights: {
    title: "Исключительные права на результаты интеллектуальной деятельности",
    nameLabel: "Наименование",
    withSubtypeSelect: false,
    subtypes: [{ value: "exclusive_rights", label: "Исключительные права" }],
    fields: [{ name: "pledgeInfo", label: "Сведения о залоге и залогодержателе" }],
  },
};

const KIND_OF_SUBTYPE: Record<PropertySubtype, PropertyKind> = KINDS.reduce(
  (map, kind) => {
    CONFIG[kind].subtypes.forEach((subtype) => {
      map[subtype.value] = kind;
    });

    return map;
  },
  {} as Record<PropertySubtype, PropertyKind>,
);

const emptyItem = (kind: PropertyKind): PropertyItem => ({
  subtype: CONFIG[kind].subtypes[0].value,
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
  accountType: null,
  openedAt: null,
  amount: null,
  currency: null,
  issuer: null,
  participationShare: null,
  quantity: null,
  obligationContent: null,
  basisText: null,
});

export const PropertySection = (): JSX.Element => {
  const { register, control, watch } = useFormContext<FormValues>();
  const { fields, append, remove } = useFieldArray<FormValues, "judicial_procedure.property">({
    control,
    name: "judicial_procedure.property",
  });

  const items = watch("judicial_procedure.property") ?? [];

  const renderKind = (kind: PropertyKind): JSX.Element => {
    const config = CONFIG[kind];

    return (
      <div className="space-y-4" key={kind}>
        <div className="flex items-center justify-between">
          <Label className="text-base font-semibold">{config.title}</Label>
          <Button type="button" variant="outline" onClick={() => append(emptyItem(kind))}>
            Добавить
          </Button>
        </div>

        {fields.map((field, index) => {
          const item = items[index];

          if (!item || KIND_OF_SUBTYPE[item.subtype] !== kind) {
            return null;
          }

          return (
            <Card key={field.id}>
              <CardContent className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                {config.withSubtypeSelect ? (
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
                            {config.subtypes.map((option) => (
                              <SelectItem key={option.value} value={option.value}>
                                {option.label}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      )}
                    />
                  </div>
                ) : null}

                <div className="space-y-2">
                  <Label htmlFor={`judicial_procedure.property.${index}.name`}>{config.nameLabel}</Label>
                  <Input
                    id={`judicial_procedure.property.${index}.name`}
                    {...register(`judicial_procedure.property.${index}.name`)}
                  />
                </div>

                {config.withOpenedAt ? (
                  <div className="space-y-2">
                    <Label htmlFor={`judicial_procedure.property.${index}.openedAt`}>Дата открытия счёта</Label>
                    <Controller
                      control={control}
                      name={`judicial_procedure.property.${index}.openedAt`}
                      render={({ field: openedAtField }) => (
                        <DatePickerInput
                          id={`judicial_procedure.property.${index}.openedAt`}
                          name={`judicial_procedure.property.${index}.openedAt`}
                          value={(openedAtField.value as string) ?? ""}
                          onChange={openedAtField.onChange}
                        />
                      )}
                    />
                  </div>
                ) : null}

                {config.fields.map((textField) => (
                  <div className="space-y-2" key={textField.name}>
                    <Label htmlFor={`judicial_procedure.property.${index}.${textField.name}`}>
                      {textField.label}
                    </Label>
                    <Input
                      id={`judicial_procedure.property.${index}.${textField.name}`}
                      placeholder={textField.placeholder}
                      {...register(`judicial_procedure.property.${index}.${textField.name}`)}
                    />
                  </div>
                ))}

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
  };

  return (
    <div className="mt-6 space-y-6">
      <Label className="text-lg font-semibold">Имущество</Label>
      {KINDS.map((kind) => renderKind(kind))}
    </div>
  );
};
