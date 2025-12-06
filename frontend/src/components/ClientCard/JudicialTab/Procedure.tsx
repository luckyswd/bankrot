import { Controller, useFieldArray, useFormContext, useWatch } from "react-hook-form";

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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { DatePickerInput } from "@/components/ui/DatePickerInput";
import { Switch } from "@/components/ui/switch";
import { Trash2 } from "lucide-react";
import { FormValues } from "../types";
import { DocumentsList } from "../DocumentsList";
import type { ReferenceData } from "@/types/reference";

interface ProcedureTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
}

export const ProcedureTab = ({
  openDocument,
  onDownload,
  contractData,
  referenceData,
}: ProcedureTabProps): JSX.Element => {
  const { register, control, setValue } = useFormContext<FormValues>();

  const {
    fields: creditorsClaimsFields,
    append: appendCreditorsClaim,
    remove: removeCreditorsClaim,
  } = useFieldArray<FormValues, "judicial_procedure.creditorsClaims">({
    control,
    name: "judicial_procedure.creditorsClaims",
  });

  const documents =
    (
      contractData?.judicial_procedure as {
        documents?: Array<{ id: number; name: string }>;
      }
    )?.documents || [];

  const createEmptyCreditorsClaim = () => ({
    creditorId: 0,
    debtAmount: null,
    principalAmount: null,
    interest: null,
    penalty: null,
    lateFee: null,
    forfeiture: null,
    stateDuty: null,
    basis: [],
    inclusion: null,
    isCreditCard: null,
    creditCardDate: null,
    judicialActDate: null,
  });

  return (
    <TabsContent value="procedure" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Процедура</CardTitle>
          <CardDescription>
            Данные для введения процедуры банкротства
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="mt-6 space-y-4">
            <div className="flex items-center justify-between">
              <Label className="text-lg font-semibold">
                Требования кредиторов
              </Label>
              <Button
                type="button"
                variant="outline"
                onClick={() => appendCreditorsClaim(createEmptyCreditorsClaim())}
              >
                Добавить требование
              </Button>
            </div>

            {creditorsClaimsFields.map((field, index) => (
              <Card key={field.id}>
                <CardContent className="pt-6">
                  <div className="space-y-4">
                    <div className="flex items-center justify-between">
                      <Label className="text-base font-semibold">
                        Требование #{index + 1}
                      </Label>
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        onClick={() => removeCreditorsClaim(index)}
                      >
                        Удалить
                      </Button>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.creditorId`}
                        >
                          Кредитор
                        </Label>
                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.creditorId`}
                            render={({field: selectField}) => (
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
                                      id={`judicial_procedure.creditorsClaims.${index}.creditorId`}
                                  >
                                    <SelectValue placeholder="Выберите кредитора"/>
                                  </SelectTrigger>
                                  <SelectContent>
                                    {referenceData?.creditors?.map((item) => (
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
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.debtAmount`}
                        >
                          Сумма долга
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.debtAmount`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.debtAmount`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.principalAmount`}
                        >
                          Основная сумма
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.principalAmount`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.principalAmount`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.interest`}
                        >
                          Процент
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.interest`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.interest`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.penalty`}
                        >
                          Штраф
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.penalty`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.penalty`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.lateFee`}
                        >
                          Пеня
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.lateFee`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.lateFee`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.forfeiture`}
                        >
                          Неустойка
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.forfeiture`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.forfeiture`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.stateDuty`}
                        >
                          Госпошлина
                        </Label>
                        <Input
                            id={`judicial_procedure.creditorsClaims.${index}.stateDuty`}
                            {...register(
                                `judicial_procedure.creditorsClaims.${index}.stateDuty`
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.creditCardDate`}
                        >
                          Дата кредитной карты
                        </Label>
                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.creditCardDate`}
                            render={({field: creditCardDateField}) => (
                                <DatePickerInput
                                    id={`judicial_procedure.creditorsClaims.${index}.creditCardDate`}
                                    value={creditCardDateField.value ?? ""}
                                    placeholder="Выберите дату"
                                    onChange={creditCardDateField.onChange}
                                />
                            )}
                        />
                      </div>

                      <div className="space-y-2">
                        <Label
                            htmlFor={`judicial_procedure.creditorsClaims.${index}.judicialActDate`}
                        >
                          Дата конкретного судебного акта
                        </Label>
                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.judicialActDate`}
                            render={({field: judicialActDateField}) => (
                                <DatePickerInput
                                    id={`judicial_procedure.creditorsClaims.${index}.judicialActDate`}
                                    value={judicialActDateField.value ?? ""}
                                    placeholder="Выберите дату"
                                    onChange={judicialActDateField.onChange}
                                />
                            )}
                        />
                      </div>

                      <div className="space-y-2 flex items-center gap-2">
                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.isCreditCard`}
                            render={({field: isCreditCardField}) => (
                                <>
                                  <Switch
                                      id={`judicial_procedure.creditorsClaims.${index}.isCreditCard`}
                                      checked={isCreditCardField.value ?? false}
                                      onCheckedChange={isCreditCardField.onChange}
                                  />
                                  <Label
                                      htmlFor={`judicial_procedure.creditorsClaims.${index}.isCreditCard`}
                                      className="cursor-pointer"
                                  >
                                    Кредитная карта
                                  </Label>
                                </>
                            )}
                        />
                      </div>

                      <div className="space-y-2 flex items-center gap-2">
                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.inclusion`}
                            render={({field: inclusionField}) => (
                                <>
                                  <Switch
                                      id={`judicial_procedure.creditorsClaims.${index}.inclusion`}
                                      checked={inclusionField.value ?? false}
                                      onCheckedChange={inclusionField.onChange}
                                  />
                                  <Label
                                      htmlFor={`judicial_procedure.creditorsClaims.${index}.inclusion`}
                                      className="cursor-pointer"
                                  >
                                    Требования получено
                                  </Label>
                                </>
                            )}
                        />
                      </div>

                      <div className="col-span-full space-y-3">
                        <div className="flex items-center justify-between">
                          <Label className="font-medium">
                            Основания (можно несколько)
                          </Label>
                        </div>

                        <Controller
                            control={control}
                            name={`judicial_procedure.creditorsClaims.${index}.basis`}
                            render={({field: basisField}) => {
                              const basisArray = basisField.value ?? [];

                              return (
                                  <>
                                    {basisArray.length === 0 ? (
                                        <div className="space-y-2">
                                          <Label>Основание 1</Label>
                                          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <Input
                                                placeholder="Номер"
                                                onChange={(e) => {
                                                  const currentBasis = basisArray.length > 0 ? basisArray[0] : {
                                                    number: "",
                                                    date: ""
                                                  };
                                                  basisField.onChange([
                                                    {...currentBasis, number: e.target.value},
                                                  ]);
                                                }}
                                            />
                                            <DatePickerInput
                                                placeholder="Дата"
                                                onChange={(value) => {
                                                  const currentBasis = basisArray.length > 0 ? basisArray[0] : {
                                                    number: "",
                                                    date: ""
                                                  };
                                                  basisField.onChange([
                                                    {...currentBasis, date: value},
                                                  ]);
                                                }}
                                            />
                                          </div>
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                          {basisArray.map(
                                              (
                                                  basisItem: { number: string; date: string },
                                                  basisIndex: number
                                              ) => (
                                                  <div
                                                      key={basisIndex}
                                                      className="flex items-end gap-3"
                                                  >
                                                    <div className="flex-1 space-y-2">
                                                      <Label>
                                                        Основание {basisIndex + 1}
                                                      </Label>
                                                      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                        <Input
                                                            value={basisItem.number ?? ""}
                                                            placeholder="Номер"
                                                            onChange={(e) => {
                                                              const newBasis = [
                                                                ...basisArray,
                                                              ];
                                                              newBasis[basisIndex] = {
                                                                ...basisItem,
                                                                number: e.target.value,
                                                              };
                                                              basisField.onChange(newBasis);
                                                            }}
                                                        />
                                                        <DatePickerInput
                                                            value={basisItem.date ?? ""}
                                                            placeholder="Дата"
                                                            onChange={(value) => {
                                                              const newBasis = [
                                                                ...basisArray,
                                                              ];
                                                              newBasis[basisIndex] = {
                                                                ...basisItem,
                                                                date: value,
                                                              };
                                                              basisField.onChange(newBasis);
                                                            }}
                                                        />
                                                      </div>
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => {
                                                          const newBasis = basisArray.filter(
                                                              (_: unknown, i: number) =>
                                                                  i !== basisIndex
                                                          );
                                                          basisField.onChange(newBasis);
                                                        }}
                                                        aria-label={`Удалить основание ${basisIndex + 1}`}
                                                    >
                                                      <Trash2 className="h-4 w-4"/>
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
                                          const currentBasis = basisField.value ?? [];
                                          basisField.onChange([
                                            ...currentBasis,
                                            {number: "", date: ""},
                                          ]);
                                        }}
                                    >
                                      Добавить основание
                                    </Button>
                                  </>
                              );
                            }}
                        />
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          <DocumentsList
              documents={documents}
              title="Документы процедуры:"
              onDocumentClick={openDocument}
              onDownload={onDownload}
          />
        </CardContent>
      </Card>
    </TabsContent>
  );
};
