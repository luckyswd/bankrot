import { useEffect, useState } from "react"

import { apiRequest } from "@/config/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import InputMask from "react-input-mask"

type FinancialManager = {
  id?: number
  fio?: string
  inn?: string
  snils?: string
  arbitrationManagerRegistryNumber?: string
  email?: string
  phone?: string
  aauName?: string
  aauOgrn?: string
  aauInn?: string
  aauAddress?: string
}

type FinancialManagerFormModalProps = {
  isOpen: boolean
  onClose: () => void
  financialManager?: FinancialManager | null
  onSuccess?: (message: string) => Promise<void> | void
  onError?: (message: string) => void
}

const emptyForm = {
  fio: "",
  inn: "",
  snils: "",
  arbitrationManagerRegistryNumber: "",
  email: "",
  phone: "",
  aauName: "",
  aauOgrn: "",
  aauInn: "",
  aauAddress: "",
}

export const FinancialManagerFormModal = ({ isOpen, onClose, financialManager, onSuccess, onError }: FinancialManagerFormModalProps) => {
  const [formData, setFormData] = useState(emptyForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (isOpen) {
      setFormData({
        fio: financialManager?.fio ?? "",
        inn: financialManager?.inn ?? "",
        snils: financialManager?.snils ?? "",
        arbitrationManagerRegistryNumber: financialManager?.arbitrationManagerRegistryNumber ?? "",
        email: financialManager?.email ?? "",
        phone: financialManager?.phone ?? "",
        aauName: financialManager?.aauName ?? "",
        aauOgrn: financialManager?.aauOgrn ?? "",
        aauInn: financialManager?.aauInn ?? "",
        aauAddress: financialManager?.aauAddress ?? "",
      })
      setError(null)
      setSubmitting(false)
    } else {
      setFormData(emptyForm)
    }
  }, [isOpen, financialManager])

  const handleSubmit = async () => {
    if (!financialManager?.id && !formData.fio.trim()) {
      setError("ФИО обязательно")
      return
    }

    const payload: Record<string, string | null> = {
      fio: formData.fio.trim() || null,
      inn: formData.inn.trim() || null,
      snils: formData.snils.trim() || null,
      arbitrationManagerRegistryNumber: formData.arbitrationManagerRegistryNumber.trim() || null,
      email: formData.email.trim() || null,
      phone: formData.phone.trim() || null,
      aauName: formData.aauName.trim() || null,
      aauOgrn: formData.aauOgrn.trim() || null,
      aauInn: formData.aauInn.trim() || null,
      aauAddress: formData.aauAddress.trim() || null,
    }

    try {
      setSubmitting(true)
      setError(null)
      if (financialManager?.id) {
        await apiRequest(`/financial-managers/${financialManager.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Финансовый управляющий успешно обновлён")
      } else {
        await apiRequest("/financial-managers", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        })
        await onSuccess?.("Финансовый управляющий успешно создан")
      }
      onClose()
    } catch (err) {
      console.error("Ошибка при сохранении финансового управляющего:", err)
      const message = err instanceof Error ? err.message : "Не удалось сохранить финансового управляющего"
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
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{financialManager?.id ? "Редактировать финансового управляющего" : "Новый финансовый управляющий"}</DialogTitle>
          <DialogDescription>
            {financialManager?.id ? "Измените данные финансового управляющего" : "Добавьте нового финансового управляющего в базу данных"}
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="fio">ФИО *</Label>
              <Input
                id="fio"
                value={formData.fio}
                onChange={(e) => setFormData((prev) => ({ ...prev, fio: e.target.value }))}
                placeholder="Введите ФИО"
                disabled={submitting}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="inn">ИНН</Label>
              <Input
                id="inn"
                value={formData.inn}
                onChange={(e) => setFormData((prev) => ({ ...prev, inn: e.target.value }))}
                placeholder="164493760911"
                disabled={submitting}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="snils">СНИЛС</Label>
              <InputMask
                mask="999-999-999 99"
                value={formData.snils}
                onChange={(e) => setFormData((prev) => ({ ...prev, snils: e.target.value }))}
                maskChar={null}
                disabled={submitting}
              >
                {(inputProps: any) => (
                  <Input
                    id="snils"
                    placeholder="128-338-690 82"
                    disabled={submitting}
                    {...inputProps}
                  />
                )}
              </InputMask>
            </div>
            <div className="space-y-2">
              <Label htmlFor="arbitrationManagerRegistryNumber">Регистрационный номер</Label>
              <Input
                id="arbitrationManagerRegistryNumber"
                value={formData.arbitrationManagerRegistryNumber}
                onChange={(e) => setFormData((prev) => ({ ...prev, arbitrationManagerRegistryNumber: e.target.value }))}
                placeholder="21769"
                disabled={submitting}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                value={formData.email}
                onChange={(e) => setFormData((prev) => ({ ...prev, email: e.target.value }))}
                placeholder="ivanov@example.com"
                disabled={submitting}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="phone">Телефон</Label>
              <InputMask
                mask="+7(999)-999-99-99"
                value={formData.phone}
                onChange={(e) => setFormData((prev) => ({ ...prev, phone: e.target.value }))}
                maskChar={null}
                disabled={submitting}
              >
                {(inputProps: any) => (
                  <Input
                    id="phone"
                    placeholder="+7(999)-999-99-99"
                    disabled={submitting}
                    {...inputProps}
                  />
                )}
              </InputMask>
            </div>
          </div>

          <div className="border-t pt-4 mt-4">
            <h3 className="text-lg font-semibold mb-4">Данные ассоциации арбитражных управляющих</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="aauName">Название</Label>
                <Input
                  id="aauName"
                  value={formData.aauName}
                  onChange={(e) => setFormData((prev) => ({ ...prev, aauName: e.target.value }))}
                  placeholder="Введите название"
                  disabled={submitting}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="aauOgrn">ОГРН</Label>
                <Input
                  id="aauOgrn"
                  value={formData.aauOgrn}
                  onChange={(e) => setFormData((prev) => ({ ...prev, aauOgrn: e.target.value }))}
                  placeholder="1137800008477"
                  disabled={submitting}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="aauInn">ИНН</Label>
                <Input
                  id="aauInn"
                  value={formData.aauInn}
                  onChange={(e) => setFormData((prev) => ({ ...prev, aauInn: e.target.value }))}
                  placeholder="7801351420"
                  disabled={submitting}
                />
              </div>
            </div>
          </div>

          <div className="border-t pt-4 mt-4">
            <h3 className="text-lg font-semibold mb-4">Адрес ассоциации арбитражных управляющих</h3>
            <div className="space-y-2">
              <Label htmlFor="aauAddress">Адрес</Label>
              <Input
                id="aauAddress"
                value={formData.aauAddress}
                onChange={(e) => setFormData((prev) => ({ ...prev, aauAddress: e.target.value }))}
                placeholder="191124, город Санкт-Петербург, Суворовский пр-кт, д. 65 литер Б, пом. 8-Н-43"
                disabled={submitting}
              />
            </div>
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

