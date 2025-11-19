import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type Bailiff = {
  id: number
  department?: string
  address?: string | null
  headFullName?: string | null
}

type BailiffFormModalProps = {
  isOpen: boolean
  onClose: () => void
  bailiff?: Bailiff | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  department: "",
  address: "",
  headFullName: "",
}

export const BailiffFormModal = ({ isOpen, onClose, bailiff, onSuccess, onError }: BailiffFormModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        department: bailiff?.department ?? "",
        address: bailiff?.address ?? "",
        headFullName: bailiff?.headFullName ?? "",
      })
      setError(null)
      setSubmitting(false)
    } else {
      setFormData(emptyForm)
    }
  }, [isOpen, bailiff])

  const handleSubmit = async () => {
    if (!formData.department.trim()) {
      setError("Наименование отдела обязательно")
      return
    }

    const payload = {
      department: formData.department.trim(),
      address: formData.address.trim() || null,
      headFullName: formData.headFullName.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      if (bailiff?.id) {
        await apiRequest(`/bailiffs/${bailiff.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Отделение успешно обновлено")
      } else {
        await apiRequest("/bailiffs", {
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
      console.error("Ошибка при сохранении отделения приставов:", err)
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
          <DialogTitle>{bailiff ? "Редактировать отделение" : "Новое отделение"}</DialogTitle>
          <DialogDescription>
            {bailiff ? "Измените данные отделения" : "Добавьте новое отделение ФССП в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="bailiff-department">Отделение *</Label>
            <Input
              id="bailiff-department"
              value={formData.department}
              onChange={(e) => setFormData((prev) => ({ ...prev, department: e.target.value }))}
              placeholder="Введите наименование отделения"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="bailiff-address">Адрес</Label>
            <Input
              id="bailiff-address"
              value={formData.address}
              onChange={(e) => setFormData((prev) => ({ ...prev, address: e.target.value }))}
              placeholder="Введите адрес"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="bailiff-headFullName">Старший пристав</Label>
            <Input
              id="bailiff-headFullName"
              value={formData.headFullName}
              onChange={(e) => setFormData((prev) => ({ ...prev, headFullName: e.target.value }))}
              placeholder="Введите ФИО старшего пристава"
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
