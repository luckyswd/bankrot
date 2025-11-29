import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { IntroductionTab } from "./Introduction";
import { ProcedureTab } from "./Procedure";
import { ReportTab } from "./Report";

import type { ReferenceData } from "@/types/reference";

interface JudicialTabProps {
  openDocument: (document: { id: number; name: string }) => void;
  onDownload: (document: { id: number; name: string }) => void;
  referenceData?: ReferenceData;
  contractData?: Record<string, unknown> | null;
}

export const JudicialTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
}: JudicialTabProps): JSX.Element => {
  return (
    <TabsContent value="judicial" className="mt-0 p-0 border-0">
      <Tabs defaultValue="introduction">
        <TabsList className="grid w-full grid-cols-3 h-11 rounded-t-none border-t-0 bg-muted">
          <TabsTrigger value="introduction">Введение процедуры</TabsTrigger>
          <TabsTrigger value="procedure">Процедура</TabsTrigger>
          <TabsTrigger value="report">Отчет</TabsTrigger>
        </TabsList>

        <div className="mt-6">
          <IntroductionTab
            openDocument={openDocument}
            onDownload={onDownload}
            referenceData={referenceData}
            contractData={contractData}
          />
          <ProcedureTab
            openDocument={openDocument}
            onDownload={onDownload}
            referenceData={referenceData}
            contractData={contractData}
          />
          <ReportTab
            openDocument={openDocument}
            onDownload={onDownload}
            contractData={contractData}
          />
        </div>
      </Tabs>
    </TabsContent>
  );
};
