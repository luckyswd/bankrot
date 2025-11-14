import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { TabsContent } from "@/components/ui/tabs"
import { DocumentsList } from "../DocumentsList"

interface RealizationTabProps {
  openDocument: (document: { id: number; name: string }) => void
  contractData?: Record<string, unknown> | null
}

export const RealizationTab = ({ openDocument, contractData }: RealizationTabProps): JSX.Element => {
  const documents = (contractData?.realization as { documents?: Array<{ id: number; name: string }> })?.documents || []

  return (
    <TabsContent value="realization" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Реализация</CardTitle>
          <CardDescription>Данные по реализации имущества</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <p className="text-sm text-muted-foreground">
              Раздел в разработке
            </p>
          </div>
          <DocumentsList
            documents={documents}
            title="Документы этапа реализации:"
            onDocumentClick={openDocument}
          />
        </CardContent>
      </Card>
    </TabsContent>
  )
}
