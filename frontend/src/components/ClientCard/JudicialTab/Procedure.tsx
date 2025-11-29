import { useFormContext } from "react-hook-form";

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

interface ProcedureTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
}

export const ProcedureTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
}: ProcedureTabProps): JSX.Element => {
  const { register } = useFormContext<FormValues>();

  const documents =
    (
      contractData?.judicial_procedure as {
        documents?: Array<{ id: number; name: string }>;
      }
    )?.documents || [];

  return (
    <TabsContent value="judicial_procedure" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>2. Процедура</CardTitle>
          <CardDescription>Данные процедуры реализации</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="judicial_procedure.creditorRequirement">
                1. Требование кредитора
              </Label>
              <Input
                id="judicial_procedure.creditorRequirement"
                placeholder="Выбор из списка"
                list="creditors-list"
                {...register("judicial_procedure.creditorRequirement")}
              />
              <datalist id="creditors-list">
                {referenceData?.creditors?.map((item) => (
                  <option key={item.id} value={item.name} />
                ))}
              </datalist>
              <p className="text-xs text-muted-foreground">
                Наименование кредитора, ОГРН, ИНН, адрес
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure.receivedRequirements">
                2. Полученные требования кредитора
              </Label>
              <Input
                id="judicial_procedure.receivedRequirements"
                placeholder="Выбор из списка"
                list="creditors-list"
                {...register("judicial_procedure.receivedRequirements")}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="judicial_procedure.principalAmount">
                3. Основная сумма
              </Label>
              <Input
                id="judicial_procedure.principalAmount"
                type="number"
                {...register("judicial_procedure.principalAmount")}
              />
            </div>
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
