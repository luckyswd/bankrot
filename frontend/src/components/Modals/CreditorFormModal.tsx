import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { CREDITOR_TYPES } from "@/components/databases/constants"

type Creditor = {
  id: number
  name?: string
  inn?: string
  ogrn?: string
  type?: string
  address?: string
}

type CreditorFormModalProps = {
  isOpen: boolean
  onClose: () => void
  creditor?: Creditor | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  name: "",
  inn: "",
  ogrn: "",
  type: "",
  address: "",
}

export const CreditorFormModal = ({ isOpen, onClose, creditor, onSuccess, onError }: CreditorFormModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        name: creditor?.name ?? "",
        inn: creditor?.inn ?? "",
        ogrn: creditor?.ogrn ?? "",
        type: creditor?.type ?? "",
        address: creditor?.address ?? "",
      })
      setError(null)
      setSubmitting(false)
    } else {
      setFormData(emptyForm)
    }
  }, [isOpen, creditor])

  const handleSubmit = async () => {
    if (!formData.name.trim()) {
      setError("Наименование обязательно")
      return
    }

    const payload = {
      name: formData.name.trim(),
      inn: formData.inn.trim() || null,
      ogrn: formData.ogrn.trim() || null,
      type: formData.type || null,
      address: formData.address.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      if (creditor?.id) {
        await apiRequest(`/creditors/${creditor.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Кредитор успешно обновлён")
      } else {
        await apiRequest("/creditors", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Кредитор успешно создан")
      }
      onClose()
    } catch (err) {
      console.error("Ошибка при сохранении кредитора:", err)
      const message = err instanceof Error ? err.message : "Не удалось сохранить кредитора"
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
          <DialogTitle>{creditor ? "Редактировать кредитора" : "Новый кредитор"}</DialogTitle>
          <DialogDescription>
            {creditor ? "Измените данные кредитора" : "Добавьте нового кредитора в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="creditor-name">Наименование *</Label>
            <Input
              id="creditor-name"
              value={formData.name}
              onChange={(e) => setFormData((prev) => ({ ...prev, name: e.target.value }))}
              placeholder="Введите наименование"
              disabled={submitting}
            />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="creditor-inn">ИНН</Label>
              <Input
                id="creditor-inn"
                value={formData.inn}
                onChange={(e) => setFormData((prev) => ({ ...prev, inn: e.target.value }))}
                placeholder="Введите ИНН"
                disabled={submitting}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="creditor-ogrn">ОГРН</Label>
              <Input
                id="creditor-ogrn"
                value={formData.ogrn}
                onChange={(e) => setFormData((prev) => ({ ...prev, ogrn: e.target.value }))}
                placeholder="Введите ОГРН"
                disabled={submitting}
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="creditor-type">Тип</Label>
            <Select
              value={formData.type || undefined}
              onValueChange={(value) => setFormData((prev) => ({ ...prev, type: value }))}
              disabled={submitting}
            >
              <SelectTrigger id="creditor-type">
                <SelectValue placeholder="Выберите тип" />
              </SelectTrigger>
              <SelectContent>
                {CREDITOR_TYPES.map((type) => (
                  <SelectItem key={type.value} value={type.value}>
                    {type.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label htmlFor="creditor-address">Адрес</Label>
            <Input
              id="creditor-address"
              value={formData.address}
              onChange={(e) => setFormData((prev) => ({ ...prev, address: e.target.value }))}
              placeholder="Введите адрес"
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
