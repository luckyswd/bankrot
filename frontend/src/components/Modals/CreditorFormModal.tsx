import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"

type Creditor = {
  id: number
  name?: string
  address?: string | null
  headFullName?: string | null
  bankDetails?: string | null
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
  address: "",
  headFullName: "",
  bankDetails: "",
}

export const CreditorFormModal = ({ isOpen, onClose, creditor, onSuccess, onError }: CreditorFormModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        name: creditor?.name ?? "",
        address: creditor?.address ?? "",
        headFullName: creditor?.headFullName ?? "",
        bankDetails: creditor?.bankDetails ?? "",
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
      address: formData.address.trim() || null,
      headFullName: formData.headFullName.trim() || null,
      bankDetails: formData.bankDetails.trim() || null,
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
          <div className="space-y-2">
            <Label htmlFor="creditor-headFullName">ФИО руководителя</Label>
            <Input
              id="creditor-headFullName"
              value={formData.headFullName}
              onChange={(e) => setFormData((prev) => ({ ...prev, headFullName: e.target.value }))}
              placeholder="Введите ФИО руководителя"
              disabled={submitting}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="creditor-bankDetails">Банковские реквизиты</Label>
            <Textarea
              id="creditor-bankDetails"
              value={formData.bankDetails}
              onChange={(e) => setFormData((prev) => ({ ...prev, bankDetails: e.target.value }))}
              placeholder="Введите банковские реквизиты (БИК, ИНН, КПП, КОРР.СЧЕТ, РАСЧЕТНЫЙ СЧЕТ)"
              disabled={submitting}
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
