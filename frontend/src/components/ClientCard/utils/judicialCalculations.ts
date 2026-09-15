import { addMonths, format, isValid, parseISO } from "date-fns"

export const DEFAULT_ZAGS_DEPARTMENT = "отдел ЗАГС Комитета по делам ЗАГС Правительства Санкт-Петербурга"

const REGISTRY_CLOSING_MONTHS = 2
const KOPECKS_IN_RUBLE = 100

const moneyFormatter = new Intl.NumberFormat("ru-RU", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

type NumericInput = string | number | null | undefined

export const toDateOnly = (value: unknown): string =>
  typeof value === "string" && value.length >= 10 ? value.slice(0, 10) : ""

export const calculateRegistryClosingDate = (publicationDate?: string | null): string | null => {
  const parsed = parseISO(toDateOnly(publicationDate))

  if (!isValid(parsed)) {
    return null
  }

  return format(addMonths(parsed, REGISTRY_CLOSING_MONTHS), "dd.MM.yyyy")
}

const hasValue = (value: NumericInput): boolean => value !== null && value !== undefined && String(value).trim() !== ""

const toKopecks = (value: NumericInput): number => {
  if (!hasValue(value)) {
    return 0
  }

  const parsed = Number(String(value).replace(",", "."))

  return Number.isFinite(parsed) ? Math.round(parsed * KOPECKS_IN_RUBLE) : 0
}

export const formatMoney = (value: NumericInput): string => moneyFormatter.format(toKopecks(value) / KOPECKS_IN_RUBLE)

export const calculateUnpaid = (amount: NumericInput, paid: NumericInput): string =>
  moneyFormatter.format((toKopecks(amount) - toKopecks(paid)) / KOPECKS_IN_RUBLE)

export const isPaidOverAmount = (amount: NumericInput, paid: NumericInput): boolean =>
  hasValue(paid) && toKopecks(paid) > toKopecks(amount)

export const isNegativeNumber = (value: NumericInput): boolean => hasValue(value) && toKopecks(value) < 0

export const calculateClaimsConsidered = (included: NumericInput, rejected: NumericInput): string => {
  if (!hasValue(included) && !hasValue(rejected)) {
    return ""
  }

  return String(Math.trunc(Number(included) || 0) + Math.trunc(Number(rejected) || 0))
}
