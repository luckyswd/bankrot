import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { IntroductionTab } from "./Introduction";
import { ProcedureTab } from "./Procedure";
import { RealizationTab } from "./Realization";
import { ReportTab } from "./Report";

import { ReferenceData } from "@/context/AppContext"

interface JudicialTabProps {
  openDocument: (document: { id: number; name: string }) => void
  referenceData?: ReferenceData
  contractData?: Record<string, unknown> | null
}

export const JudicialTab = ({ openDocument, referenceData, contractData }: JudicialTabProps): JSX.Element => {
  return (
    <TabsContent value="judicial" className="mt-0 p-0 border-0">
      <Tabs defaultValue="realization">
        <TabsList className="grid w-full grid-cols-4 h-11 rounded-t-none border-t-0 bg-muted">
          <TabsTrigger value="realization">Реализация</TabsTrigger>
          <TabsTrigger value="introduction">Введение процедуры</TabsTrigger>
          <TabsTrigger value="procedure">Процедура</TabsTrigger>
          <TabsTrigger value="report">Отчет</TabsTrigger>
        </TabsList>

        <div className="mt-6">
          <RealizationTab openDocument={openDocument} contractData={contractData} />
          <IntroductionTab openDocument={openDocument} referenceData={referenceData} contractData={contractData} />
          <ProcedureTab openDocument={openDocument} contractData={contractData} />
          <ReportTab openDocument={openDocument} contractData={contractData} />
        </div>
      </Tabs>
    </TabsContent>
  );
};
