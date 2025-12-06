import { Controller, useFormContext } from "react-hook-form";

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
import { FormValues } from "../types";
import { DocumentsList } from "../DocumentsList";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Trash2 } from "lucide-react";

import type { ReferenceData } from "@/types/reference";

interface IntroductionTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
}

export const IntroductionTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
}: IntroductionTabProps): JSX.Element => {
  const { register, control } = useFormContext<FormValues>();
  const toIdString = (value: unknown) => {
    if (typeof value === "string" || typeof value === "number") {
      return String(value);
    }
    if (value && typeof value === "object" && "id" in (value as any)) {
      return String((value as any).id);
    }
    return "";
  };

  const documents =
    (
      contractData?.judicial_procedure_initiation as {
        documents?: Array<{ id: number; name: string }>;
      }
    )?.documents || [];

  return (
    <TabsContent value="introduction" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Введение процедуры</CardTitle>
          <CardDescription>
            Данные для введения процедуры банкротства
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Controller
                name="judicial_procedure_initiation.procedureInitiationDecisionDate"
                control={control}
                render={({ field }) => (
                  <DatePickerInput
                    label="Дата принятия судебного решения"
                    value={(field.value as string) ?? ""}
                    onChange={field.onChange}
                  />
                )}
              />
            </div>
                            <div className="space-y-2">
              <Controller
                name="judicial_procedure_initiation.procedureInitiationResolutionDate"
                control={control}
                render={({ field }) => (
                  <DatePickerInput
                    label="Дата объявления резолютивной части судебного решения"
                    value={(field.value as string) ?? ""}
                    onChange={field.onChange}
                  />
                )}
              />
            </div>

            <Controller
              name="judicial_procedure_initiation.procedureInitiationMchs"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationMchs">
                    ГИМС
                  </Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationMchs">
                      <SelectValue placeholder="Выберите ГИМС" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.mchs?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            />

            <Controller
              name="judicial_procedure_initiation.procedureInitiationGostekhnadzor"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationGostekhnadzor">
                    Гостехнадзор
                  </Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationGostekhnadzor">
                      <SelectValue placeholder="Выберите Гостехнадзор" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.gostekhnadzor?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            />

            <Controller
              name="judicial_procedure_initiation.procedureInitiationFns"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationFns">ФНС</Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationFns">
                      <SelectValue placeholder="Выберите ФНС" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.fns?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            />

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationDocNumber">
                Номер документа
              </Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationDocNumber"
                placeholder="Укажите номер"
                {...register("judicial_procedure_initiation.procedureInitiationDocNumber")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationCaseNumber">Номер дела</Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationCaseNumber"
                {...register("judicial_procedure_initiation.procedureInitiationCaseNumber")}
              />
            </div>

            <Controller
              name="judicial_procedure_initiation.procedureInitiationRosgvardia"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationRosgvardia">
                    Росгвардия
                  </Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationRosgvardia">
                      <SelectValue placeholder="Выберите Росгвардию" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.rosgvardia?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            />

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationJudge">Судья</Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationJudge"
                {...register("judicial_procedure_initiation.procedureInitiationJudge")}
              />
            </div>

            <Controller
              name="judicial_procedure_initiation.procedureInitiationBailiff"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationBailiff">
                    Судебный пристав
                  </Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationBailiff">
                      <SelectValue placeholder="Выберите пристава" />
                    </SelectTrigger>
                    <SelectContent>
                      {referenceData?.bailiffs?.map((item) => (
                        <SelectItem key={item.id} value={String(item.id)}>
                          {item.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            />
{/* 
            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationDuration">
                Продолжительность процедуры
              </Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationDuration"
                placeholder="Укажите срок"
                {...register(
                  "judicial_procedure_initiation.procedureInitiationDuration"
                )}
              />
            </div>

            <div className="space-y-2 md:col-span-2">
              <Label>Окончание исполнительных производств</Label>
              <div className="grid grid-cols-2 gap-2">
                <Input
                  placeholder="Номер"
                  {...register("judicial_procedure_initiation.executionNumber")}
                />
                <Controller
                  name="judicial_procedure_initiation.executionDate"
                  control={control}
                  render={({ field }) => (
                    <DatePickerInput
                      value={(field.value as string) ?? ""}
                      onChange={field.onChange}
                      className="space-y-1 flex-1"
                    />
                  )}
                />
              </div>
            </div> */}

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationSpecialAccountNumber">
                Номер спец счёта
              </Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationSpecialAccountNumber"
                {...register("judicial_procedure_initiation.procedureInitiationSpecialAccountNumber")}
              />
            </div>

            <div className="col-span-full space-y-3">
              <div className="flex items-center justify-between">
                <Label className="font-medium">
                  Окончания исполнительных производств (можно несколько)
                </Label>
              </div>

              <Controller
                control={control}
                name="judicial_procedure_initiation.procedureInitiationIPEndings"
                render={({ field: executionTerminationsField }) => {
                  const executionTerminationsArray = executionTerminationsField.value ?? [];

                  return (
                    <>
                      {executionTerminationsArray.length === 0 ? (
                        <div className="space-y-2">
                          <Label>Окончание 1</Label>
                          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Input
                              placeholder="Номер"
                              onChange={(e) => {
                                const currentTermination = executionTerminationsArray.length > 0 ? executionTerminationsArray[0] : {
                                  number: "",
                                  date: ""
                                };
                                executionTerminationsField.onChange([
                                  { ...currentTermination, number: e.target.value },
                                ]);
                              }}
                            />
                            <DatePickerInput
                              placeholder="Дата"
                              onChange={(value) => {
                                const currentTermination = executionTerminationsArray.length > 0 ? executionTerminationsArray[0] : {
                                  number: "",
                                  date: ""
                                };
                                executionTerminationsField.onChange([
                                  { ...currentTermination, date: value },
                                ]);
                              }}
                            />
                          </div>
                        </div>
                      ) : (
                        <div className="space-y-3">
                          {executionTerminationsArray.map(
                            (
                              terminationItem: { number: string; date: string },
                              terminationIndex: number
                            ) => (
                              <div
                                key={terminationIndex}
                                className="flex items-end gap-3"
                              >
                                <div className="flex-1 space-y-2">
                                  <Label>
                                    Окончание {terminationIndex + 1}
                                  </Label>
                                  <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <Input
                                      value={terminationItem.number ?? ""}
                                      placeholder="Номер"
                                      onChange={(e) => {
                                        const newTerminations = [
                                          ...executionTerminationsArray,
                                        ];
                                        newTerminations[terminationIndex] = {
                                          ...terminationItem,
                                          number: e.target.value,
                                        };
                                        executionTerminationsField.onChange(newTerminations);
                                      }}
                                    />
                                    <DatePickerInput
                                      value={terminationItem.date ?? ""}
                                      placeholder="Дата"
                                      onChange={(value) => {
                                        const newTerminations = [
                                          ...executionTerminationsArray,
                                        ];
                                        newTerminations[terminationIndex] = {
                                          ...terminationItem,
                                          date: value,
                                        };
                                        executionTerminationsField.onChange(newTerminations);
                                      }}
                                    />
                                  </div>
                                </div>
                                <Button
                                  type="button"
                                  variant="ghost"
                                  size="icon"
                                  onClick={() => {
                                    const newTerminations = executionTerminationsArray.filter(
                                      (_: unknown, i: number) =>
                                        i !== terminationIndex
                                    );
                                    executionTerminationsField.onChange(newTerminations);
                                  }}
                                  aria-label={`Удалить окончание ${terminationIndex + 1}`}
                                >
                                  <Trash2 className="h-4 w-4" />
                                </Button>
                              </div>
                            )
                          )}
                        </div>
                      )}

                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => {
                          const currentTerminations = executionTerminationsField.value ?? [];
                          executionTerminationsField.onChange([
                            ...currentTerminations,
                            { number: "", date: "" },
                          ]);
                        }}
                      >
                        Добавить окончание
                      </Button>
                    </>
                  );
                }}
              />
            </div>
          </div>

          <DocumentsList
            documents={documents}
            title="Документы этапа введения:"
            onDocumentClick={openDocument}
            onDownload={onDownload}
          />
        </CardContent>
      </Card>
    </TabsContent>
  );
};
