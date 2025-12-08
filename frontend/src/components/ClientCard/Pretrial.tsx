import { Controller, useFieldArray, useFormContext } from "react-hook-form";
import { useMemo } from "react";

import { DatePickerInput } from "@/components/ui/DatePickerInput";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { TabsContent } from "@/components/ui/tabs";
import { FormValues } from "./types";
import { DocumentsList } from "./DocumentsList";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Button } from "@/components/ui/button";
import { Trash2 } from "lucide-react";

import type { ReferenceData } from "@/types/reference";

interface PretrialTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
}

export const PretrialTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
}: PretrialTabProps): JSX.Element => {
  const { register, control, watch } = useFormContext<FormValues>();
  const toIdString = (value: unknown) => {
    if (typeof value === "string" || typeof value === "number")
      return String(value);
    if (value && typeof value === "object" && "id" in (value as any))
      return String((value as any).id);
    return "";
  };

  const {
    fields: creditorFields,
    append: appendCreditor,
    remove: removeCreditor,
  } = useFieldArray({
    control: control as any,
    name: "pre_court.creditors" as any,
  });
  const watchedCreditors = watch("pre_court.creditors") ?? [];
  const selectedCreditorIds = useMemo(
    () =>
      watchedCreditors
        .filter((id) => typeof id === "number")
        .map((id) => String(id)),
    [watchedCreditors]
  );
  const getAvailableCreditors = (currentValue?: string) =>
    (referenceData?.creditors ?? []).filter((creditor) => {
      const id = String(creditor.id);
      return id === currentValue || !selectedCreditorIds.includes(id);
    });
  const hasAvailableCreditors = getAvailableCreditors().length > 0;

  const documents =
    (
      contractData?.pre_court as {
        documents?: Array<{ id: number; name: string }>;
      }
    )?.documents || [];
  const hearingDateTimeValue = watch("pre_court.hearingDateTime") as
    | string
    | undefined;
  const parseDateTime = (value?: string) => {
    if (!value) return { date: "", time: "" };
    const [date, timeWithZone] = value.split("T");
    const time = timeWithZone?.slice(0, 5) ?? "";
    return { date, time };
  };
  const combineDateTime = (date: string, time: string) => {
    if (!date && !time) return "";
    if (!date) return time ? `T${time}` : "";
    return time ? `${date}T${time}` : date;
  };
  const hasHearingDateTime = Boolean(hearingDateTimeValue);

  return (
    <TabsContent value="pre_court" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Досудебка</CardTitle>
          <CardDescription>Информация о досудебном этапе</CardDescription>
        </CardHeader>
        <CardContent>
          <Accordion
            type="multiple"
            className="mb-6"
            defaultValue={["creditorsInfo", "courtInfo"]}
          >
            <AccordionItem value="creditorsInfo">
              <AccordionTrigger>
                <h3 className="text-xl font-semibold">Кредиторы</h3>
              </AccordionTrigger>
              <AccordionContent>
                <div className="space-y-3 p-1">
                  <div className="flex items-center justify-between">
                    <Label htmlFor="pre_court.creditors" className="font-medium">
                      Кредиторы (можно несколько)
                    </Label>
                  </div>

                  {creditorFields.length === 0 ? (
                    <div className="space-y-2">
                      <Label htmlFor="pre_court.creditors.empty">
                        Кредитор 1
                      </Label>
                      <Select
                        onValueChange={(val) => appendCreditor(Number(val))}
                        disabled={!hasAvailableCreditors}
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Выберите кредитора" />
                        </SelectTrigger>
                        <SelectContent>
                          {getAvailableCreditors().map((creditor) => (
                            <SelectItem
                              key={creditor.id}
                              value={String(creditor.id)}
                            >
                              {creditor.name}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {creditorFields.map((item, index) => {
                        const currentValue = toIdString(
                          watchedCreditors[index]
                        );
                        const options = getAvailableCreditors(currentValue);

                        return (
                          <div key={item.id} className="flex items-end gap-3">
                            <div className="flex-1 space-y-2">
                              <Label htmlFor={`pre_court.creditors.${index}`}>
                                Кредитор {index + 1}
                              </Label>
                              <Controller
                                name={`pre_court.creditors.${index}`}
                                control={control}
                                render={({ field }) => (
                                  <Select
                                    value={toIdString(field.value)}
                                    onValueChange={(val) =>
                                      field.onChange(Number(val))
                                    }
                                  >
                                    <SelectTrigger>
                                      <SelectValue placeholder="Выберите кредитора" />
                                    </SelectTrigger>
                                    <SelectContent>
                                      {options.map((creditor) => (
                                        <SelectItem
                                          key={creditor.id}
                                          value={String(creditor.id)}
                                        >
                                          {creditor.name}
                                        </SelectItem>
                                      ))}
                                    </SelectContent>
                                  </Select>
                                )}
                              />
                            </div>
                            <Button
                              type="button"
                              variant="ghost"
                              size="icon"
                              onClick={() => removeCreditor(index)}
                              aria-label={`Удалить кредитора ${index + 1}`}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        );
                      })}
                    </div>
                  )}

                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="ml-auto block"
                    onClick={() => {
                      const firstAvailable = getAvailableCreditors()[0];
                      if (firstAvailable) {
                        appendCreditor(Number(firstAvailable.id));
                      }
                    }}
                    disabled={!hasAvailableCreditors}
                  >
                    Добавить кредитора
                  </Button>
                </div>
              </AccordionContent>
            </AccordionItem>

            <AccordionItem value="courtInfo">
              <AccordionTrigger>
                <h3 className="text-xl font-semibold">Суд и заседание</h3>
              </AccordionTrigger>
              <AccordionContent>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 p-1">
                  <div className="space-y-2">
                    <Label htmlFor="pre_court.court">Арбитражный суд</Label>
                    <Controller
                      name="pre_court.court"
                      control={control}
                      render={({ field }) => (
                        <Select
                          value={toIdString(field.value)}
                          onValueChange={field.onChange}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Выберите суд" />
                          </SelectTrigger>
                          <SelectContent>
                            {referenceData?.courts?.map((court) => (
                              <SelectItem
                                key={court.id}
                                value={String(court.id)}
                              >
                                {court.name}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      )}
                    />
                  </div>
                  <div className="space-y-1">
                    <Label>Номер дела</Label>
                    <Input
                      type="text"
                      placeholder="А56-12345/2024"
                      {...register("pre_court.caseNumber")}
                    />
                    <p className="text-xs text-muted-foreground/70 italic">Пример: А56-12345/2024</p>
                  </div>

                  <div className="space-y-1">
                    <Label>Доверенность</Label>
                    <div className="grid grid-cols-2 gap-2">
                      <div className="space-y-1">
                        <Input
                          type="text"
                          placeholder="ДГ-2024-001234"
                          {...register("pre_court.powerOfAttorneyNumber")}
                        />
                        <p className="text-xs text-muted-foreground/70 italic">Пример: ДГ-2024-001234</p>
                      </div>
                      <Controller
                        name="pre_court.powerOfAttorneyDate"
                        control={control}
                        render={({ field }) => (
                          <DatePickerInput
                            value={(field.value as string) ?? ""}
                            onChange={field.onChange}
                            className="space-y-1"
                          />
                        )}
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label>Дата и время заседания</Label>
                    <Controller
                      name="pre_court.hearingDateTime"
                      control={control}
                      render={({ field }) => {
                        const { date, time } = parseDateTime(
                          field.value as string
                        );
                        return (
                          <div className="grid grid-cols-2 gap-2">
                            <DatePickerInput
                              value={date}
                              onChange={(nextDate) =>
                                field.onChange(
                                  combineDateTime(
                                    typeof nextDate === "string"
                                      ? nextDate
                                      : "",
                                    time
                                  )
                                )
                              }
                              className="space-y-1"
                            />
                            <div className="space-y-1">
                              <Input
                                type="time"
                                value={time}
                                onChange={(e) =>
                                  field.onChange(
                                    combineDateTime(date, e.target.value)
                                  )
                                }
                              />
                            </div>
                          </div>
                        );
                      }}
                    />
                  </div>
                  {hasHearingDateTime && (
                    <div className="space-y-1">
                      <Label htmlFor="pre_court.efrsbCabinet">
                        Кабинет ЕФРСБ
                      </Label>
                      <Input
                        id="pre_court.efrsbCabinet"
                        placeholder="https://cabinet.fedresurs.ru/Person/12345678"
                        {...register("pre_court.efrsbCabinet")}
                      />
                      <p className="text-xs text-muted-foreground/70 italic">Пример: https://cabinet.fedresurs.ru/Person/12345678</p>
                    </div>
                  )}
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>

          <DocumentsList
            documents={documents}
            title="Документы досудебного этапа:"
            onDocumentClick={openDocument}
            onDownload={onDownload}
          />
        </CardContent>
      </Card>
    </TabsContent>
  );
};
