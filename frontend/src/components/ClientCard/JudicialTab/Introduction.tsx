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
                name="judicial_procedure_initiation.courtDecisionDate"
                control={control}
                render={({ field }) => (
                  <DatePickerInput
                    label="1. Дата решения суда"
                    value={(field.value as string) ?? ""}
                    onChange={field.onChange}
                  />
                )}
              />
              <p className="text-xs text-muted-foreground">
                Если ставится дата суда, то информация не будет отображена в
                документе
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.gims">ГИМС</Label>
              <Input
                id="judicial_procedure_initiation.gims"
                placeholder="Выбор из списка"
                {...register("judicial_procedure_initiation.gims")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.gostechnadzor">
                3. Гостехнадзор
              </Label>
              <Input
                id="judicial_procedure_initiation.gostechnadzor"
                placeholder="Выбор из списка"
                {...register("judicial_procedure_initiation.gostechnadzor")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.fns">ФНС</Label>
              <Input
                id="judicial_procedure_initiation.fns"
                placeholder="Выбор из списка"
                list="fns-list"
                {...register("judicial_procedure_initiation.fns")}
              />
              <datalist id="fns-list">
                {referenceData?.fns?.map((item) => (
                  <option key={item.id} value={item.name} />
                ))}
              </datalist>
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.documentNumber">
                Номер документа
              </Label>
              <Input
                id="judicial_procedure_initiation.documentNumber"
                placeholder="Укажите номер"
                {...register("judicial_procedure_initiation.documentNumber")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.procedureInitiationCaseNumber">Номер дела</Label>
              <Input
                id="judicial_procedure_initiation.procedureInitiationCaseNumber"
                {...register("judicial_procedure_initiation.procedureInitiationCaseNumber")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.rosaviation">Росавиация</Label>
              <Input
                id="judicial_procedure_initiation.rosaviation"
                placeholder="Выбор из списка"
                {...register("judicial_procedure_initiation.rosaviation")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.judge">Судья</Label>
              <Input
                id="judicial_procedure_initiation.judge"
                {...register("judicial_procedure_initiation.judge")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.bailiff">Судебный пристав</Label>
              <Input
                id="judicial_procedure_initiation.bailiff"
                placeholder="Выбор из списка"
                {...register("judicial_procedure_initiation.bailiff")}
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
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure_initiation.specialAccountNumber">
                Номер спец счёта
              </Label>
              <Input
                id="judicial_procedure_initiation.specialAccountNumber"
                {...register("judicial_procedure_initiation.specialAccountNumber")}
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
