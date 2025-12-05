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
    <TabsContent value="procedure" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Процедура</CardTitle>
          <CardDescription>
            Данные для введения процедуры банкротства
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="judicial_procedure.procedureMainAmount">
                Основная сумма
              </Label>
              <Input
                id="judicial_procedure.procedureMainAmount"
                {...register("judicial_procedure.procedureMainAmount")}
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
