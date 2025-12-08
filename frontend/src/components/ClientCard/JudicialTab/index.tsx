import { useSearchParams } from "react-router-dom";
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
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void;
}

export const JudicialTab = ({
  openDocument,
  onDownload,
  referenceData,
  contractData,
  onNavigateToField,
}: JudicialTabProps): JSX.Element => {
  const [searchParams, setSearchParams] = useSearchParams();
  
  // Получаем текущий вложенный таб из URL или используем значение по умолчанию
  const currentSubTab = searchParams.get("subTab") || "introduction";
  const validSubTabs = ["introduction", "procedure", "report"];
  const activeSubTab = validSubTabs.includes(currentSubTab) ? currentSubTab : "introduction";

  // Обработчик изменения вложенного таба
  const handleSubTabChange = (value: string) => {
    const newParams = new URLSearchParams(searchParams);
    newParams.set("subTab", value);
    setSearchParams(newParams, { replace: true });
  };

  return (
    <TabsContent value="judicial" className="mt-0 p-0 border-0">
      <Tabs value={activeSubTab} onValueChange={handleSubTabChange}>
        <div className="flex flex-col">
          <TabsList className="grid w-full grid-cols-3 h-12 rounded-t-none p-0 gap-0.5">
            <TabsTrigger 
              value="introduction"
              className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
            >
              Введение процедуры
            </TabsTrigger>
            <TabsTrigger 
              value="procedure"
              className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
            >
              Процедура
            </TabsTrigger>
            <TabsTrigger 
              value="report"
              className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
            >
              Отчет
            </TabsTrigger>
          </TabsList>
        </div>

        <div className="mt-6">
          <IntroductionTab
            openDocument={openDocument}
            onDownload={onDownload}
            referenceData={referenceData}
            contractData={contractData}
            onNavigateToField={onNavigateToField}
          />
          <ProcedureTab
            openDocument={openDocument}
            onDownload={onDownload}
            referenceData={referenceData}
            contractData={contractData}
            onNavigateToField={onNavigateToField}
          />
          <ReportTab
            openDocument={openDocument}
            onDownload={onDownload}
            contractData={contractData}
            onNavigateToField={onNavigateToField}
          />
        </div>
      </Tabs>
    </TabsContent>
  );
};
