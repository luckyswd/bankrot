import { useEffect, useState } from "react"

import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"

type ConfirmModalProps = {
  isOpen: boolean
  onClose: () => void
  title: string
  description?: string
  confirmLabel?: string
  cancelLabel?: string
  confirmVariant?: "default" | "destructive"
  onConfirm?: () => Promise<void> | void
}

export const ConfirmModal = ({
  isOpen,
  onClose,
  title,
  description,
  confirmLabel = "Подтвердить",
  cancelLabel = "Отмена",
  confirmVariant = "default",
  onConfirm,
}: ConfirmModalProps) => {
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!isOpen) {
      setSubmitting(false)
      setError(null)
    }
  }, [isOpen])

  const handleConfirm = async () => {
    if (!onConfirm) {
      onClose()
      return
    }

    try {
      setSubmitting(true)
      setError(null)
      await onConfirm()
      onClose()
    } catch (err) {
      console.error("Confirm modal error:", err)
      const message =
        err instanceof Error
          ? err.message
          : typeof err === "string"
          ? err
          : "Не удалось выполнить действие"
      setError(message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Dialog
      open={isOpen}
      onOpenChange={(open) => {
        if (!open && !submitting) {
          onClose()
        }
      }}
    >
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          {description && <DialogDescription>{description}</DialogDescription>}
        </DialogHeader>
        {error && <p className="text-sm text-destructive">{error}</p>}
        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => {
              if (!submitting) {
                onClose()
              }
            }}
            disabled={submitting}
          >
            {cancelLabel}
          </Button>
          <Button variant={confirmVariant} onClick={handleConfirm} disabled={submitting}>
            {submitting ? "Подождите..." : confirmLabel}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
