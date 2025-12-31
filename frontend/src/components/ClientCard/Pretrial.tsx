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
import { FormValues, PreCourtCreditorItem } from "./types";
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

import type { ReferenceData } from "@/types/reference";

interface PretrialTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void;
}

export const PretrialTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
  onNavigateToField,
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
    fields: preCourtCreditorFields,
    append: appendPreCourtCreditor,
    remove: removePreCourtCreditor,
  } = useFieldArray<FormValues, "pre_court.preCourtCreditors">({
    control,
    name: "pre_court.preCourtCreditors",
  });

  const watchedPreCourtCreditors = watch("pre_court.preCourtCreditors") ?? [];
  const selectedCreditorIds = useMemo(
    () =>
      watchedPreCourtCreditors
        .map((item) => (item?.creditorId ? String(item.creditorId) : null))
        .filter((id): id is string => id !== null),
    [watchedPreCourtCreditors]
  );

  const getAvailableCreditors = (currentCreditorId?: number) =>
    (referenceData?.creditors ?? []).filter((creditor) => {
      const id = String(creditor.id);
      return (
        (currentCreditorId && creditor.id === currentCreditorId) ||
        !selectedCreditorIds.includes(id)
      );
    });

  const createEmptyPreCourtCreditor = (): PreCourtCreditorItem => ({
    creditorId: 0,
    creditContractNumber: null,
    creditContractDate: null,
    debtAmount: null,
    principalAmount: null,
    financialSanctions: null,
  });

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
                <div className="space-y-4 p-1">
                  <div className="flex items-center justify-between">
                    <Label className="text-lg font-semibold">
                      Кредиторы
                    </Label>
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() =>
                        appendPreCourtCreditor(createEmptyPreCourtCreditor())
                      }
                    >
                      Добавить кредитора
                    </Button>
                  </div>

                  {preCourtCreditorFields.map((field, index) => {
                    const currentCreditorId =
                      watchedPreCourtCreditors[index]?.creditorId;
                    const availableCreditors = getAvailableCreditors(
                      currentCreditorId
                    );

                    return (
                      <Card key={field.id}>
                        <CardContent className="pt-6">
                          <div className="space-y-4">
                            <div className="flex items-center justify-between">
                              <Label className="text-base font-semibold">
                                Кредитор #{index + 1}
                              </Label>
                              <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                onClick={() => removePreCourtCreditor(index)}
                              >
                                Удалить
                              </Button>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.creditorId`}
                                >
                                  Кредитор
                                </Label>
                                <Controller
                                  control={control}
                                  name={`pre_court.preCourtCreditors.${index}.creditorId`}
                                  render={({ field: selectField }) => (
                                    <Select
                                      value={
                                        selectField.value
                                          ? String(selectField.value)
                                          : ""
                                      }
                                      onValueChange={(value) =>
                                        selectField.onChange(Number(value))
                                      }
                                    >
                                      <SelectTrigger
                                        id={`pre_court.preCourtCreditors.${index}.creditorId`}
                                      >
                                        <SelectValue placeholder="Выберите кредитора" />
                                      </SelectTrigger>
                                      <SelectContent>
                                        {availableCreditors.map((item) => (
                                          <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                          >
                                            {item.name}
                                          </SelectItem>
                                        ))}
                                      </SelectContent>
                                    </Select>
                                  )}
                                />
                              </div>

                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.creditContractNumber`}
                                >
                                  № Кредитного договора
                                </Label>
                                <Input
                                  id={`pre_court.preCourtCreditors.${index}.creditContractNumber`}
                                  {...register(
                                    `pre_court.preCourtCreditors.${index}.creditContractNumber`
                                  )}
                                />
                              </div>

                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.creditContractDate`}
                                >
                                  Дата Кредитного договора
                                </Label>
                                <Controller
                                  control={control}
                                  name={`pre_court.preCourtCreditors.${index}.creditContractDate`}
                                  render={({ field: dateField }) => (
                                    <DatePickerInput
                                      id={`pre_court.preCourtCreditors.${index}.creditContractDate`}
                                      name={`pre_court.preCourtCreditors.${index}.creditContractDate`}
                                      value={(dateField.value as string) ?? ""}
                                      onChange={dateField.onChange}
                                    />
                                  )}
                                />
                              </div>

                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.debtAmount`}
                                >
                                  Сумма долга
                                </Label>
                                <Input
                                  id={`pre_court.preCourtCreditors.${index}.debtAmount`}
                                  {...register(
                                    `pre_court.preCourtCreditors.${index}.debtAmount`
                                  )}
                                />
                              </div>

                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.principalAmount`}
                                >
                                  Основной долг
                                </Label>
                                <Input
                                  id={`pre_court.preCourtCreditors.${index}.principalAmount`}
                                  {...register(
                                    `pre_court.preCourtCreditors.${index}.principalAmount`
                                  )}
                                />
                              </div>

                              <div className="space-y-2">
                                <Label
                                  htmlFor={`pre_court.preCourtCreditors.${index}.financialSanctions`}
                                >
                                  Финансовые санкции
                                </Label>
                                <Input
                                  id={`pre_court.preCourtCreditors.${index}.financialSanctions`}
                                  {...register(
                                    `pre_court.preCourtCreditors.${index}.financialSanctions`
                                  )}
                                />
                              </div>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
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
                          <SelectTrigger id="pre_court.court">
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
                    <Label htmlFor="pre_court.caseNumber">Номер дела</Label>
                    <Input
                        id="pre_court.caseNumber"
                        type="text"
                        placeholder="А56-12345/2024"
                        {...register("pre_court.caseNumber")}
                    />
                  </div>

                  <div className="space-y-1">
                    <Label htmlFor="pre_court.powerOfAttorneyNumber">Доверенность</Label>
                    <div className="grid grid-cols-2 gap-2">
                      <div className="space-y-1">
                        <Input
                          id="pre_court.powerOfAttorneyNumber"
                          type="text"
                          placeholder="ДГ-2024-001234"
                          {...register("pre_court.powerOfAttorneyNumber")}
                        />
                      </div>
                      <Controller
                        name="pre_court.powerOfAttorneyDate"
                        control={control}
                        render={({ field }) => (
                          <DatePickerInput
                            id="pre_court.powerOfAttorneyDate"
                            name="pre_court.powerOfAttorneyDate"
                            value={(field.value as string) ?? ""}
                            onChange={field.onChange}
                            className="space-y-1"
                          />
                        )}
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="pre_court.hearingDateTime">Дата и время заседания</Label>
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
                              id="pre_court.hearingDateTime"
                              name="pre_court.hearingDateTime"
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
                                id="pre_court.hearingDateTime_time"
                                value={time}
                                placeholder="15:30"
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
                        Кабинет
                      </Label>
                      <Input
                        id="pre_court.efrsbCabinet"
                        placeholder="https://cabinet.fedresurs.ru/Person/12345678"
                        {...register("pre_court.efrsbCabinet")}
                      />
                    </div>
                  )}
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>

          <DocumentsList
            documents={documents}
            title="Документы досудебного этапа:"
            formValues={watch()}
            onDocumentClick={openDocument}
            onDownload={onDownload}
            onNavigateToField={onNavigateToField}
          />
        </CardContent>
      </Card>
    </TabsContent>
  );
};
