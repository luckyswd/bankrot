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
              name="judicial_procedure_initiation.procedureInitiationRoszdrav"
              control={control}
              render={({ field }) => (
                <div className="space-y-2">
                  <Label htmlFor="judicial_procedure_initiation.procedureInitiationRoszdrav">
                    Росздрав
                  </Label>
                  <Select
                    value={toIdString(field.value)}
                    onValueChange={field.onChange}
                  >
                    <SelectTrigger id="judicial_procedure_initiation.procedureInitiationRoszdrav">
                      <SelectValue placeholder="Выберите Росздрав" />
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
