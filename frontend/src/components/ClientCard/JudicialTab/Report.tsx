import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { TabsContent } from "@/components/ui/tabs"
import { DocumentsList } from "../DocumentsList"

interface ReportTabProps {
  openDocument: (document: { id: number; name: string }) => void
  contractData?: Record<string, unknown> | null
}

export const ReportTab = ({ openDocument, contractData }: ReportTabProps): JSX.Element => {
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
            onDocumentClick={openDocument}
          />
        </CardContent>
      </Card>
    </TabsContent>
  )
}
