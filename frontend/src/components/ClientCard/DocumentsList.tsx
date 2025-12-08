import { FileText, Eye, Download, FolderOpen } from "lucide-react";
import { Separator } from "@/components/ui/separator";
import { Tooltip, TooltipContent, TooltipTrigger } from "@ui/tooltip";
import { Button } from "@ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface Document {
  id: number;
  name: string;
}

interface DocumentsListProps {
  documents: Document[];
  title: string;
  onDocumentClick: (document: Document) => void;
  onDownload: (document: Document) => void;
}

export const DocumentsList = ({
  documents,
  title,
  onDocumentClick,
  onDownload,
}: DocumentsListProps): JSX.Element | null => {
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
        <CardContent className="space-y-2">
          {documents.map((document) => (
            <div
              key={document.id}
              className="w-full justify-between flex items-center p-3 rounded-lg bg-background/80 dark:bg-background/60 border border-border/50"
            >
              <div className="flex items-center gap-2">
                <FileText className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                <span className="font-medium">{document.name}</span>
              </div>
              <div className="flex gap-2">
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
          ))}
        </CardContent>
      </Card>
    </>
  );
};
