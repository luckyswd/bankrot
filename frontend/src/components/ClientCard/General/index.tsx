import { useContext } from "react";
import React from "react";
import { useFormContext, useWatch } from "react-hook-form";

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { TabsContent } from "@/components/ui/tabs";
import { FormValues } from "../types";
import { DocumentsList } from "../DocumentsList";
import { MainInfo } from "./MainInfo";
import { Accordion } from "@/components/ui/accordion";
import { AddressInfo } from "./AddressInfo";
import { PassportInfo } from "./Passport";
import { FamilyInfo } from "./FamilyInfo";
import { WorkInfo } from "./WorkInfo";
import { ContactInfo } from "./ContactInfo";
import { DeptsInfo } from "./DeptsInfo";

export const SaveContext = React.createContext<(() => Promise<void>) | null>(
  null
);
export const useSaveContract = () => useContext(SaveContext);

interface GeneralTabProps {
  contractData?: Record<string, unknown> | null;
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void;
}

export const GeneralTab = ({
  contractData,
  openDocument,
  onDownload,
  onNavigateToField,
}: GeneralTabProps): JSX.Element => {
  const { register, control, watch } = useFormContext<FormValues>();
  const formValues = watch();

  const documents =
    (
      contractData?.basic_info as {
        documents?: Array<{ id: number; name: string }>;
      }
    )?.documents || [];
  
  return (
    <TabsContent value="primary" className="space-y-6">
      <Card>
        <CardHeader>
          <div>
            <CardTitle>Основная информация</CardTitle>
            <CardDescription>Заполните все необходимые поля</CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          <div className="space-y-6">
            <Accordion type="multiple" defaultValue={["mainInfo", "addressInfo", "passportInfo", "familyInfo", "workInfo", "contactInfo", "deptsInfo"]}>
              <MainInfo
                register={register}
                useWatch={useWatch}
                control={control}
              />
              <AddressInfo register={register} />
              <PassportInfo register={register} control={control} />
              <FamilyInfo
                register={register}
                control={control}
                useWatch={useWatch}
                watch={watch}
              />
              <WorkInfo register={register} control={control} />
              <ContactInfo register={register} />
              <DeptsInfo register={register} control={control} />
            </Accordion>
          </div>
            <DocumentsList
              documents={documents}
              title="Документы основной информации:"
              category="basic_info"
              formValues={formValues}
              onDocumentClick={openDocument}
              onDownload={onDownload}
              onNavigateToField={onNavigateToField}
            />
        </CardContent>
      </Card>
    </TabsContent>
  );
};
