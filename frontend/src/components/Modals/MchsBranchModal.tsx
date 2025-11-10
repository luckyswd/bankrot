import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type MchsBranch = {
  id: number
  name?: string
  address?: string
  phone?: string
}

type MchsBranchModalProps = {
  isOpen: boolean
  onClose: () => void
  branch?: MchsBranch | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  name: "",
  address: "",
  phone: "",
}

export const MchsBranchModal = ({ isOpen, onClose, branch, onSuccess, onError }: MchsBranchModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        name: branch?.name ?? "",
        address: branch?.address ?? "",
        phone: branch?.phone ?? "",
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
      phone: formData.phone.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      if (branch?.id) {
        await apiRequest(`/mchs/${branch.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Отделение успешно обновлено")
      } else {
        await apiRequest("/mchs", {
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
      console.error("Ошибка при сохранении отделения МЧС:", err)
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
            {branch ? "Измените данные отделения" : "Добавьте новое отделение ГИМС МЧС в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="mchs-name">Наименование *</Label>
            <Input
              id="mchs-name"
              value={formData.name}
              onChange={(e) => setFormData((prev) => ({ ...prev, name: e.target.value }))}
              placeholder="Введите наименование"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="mchs-address">Адрес</Label>
            <Input
              id="mchs-address"
              value={formData.address}
              onChange={(e) => setFormData((prev) => ({ ...prev, address: e.target.value }))}
              placeholder="Введите адрес"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="mchs-phone">Телефон</Label>
            <Input
              id="mchs-phone"
              value={formData.phone}
              onChange={(e) => setFormData((prev) => ({ ...prev, phone: e.target.value }))}
              placeholder="Введите телефон"
              disabled={submitting}
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
