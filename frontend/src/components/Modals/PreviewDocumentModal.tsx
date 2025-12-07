import { useEffect, useRef, useState } from "react";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { apiRequest } from "@/config/api";
import { notify } from "../ui/toast";
import { renderAsync } from "docx-preview";

type PreviewDocumentModalProps = {
  isOpen: boolean;
  onClose: () => void;
  document?: { documentId: number; contractId: number; documentName: string };
};

export const PreviewDocumentModal = ({
  isOpen,
  onClose,
  document,
}: PreviewDocumentModalProps) => {
  useEffect(() => {
    fetchDocument();
  }, []);
  const [stateBlob, setStateBlob] = useState<Blob | null>(null);

  const documentContainer = useRef<HTMLDivElement>(null);

  const fetchDocument = async () => {
    try {
      const blob = await apiRequest(
        `/document-templates/${document?.documentId}/generate`,
        {
          method: "POST",
          body: JSON.stringify({ contractId: document?.contractId }),
          responseType: "blob",
        }
      );
      setStateBlob(blob);
      await renderAsync(blob, documentContainer.current!, undefined, {
        inWrapper: true,
      });

    } catch (error) {
      notify({
        message:
          error instanceof Error
            ? error.message
            : "Ошибка при генерации документа",
        type: "error",
        duration: 5000,
      });
    }
  };

  const handleDownload = () => {
    if (!stateBlob) return;

      const downloadUrl = window.URL.createObjectURL(stateBlob);
      const link = window.document.createElement("a");

      link.href = downloadUrl;
      // Для шаблона с ID=200 используем расширение .xlsx
      link.download = `${document?.documentName}.${document?.documentId === 200 ? 'xlsx' : 'docx'}`;
      window.document.body.appendChild(link);
      link.click();
      window.document.body.removeChild(link);
      window.URL.revokeObjectURL(downloadUrl);
      onClose();
  }
  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-5xl">
        <DialogHeader>
          <DialogTitle>Просмотр документа</DialogTitle>
          <DialogDescription>{document?.documentName}</DialogDescription>
        </DialogHeader>
        <div className="max-h-[50vh] overflow-scroll writing-unset" ref={documentContainer}>

        </div>
        <DialogFooter>
          <Button variant="outline" onClick={onClose}>
            Отмена
          </Button>
          <Button onClick={handleDownload}>Скачать</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};
