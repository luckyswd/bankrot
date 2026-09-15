import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { TabsContent } from "@/components/ui/tabs"
import { FinancialManagerInsuranceAlert } from "@/components/shared/InsuranceStatusAlert"
import { DocumentsList } from "../DocumentsList"
import { useFormContext } from "react-hook-form"
import { FormValues } from "../types"

import type { ReferenceData } from "@/types/reference"

interface ReportTabProps {
  openDocument: (document: { id: number; name: string }) => void
  onDownload: (document: { id: number; name: string }) => void
  referenceData?: ReferenceData
  contractData?: Record<string, unknown> | null
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void
}

const toManagerId = (value: unknown): string | null => {
  if (typeof value === "string" || typeof value === "number") {
    return String(value)
  }

  if (value && typeof value === "object" && "id" in value) {
    return String((value as { id: number }).id)
  }

  return null
}

export const ReportTab = ({ openDocument, onDownload, referenceData, contractData, onNavigateToField }: ReportTabProps): JSX.Element => {
  const { watch } = useFormContext<FormValues>()
  const formValues = watch()
  const documents = (contractData?.judicial_report as { documents?: Array<{ id: number; name: string }> })?.documents || []

  return (
    <TabsContent value="judicial_report" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Отчет</CardTitle>
          <CardDescription>Отчеты финансового управляющего</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <FinancialManagerInsuranceAlert
            managerId={toManagerId(formValues.basic_info?.manager)}
            financialManagers={referenceData?.financialManagers}
          />
          <DocumentsList
            documents={documents}
            title="Документы отчетов:"
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
