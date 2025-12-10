import { useEffect, useState } from "react";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

type UnsavedChangesModalProps = {
  isOpen: boolean;
  onClose: () => void;
  onConfirm?: () => void;
  onSaveAndExit?: () => Promise<void> | void;
};

export const UnsavedChangesModal = ({
  isOpen,
  onClose,
  onConfirm,
  onSaveAndExit,
}: UnsavedChangesModalProps) => {
  const [submitting, setSubmitting] = useState(false);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!isOpen) {
      setSubmitting(false);
    }
  }, [isOpen]);

  const handleConfirm = async () => {
    if (!onConfirm) {
      onClose();
      return;
    }
    try {
      setSubmitting(true);
      await onConfirm();
      onClose();
    } finally {
      setSubmitting(false);
    }
  };

  const handleSaveAndExit = async () => {
    if (!onSaveAndExit) {
      onClose();
      return;
    }

    try {
      setSaving(true);
      await onSaveAndExit();
      onClose();
    } finally {
      setSaving(false);
    }
  };

  return (
    <Dialog
      open={isOpen}
      onOpenChange={(open) => {
        if (!open && !submitting) {
          onClose();
        }
      }}
    >
      <DialogContent>
        <DialogHeader>
          <DialogTitle>У вас есть не сохраненные элементы</DialogTitle>
          <DialogDescription>
            Вы уверены, что хотите выйти, не сохранив изменения?
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" onClick={onClose} disabled={submitting}>
            Остаться
          </Button>
          <Button
            variant="destructive"
            onClick={handleConfirm}
            disabled={submitting}
          >
            {submitting ? "Выходим..." : "Выйти без сохранения"}
          </Button>
          <Button className="bg-green-500" onClick={handleSaveAndExit} disabled={submitting || saving}>
            {saving ? "Сохраняем..." : "Сохранить и выйти"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};
