import { FileText, Eye, Download, FolderOpen, AlertCircle, CheckCircle2 } from "lucide-react";
import { Separator } from "@/components/ui/separator";
import { Tooltip, TooltipContent, TooltipTrigger } from "@ui/tooltip";
import { Button } from "@ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { calculateDocumentCompleteness } from "./utils/documentCompleteness";
import type { FormValues } from "./types";
import { useState } from "react";

interface Document {
  id: number;
  name: string;
}

interface DocumentsListProps {
  documents: Document[];
  title: string;
  category: string;
  formValues: FormValues;
  onDocumentClick: (document: Document) => void;
  onDownload: (document: Document) => void;
  onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void;
}

// Маппинг табов для отображения
const tabLabels: Record<string, string> = {
  primary: "Основная информация",
  pre_court: "Досудебка",
  judicial: "Судебка",
};

// Маппинг аккордеонов для отображения
const accordionLabels: Record<string, string> = {
  mainInfo: "Личные данные",
  addressInfo: "Адрес регистрации",
  passportInfo: "Паспорт",
  familyInfo: "Семейное положение",
  workInfo: "Работа и образование",
  contactInfo: "Контакты",
  deptsInfo: "Долг и исполнительные производства",
  introduction: "Введение процедуры",
  procedure: "Процедура",
  report: "Отчет",
};

export const DocumentsList = ({
  documents,
  title,
  category,
  formValues,
  onDocumentClick,
  onDownload,
  onNavigateToField,
}: DocumentsListProps): JSX.Element | null => {
  const [expandedDocument, setExpandedDocument] = useState<number | null>(null);

  if (!documents || documents.length === 0) {
    return null;
  }

  return (
    <>
      <Separator className="my-6" />
      <Card className="border-2 border-blue-200 dark:border-blue-800 bg-blue-50/30 dark:bg-blue-950/20 shadow-md">
        <CardHeader className="pb-3">
          <CardTitle className="flex items-center gap-2 text-lg font-bold text-blue-900 dark:text-blue-100">
            <FolderOpen className="h-5 w-5" />
            {title}
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {documents.map((document) => {
            const completeness = calculateDocumentCompleteness(category, formValues);
            
            // Определяем цвет границы в зависимости от статуса
            const borderColorClass = 
              completeness.status === "complete" 
                ? "border-green-500 dark:border-green-600" 
                : completeness.status === "partial"
                ? "border-yellow-500 dark:border-yellow-600"
                : "border-red-500 dark:border-red-600";

            const borderWidthClass = completeness.status === "complete" ? "border-2" : "border-2";

            return (
              <div
                key={document.id}
                className={`w-full p-3 rounded-lg bg-background/80 dark:bg-background/60 ${borderWidthClass} ${borderColorClass} transition-all`}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3 flex-1">
                    <FileText className="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <span className="font-medium">{document.name}</span>
                        {completeness.status === "complete" && (
                          <CheckCircle2 className="h-4 w-4 text-green-600 dark:text-green-400" />
                        )}
                        {completeness.status === "partial" && (
                          <AlertCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                        )}
                        {completeness.status === "empty" && (
                          <AlertCircle className="h-4 w-4 text-red-600 dark:text-red-400" />
                        )}
                      </div>
                      <div className="mt-1 flex items-center gap-2">
                        <span className="text-xs text-muted-foreground">
                          Заполнено: {completeness.percentage}%
                        </span>
                        {completeness.missingFields.length > 0 && (
                          <Button
                            variant="ghost"
                            size="sm"
                            className="h-5 px-2 text-xs"
                            onClick={() => setExpandedDocument(expandedDocument === document.id ? null : document.id)}
                          >
                            {expandedDocument === document.id ? "Скрыть" : "Показать"} незаполненные поля ({completeness.missingFields.length})
                          </Button>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="flex gap-2 ml-4">
                    {/* Для шаблона с ID=200 скрываем кнопку просмотра, только скачивание */}
                    {document.id !== 200 && (
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button 
                            onClick={() => onDocumentClick(document)} 
                            variant="outline"
                            size="sm"
                            className="border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900"
                          >
                            <Eye className="h-4 w-4" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Просмотр документа</TooltipContent>
                      </Tooltip>
                    )}
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <Button 
                          onClick={() => onDownload(document)} 
                          variant="outline"
                          size="sm"
                          className="border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900"
                        >
                          <Download className="h-4 w-4" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Скачать документ</TooltipContent>
                    </Tooltip>
                  </div>
                </div>
                {expandedDocument === document.id && completeness.missingFields.length > 0 && (
                  <div className="mt-3 pt-3 border-t border-border/50">
                    <p className="text-xs font-medium text-muted-foreground mb-2">
                      Незаполненные поля:
                    </p>
                    <ul className="list-none space-y-2">
                      {completeness.missingFields.map((field, index) => (
                        <li key={index} className="text-xs">
                          <button
                            onClick={() => {
                              if (onNavigateToField) {
                                onNavigateToField({
                                  tab: field.tab,
                                  accordion: field.accordion,
                                  fieldId: field.fieldId,
                                });
                              }
                            }}
                            className="text-left w-full hover:text-primary transition-colors cursor-pointer flex items-start gap-2 group"
                          >
                            <span className="text-muted-foreground group-hover:text-primary">
                              {field.label}
                            </span>
                            <span className="text-muted-foreground/60 text-[10px] mt-0.5">
                              ({tabLabels[field.tab] || field.tab}
                              {field.accordion && ` → ${accordionLabels[field.accordion] || field.accordion}`})
                            </span>
                          </button>
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </div>
            );
          })}
        </CardContent>
      </Card>
    </>
  );
};
