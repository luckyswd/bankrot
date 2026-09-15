import { AlertTriangle } from "lucide-react"

import { cn } from "@/lib/utils"
import type { ReferenceItem } from "@/types/reference"

export type InsuranceStatus = "valid" | "expired" | "missing"

interface InsuranceStatusAlertProps {
  status?: InsuranceStatus | null
  endDate?: string | null
  className?: string
}

const toIsoDate = (date: Date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, "0")
  const day = String(date.getDate()).padStart(2, "0")
  return `${year}-${month}-${day}`
}

const formatIsoDate = (value: string) => {
  const [year, month, day] = value.slice(0, 10).split("-")
  return `${day}.${month}.${year}`
}

export const resolveInsuranceStatus = (endDate?: string | null): InsuranceStatus => {
  if (!endDate) {
    return "missing"
  }

  return endDate.slice(0, 10) < toIsoDate(new Date()) ? "expired" : "valid"
}

export const getInsuranceStatusMessage = (status?: InsuranceStatus | null, endDate?: string | null): string | null => {
  if (status === "expired" && endDate) {
    return `Полис страхования истёк ${formatIsoDate(endDate)}. Внесите новый договор страхования в справочнике финансовых управляющих`
  }

  if (status === "missing") {
    return "Не внесён договор страхования ответственности. Заполните его в справочнике финансовых управляющих"
  }

  return null
}

export const InsuranceStatusBadge = ({ status, endDate, className }: InsuranceStatusAlertProps) => {
  const message = getInsuranceStatusMessage(status, endDate)

  if (!message) {
    return null
  }

  return (
    <span
      title={message}
      className={cn(
        "inline-flex w-fit items-center gap-1 rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200",
        className
      )}
    >
      <AlertTriangle className="size-3" />
      {status === "expired" && endDate ? `Полис истёк ${formatIsoDate(endDate)}` : "Нет полиса"}
    </span>
  )
}

export const InsuranceStatusAlert = ({ status, endDate, className }: InsuranceStatusAlertProps) => {
  const message = getInsuranceStatusMessage(status, endDate)

  if (!message) {
    return null
  }

  return (
    <div
      role="alert"
      className={cn(
        "flex items-start gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200",
        className
      )}
    >
      <AlertTriangle className="mt-0.5 size-4 shrink-0" />
      <span>{message}</span>
    </div>
  )
}

interface FinancialManagerInsuranceAlertProps {
  managerId?: string | null
  financialManagers?: ReferenceItem[]
  className?: string
}

export const FinancialManagerInsuranceAlert = ({ managerId, financialManagers, className }: FinancialManagerInsuranceAlertProps) => {
  const manager = managerId ? financialManagers?.find((item) => String(item.id) === String(managerId)) : undefined

  if (!manager) {
    return null
  }

  return (
    <InsuranceStatusAlert
      status={manager.insuranceStatus as InsuranceStatus | undefined}
      endDate={typeof manager.insuranceEndDate === "string" ? manager.insuranceEndDate : null}
      className={className}
    />
  )
}
