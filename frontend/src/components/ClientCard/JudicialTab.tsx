import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { IntroductionTab } from "./Introduction"
import { ProcedureTab } from "./Procedure"
import { RealizationTab } from "./Realization"
import { ReportTab } from "./Report"

interface JudicialTabProps {
  openDocument: (docType: string) => void
  databases?: {
    fns?: Array<{ id: number | string; name: string }>
  }
}

export const JudicialTab = ({ openDocument, databases }: JudicialTabProps) => {
  return (
    <Tabs defaultValue="realization">
      <TabsList className="grid w-full grid-cols-4 h-11 rounded-t-none border-t-0 bg-muted">
        <TabsTrigger value="realization">Реализация</TabsTrigger>
        <TabsTrigger value="introduction">Введение процедуры</TabsTrigger>
        <TabsTrigger value="procedure">Процедура</TabsTrigger>
        <TabsTrigger value="report">Отчет</TabsTrigger>
      </TabsList>

      <div className="mt-6">
        <RealizationTab openDocument={openDocument} databases={databases} />
        <IntroductionTab openDocument={openDocument} databases={databases} />
        <ProcedureTab openDocument={openDocument} />
        <ReportTab openDocument={openDocument} />
      </div>
    </Tabs>
  )
}

