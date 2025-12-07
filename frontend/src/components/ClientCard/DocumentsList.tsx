import { FileText, Eye, Download } from "lucide-react";
import { Separator } from "@/components/ui/separator";
import { Tooltip, TooltipContent, TooltipTrigger } from "@ui/tooltip";
import { Button } from "@ui/button";

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
      <div className="space-y-3">
        <h4 className="font-semibold">{title}</h4>
        {documents.map((document) => (
          <div
            key={document.id}
            className="w-full justify-between flex items-center p-2"
          >
            <div className="flex items-center">
              <FileText className="mr-2 h-4 w-4" />
              {document.name}
            </div>
            <div className="flex gap-3">
              {/* Для шаблона с ID=200 скрываем кнопку просмотра, только скачивание */}
              {document.id !== 200 && (
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button onClick={() => onDocumentClick(document)} variant="secondary">
                      <Eye className="h-5 w-5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Просмотр документа</TooltipContent>
                </Tooltip>
              )}
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button onClick={() => onDownload(document)} variant="secondary">
                    <Download className="h-5 w-5" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Скачать документ</TooltipContent>
              </Tooltip>
            </div>
          </div>
        ))}
      </div>
    </>
  );
};
