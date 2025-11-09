import { useFormContext } from "react-hook-form"
import { FileText } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { TabsContent } from "@/components/ui/tabs"

import type { FormValues } from "./index"

interface ReportTabProps {
  openDocument: (docType: string) => void
}

export const ReportTab = ({ openDocument }: ReportTabProps) => {
  const { register } = useFormContext<FormValues>()

  return (
    <TabsContent value="report" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Отчет</CardTitle>
          <CardDescription>Отчеты финансового управляющего</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <FileText className="h-4 w-4 text-muted-foreground" />
              <Button onClick={() => openDocument("financialReport")} variant="outline" className="justify-start" size="sm">
                Отчет финансового управляющего
              </Button>
            </div>
            <p className="text-sm text-muted-foreground">
              Раздел в разработке
            </p>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  )
}

