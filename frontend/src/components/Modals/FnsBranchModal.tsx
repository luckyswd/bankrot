import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type FnsBranch = {
  id: number
  name?: string
  address?: string
  directorName?: string
  bankDetails?: string
}

type FnsBranchModalProps = {
  isOpen: boolean
  onClose: () => void
  branch?: FnsBranch | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  name: "",
  address: "",
  directorName: "",
  bankDetails: "",
}

export const FnsBranchModal = ({ isOpen, onClose, branch, onSuccess, onError }: FnsBranchModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        name: branch?.name ?? "",
        address: branch?.address ?? "",
        directorName: branch?.directorName ?? "",
        bankDetails: branch?.bankDetails ?? "",
      })
      setError(null)
      setSubmitting(false)
    } else {
      setFormData(emptyForm)
    }
  }, [isOpen, branch])

  const handleSubmit = async () => {
    if (!formData.name.trim()) {
      setError("Наименование обязательно")
      return
    }

    const payload = {
      name: formData.name.trim(),
      address: formData.address.trim() || null,
      directorName: formData.directorName.trim() || null,
      bankDetails: formData.bankDetails.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      if (branch?.id) {
        await apiRequest(`/fns/${branch.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Отделение успешно обновлено")
      } else {
        await apiRequest("/fns", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Отделение успешно создано")
      }
      onClose()
    } catch (err) {
      console.error("Ошибка при сохранении отделения ФНС:", err)
      const message = err instanceof Error ? err.message : "Не удалось сохранить отделение"
      setError(message)
      onError?.(message)
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
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{branch ? "Редактировать отделение" : "Новое отделение"}</DialogTitle>
          <DialogDescription>
            {branch ? "Измените данные отделения" : "Добавьте новое отделение ФНС в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="fns-name">Наименование *</Label>
            <Input
              id="fns-name"
              value={formData.name}
              onChange={(e) => setFormData((prev) => ({ ...prev, name: e.target.value }))}
              placeholder="Введите наименование"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="fns-address">Адрес</Label>
            <Input
              id="fns-address"
              value={formData.address}
              onChange={(e) => setFormData((prev) => ({ ...prev, address: e.target.value }))}
              placeholder="Введите адрес"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="fns-directorName">ФИО руководителя</Label>
            <Input
              id="fns-directorName"
              value={formData.directorName}
              onChange={(e) => setFormData((prev) => ({ ...prev, directorName: e.target.value }))}
              placeholder="Введите ФИО руководителя"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="fns-bankDetails">Банковские реквизиты</Label>
            <textarea
              id="fns-bankDetails"
              value={formData.bankDetails}
              onChange={(e) => setFormData((prev) => ({ ...prev, bankDetails: e.target.value }))}
              placeholder="Введите банковские реквизиты"
              disabled={submitting}
              className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              rows={4}
            />
          </div>
          {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={onClose} disabled={submitting}>
            Отмена
          </Button>
          <Button onClick={handleSubmit} disabled={submitting}>
            {submitting ? "Сохранение..." : "Сохранить"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
