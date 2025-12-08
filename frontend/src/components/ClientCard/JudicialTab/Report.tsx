import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { TabsContent } from "@/components/ui/tabs"
import { DocumentsList } from "../DocumentsList"
import { useFormContext } from "react-hook-form"
import { FormValues } from "../types"

interface ReportTabProps {
  openDocument: (document: { id: number; name: string }) => void
  onDownload: (document: { id: number; name: string }) => void
  contractData?: Record<string, unknown> | null
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void
}

export const ReportTab = ({ openDocument, onDownload, contractData, onNavigateToField }: ReportTabProps): JSX.Element => {
  const { watch } = useFormContext<FormValues>();
  const formValues = watch();
  const documents = (contractData?.report as { documents?: Array<{ id: number; name: string }> })?.documents || []

  return (
    <TabsContent value="report" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Отчет</CardTitle>
          <CardDescription>Отчеты финансового управляющего</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <p className="text-sm text-muted-foreground">
              Раздел в разработке
            </p>
          </div>
          <DocumentsList
            documents={documents}
            title="Документы отчетов:"
            category="judicial_report"
            formValues={formValues}
            onDocumentClick={openDocument}
            onDownload={onDownload}
            onNavigateToField={onNavigateToField}
          />
        </CardContent>
      </Card>
    </TabsContent>
  )
}
