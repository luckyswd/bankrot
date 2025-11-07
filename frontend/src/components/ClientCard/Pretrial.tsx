import { FileText } from "lucide-react";

import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { TabsContent } from "@/components/ui/tabs";
import { DatePickerInput } from "@/components/ui/DatePickerInput";

type FieldAccessor = (path: string) => unknown;
type FieldUpdater = (path: string, value: unknown) => void;

interface ReferenceItem {
  id: number | string;
  name: string;
}

interface PretrialTabProps {
  handleChange: FieldUpdater;
  getValue: FieldAccessor;
  openDocument: (docType: string) => void;
  databases?: {
    courts?: ReferenceItem[];
  };
}

export const PretrialTab = ({
  handleChange,
  getValue,
  openDocument,
  databases,
}: PretrialTabProps) => {
  const getStringValue = (path: string) => (getValue(path) as string) || "";

  return (
    <TabsContent value="pretrial" className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Досудебка</CardTitle>
          <CardDescription>Информация о досудебном этапе</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label>1. Арбитражный суд</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("pretrial.court")}
                onChange={(e) => handleChange("pretrial.court", e.target.value)}
                list="courts-list"
              />
              <datalist id="courts-list">
                {databases?.courts?.map((court) => (
                  <option key={court.id} value={court.name} />
                ))}
              </datalist>
            </div>

            <div className="space-y-2">
              <Label>2. Кредиторы</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("pretrial.creditors")}
                onChange={(e) =>
                  handleChange("pretrial.creditors", e.target.value)
                }
              />
            </div>

            <div className="space-y-2">
              <Label>3. Доверенность</Label>
              <div className="grid grid-cols-2 gap-2">
                <Input
                  type="text"
                  placeholder="Номер"
                  value={getStringValue("pretrial.powerOfAttorneyNumber")}
                  onChange={(e) =>
                    handleChange(
                      "pretrial.powerOfAttorneyNumber",
                      e.target.value
                    )
                  }
                />
                             <DatePickerInput
                value={getStringValue("pretrial.powerOfAttorneyDate")}
                onChange={(next) =>
                  handleChange("pretrial.powerOfAttorneyDate", next)
                }
                className="space-y-1"
              />
              </div>
            </div>

            <div className="space-y-2">
              <Label>4. Кредитор</Label>
              <Input
                placeholder="Выбор из списка"
                value={getStringValue("pretrial.creditor")}
                onChange={(e) =>
                  handleChange("pretrial.creditor", e.target.value)
                }
              />
            </div>

            <div className="space-y-2">
              <Label>5. № Дела</Label>
              <Input
                value={getStringValue("pretrial.caseNumber")}
                onChange={(e) =>
                  handleChange("pretrial.caseNumber", e.target.value)
                }
              />
            </div>

            <div className="space-y-2">
              <Label>6. Дата и время заседания</Label>
              <div className="grid grid-cols-2 gap-2">
                <DatePickerInput
                  value={getStringValue("pretrial.hearingDate")}
                  onChange={(next) =>
                    handleChange("pretrial.hearingDate", next)
                  }
                  className="space-y-1"
                />
                <div className="space-y-1">
                  <Input
                    type="time"
                    value={getStringValue("pretrial.hearingTime")}
                    onChange={(e) =>
                      handleChange("pretrial.hearingTime", e.target.value)
                    }
                  />
                </div>
              </div>
            </div>
          </div>

          <Separator className="my-6" />

          <div className="space-y-3">
            <h4 className="font-semibold">Документы досудебного этапа:</h4>
            <Button
              onClick={() => openDocument("bankruptcyApplication")}
              variant="outline"
              className="w-full justify-start"
            >
              <FileText className="mr-2 h-4 w-4" />
              Заявление о признании банкротом
            </Button>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  );
};
