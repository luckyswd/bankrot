import { useFormContext } from "react-hook-form"
import { FileText } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { TabsContent } from "@/components/ui/tabs"

import type { FormValues } from "./index"

interface ReferenceItem {
  id: number | string
  name: string
}

interface RealizationTabProps {
  openDocument: (docType: string) => void
  databases?: {
    fns?: ReferenceItem[]
  }
}

export const RealizationTab = ({ openDocument, databases }: RealizationTabProps) => {
  const { register } = useFormContext<FormValues>()

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
        </CardContent>
      </Card>
    </TabsContent>
  )
}

