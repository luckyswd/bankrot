import { useEffect, useState, type ChangeEvent } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type CreateContractModalProps = {
  isOpen: boolean
  onClose: () => void
  onSuccess?: (contractNumber: string) => Promise<void> | void
  onError?: (message: string) => void
}

const initialForm = {
  contractNumber: "",
  lastName: "",
  firstName: "",
  middleName: "",
}

export const CreateContractModal = ({ isOpen, onClose, onSuccess, onError }: CreateContractModalProps) => {
  const [form, setForm] = useState(initialForm)
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    if (isOpen) {
      setForm(initialForm)
      setError(null)
      setSubmitting(false)
    }
  }, [isOpen])

  const handleChange = (field: keyof typeof form) => (event: ChangeEvent<HTMLInputElement>) => {
    setForm((prev) => ({ ...prev, [field]: event.target.value }))
  }

  const handleSubmit = async () => {
    if (!form.contractNumber.trim() || !form.lastName.trim() || !form.firstName.trim()) {
      setError("Укажите номер договора, фамилию и имя")
      return
    }

    const payload = {
      contractNumber: form.contractNumber.trim(),
      lastName: form.lastName.trim(),
      firstName: form.firstName.trim(),
      middleName: form.middleName.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      await apiRequest("/contracts", {
        method: "POST",
        body: JSON.stringify(payload),
      })
      await onSuccess?.(payload.contractNumber)
      onClose()
    } catch (err) {
      console.error("Ошибка при создании договора:", err)
      const message =
        err instanceof Error ? err.message : "Не удалось создать договор"
      setError(message)
      onError?.(message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Dialog open={isOpen} onOpenChange={(open) => !submitting && !open && onClose()}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Новый договор</DialogTitle>
          <DialogDescription>Укажите основные данные, чтобы создать карточку договора</DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-2">
          <div className="space-y-2">
            <Label htmlFor="modal-contract-number">Номер договора *</Label>
            <Input
              id="modal-contract-number"
              placeholder="ДГ-001"
              value={form.contractNumber}
              onChange={handleChange("contractNumber")}
              disabled={submitting}
            />
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="modal-last-name">Фамилия *</Label>
              <Input
                id="modal-last-name"
                placeholder="Иванов"
                value={form.lastName}
                onChange={handleChange("lastName")}
                disabled={submitting}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="modal-first-name">Имя *</Label>
              <Input
                id="modal-first-name"
                placeholder="Иван"
                value={form.firstName}
                onChange={handleChange("firstName")}
                disabled={submitting}
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="modal-middle-name">Отчество</Label>
            <Input
              id="modal-middle-name"
              placeholder="Иванович"
              value={form.middleName}
              onChange={handleChange("middleName")}
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
            {submitting ? "Создание..." : "Создать"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
