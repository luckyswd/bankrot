import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type Court = {
  id: number
  name?: string
  address?: string
  phone?: string
}

type CourtFormModalProps = {
  isOpen: boolean
  onClose: () => void
  court?: Court | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  name: "",
  address: "",
  phone: "",
}

export const CourtFormModal = ({ isOpen, onClose, court, onSuccess, onError }: CourtFormModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        name: court?.name ?? "",
        address: court?.address ?? "",
        phone: court?.phone ?? "",
      })
      setError(null)
      setSubmitting(false)
    } else {
      setFormData(emptyForm)
    }
  }, [isOpen, court])

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
      if (court?.id) {
        await apiRequest(`/courts/${court.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Суд успешно обновлён")
      } else {
        await apiRequest("/courts", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Суд успешно создан")
      }
      onClose()
    } catch (err) {
      console.error("Ошибка при сохранении суда:", err)
      const message = err instanceof Error ? err.message : "Не удалось сохранить суд"
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
          <DialogTitle>{court ? "Редактировать суд" : "Новый суд"}</DialogTitle>
          <DialogDescription>
            {court ? "Измените данные суда" : "Добавьте новый арбитражный суд в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="court-name">Наименование *</Label>
            <Input
              id="court-name"
              value={formData.name}
              onChange={(e) => setFormData((prev) => ({ ...prev, name: e.target.value }))}
              placeholder="Введите наименование"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="court-address">Адрес</Label>
            <Input
              id="court-address"
              value={formData.address}
              onChange={(e) => setFormData((prev) => ({ ...prev, address: e.target.value }))}
              placeholder="Введите адрес"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="court-phone">Телефон</Label>
            <Input
              id="court-phone"
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
