import { useEffect, useMemo, useState } from "react"
import { CalendarIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { cn } from "@/lib/utils"

const CLEAR_VALUE = ""

const ruFormatter = new Intl.DateTimeFormat("ru-RU", {
  day: "2-digit",
  month: "long",
  year: "numeric",
})

interface DatePickerInputProps {
  label?: string
  value?: string
  onChange: (value: string) => void
  placeholder?: string
  name?: string
  id?: string
  disabled?: boolean
  required?: boolean
  className?: string
}

const toStorageFormat = (date: Date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, "0")
  const day = String(date.getDate()).padStart(2, "0")
  return `${year}-${month}-${day}`
}

const parseStoredValue = (value?: string) => {
  if (!value) return undefined
  const isoMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (isoMatch) {
    const [, y, m, d] = isoMatch
    const date = new Date(Number(y), Number(m) - 1, Number(d))
    return Number.isNaN(date.getTime()) ? undefined : date
  }
  const fallback = new Date(value)
  return Number.isNaN(fallback.getTime()) ? undefined : fallback
}

const parseInputValue = (raw: string) => {
  const trimmed = raw.trim()
  if (!trimmed) {
    return undefined
  }

  const dotMatch = trimmed.match(/^(\d{2})\.(\d{2})\.(\d{4})$/)
  if (dotMatch) {
    const [, d, m, y] = dotMatch
    const date = new Date(Number(y), Number(m) - 1, Number(d))
    return Number.isNaN(date.getTime()) ? undefined : date
  }

  return parseStoredValue(trimmed)
}

const formatDisplayValue = (date?: Date) => {
  if (!date) return ""
  return ruFormatter.format(date)
}

export function DatePickerInput({
  label,
  value,
  onChange,
  placeholder = "Выберите дату",
  name,
  id,
  disabled,
  required,
  className,
}: DatePickerInputProps) {
  const parsedDate = useMemo(() => parseStoredValue(value), [value])
  const [open, setOpen] = useState(false)
  const [date, setDate] = useState<Date | undefined>(parsedDate)
  const [month, setMonth] = useState<Date>(parsedDate ?? new Date())
  const [inputValue, setInputValue] = useState(() => formatDisplayValue(parsedDate))

  useEffect(() => {
    setDate(parsedDate)
    setInputValue(formatDisplayValue(parsedDate))
    setMonth(parsedDate ?? new Date())
  }, [parsedDate])

  const handleSelect = (selected?: Date) => {
    if (!selected) return
    setDate(selected)
    setInputValue(formatDisplayValue(selected))
    onChange(toStorageFormat(selected))
    setOpen(false)
  }

  const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const raw = event.target.value
    setInputValue(raw)

    if (!raw.trim()) {
      onChange(CLEAR_VALUE)
      setDate(undefined)
      return
    }

    const nextDate = parseInputValue(raw)
    if (nextDate) {
      setDate(nextDate)
      setMonth(nextDate)
      onChange(toStorageFormat(nextDate))
    }
  }

  return (
    <div className={cn("space-y-2", className)}>
      {label && <Label htmlFor={id}>{label}</Label>}
      <div className="relative flex gap-2">
        <Input
          id={id}
          name={name}
          value={inputValue}
          placeholder={placeholder}
          className="bg-background pr-10"
          onChange={handleInputChange}
          onFocus={() => setOpen(true)}
          onKeyDown={(event) => {
            if (event.key === "ArrowDown") {
              event.preventDefault()
              setOpen(true)
            }
          }}
          disabled={disabled}
          required={required}
        />
        <Popover open={open} onOpenChange={setOpen}>
          <PopoverTrigger asChild>
            <Button
              type="button"
              variant="ghost"
              className="absolute right-2 top-1/2 size-6 -translate-y-1/2 p-0"
              disabled={disabled}
            >
              <CalendarIcon className="size-3.5" />
              <span className="sr-only">Открыть календарь</span>
            </Button>
          </PopoverTrigger>
          <PopoverContent className="w-auto overflow-hidden p-0" align="end" alignOffset={-8} sideOffset={8}>
            <Calendar
              mode="single"
              selected={date}
              month={month}
              captionLayout="dropdown"
              onMonthChange={setMonth}
              onSelect={handleSelect}
            />
          </PopoverContent>
        </Popover>
      </div>
    </div>
  )
}
