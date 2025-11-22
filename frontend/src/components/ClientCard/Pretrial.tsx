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
    name: "pretrial.creditors" as any,
  });
  const watchedCreditors = watch("pretrial.creditors") ?? [];
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

  return (
    <TabsContent value="pretrial" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Досудебка</CardTitle>
          <CardDescription>Информация о досудебном этапе</CardDescription>
        </CardHeader>
        <CardContent>
          <Accordion
            type="single"
            collapsible
            className="mb-6"
            defaultValue="creditorsInfo"
          >
            <AccordionItem value="creditorsInfo">
              <AccordionTrigger>
                <h3 className="text-xl font-semibold">Кредиторы</h3>
              </AccordionTrigger>
              <AccordionContent>
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <Label htmlFor="pretrial.creditors" className="font-medium">
                      Кредиторы (можно несколько)
                    </Label>
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
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

                  {creditorFields.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                      Список пуст. Добавьте кредитора.
                    </p>
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
                              <Label htmlFor={`pretrial.creditors.${index}`}>
                                Кредитор {index + 1}
                              </Label>
                              <Controller
                                name={`pretrial.creditors.${index}`}
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
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="pretrial.court">Арбитражный суд</Label>
              <Controller
                name="pretrial.court"
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
                        <SelectItem key={court.id} value={String(court.id)}>
                          {court.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
              />
            </div>

            <div className="space-y-2">
              <Label>3. Доверенность</Label>
              <div className="grid grid-cols-2 gap-2">
                <Input
                  type="text"
                  placeholder="Номер"
                  {...register("pretrial.powerOfAttorneyNumber")}
                />
                <Controller
                  name="pretrial.powerOfAttorneyDate"
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
              <div className="grid grid-cols-2 gap-2">
                <Controller
                  name="pretrial.hearingDate"
                  control={control}
                  render={({ field }) => {
                    console.log(field)
                    return (
                    <DatePickerInput
                      value={(field.value as string) ?? ""}
                      onChange={field.onChange}
                      className="space-y-1"
                    />
                  )
                  }}
                />
                <div className="space-y-1">
                  <Input type="time" {...register("pretrial.hearingTime")} />
                </div>
              </div>
            </div>
          </div>

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
