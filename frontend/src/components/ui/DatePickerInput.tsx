import { useEffect, useMemo, useState } from "react"
import { CalendarIcon } from "lucide-react"
import InputMask from "react-input-mask"

import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { cn } from "@/lib/utils"

const CLEAR_VALUE = ""

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

  const digitsOnly = trimmed.replace(/\D/g, "")
  if (digitsOnly.length !== 8) {
    return undefined
  }

  const dotMatch = trimmed.match(/^(\d{2})\.(\d{2})\.(\d{4})$/)
  if (dotMatch) {
    const [, d, m, y] = dotMatch
    const date = new Date(Number(y), Number(m) - 1, Number(d))
    return Number.isNaN(date.getTime()) ? undefined : date
  }

  const d = digitsOnly.slice(0, 2)
  const m = digitsOnly.slice(2, 4)
  const y = digitsOnly.slice(4, 8)
  const date = new Date(Number(y), Number(m) - 1, Number(d))
  return Number.isNaN(date.getTime()) ? undefined : date
}

const formatDisplayValue = (date?: Date) => {
  if (!date) return ""
  const day = String(date.getDate()).padStart(2, "0")
  const month = String(date.getMonth() + 1).padStart(2, "0")
  const year = date.getFullYear()
  return `${day}.${month}.${year}`
}

export function DatePickerInput({
  label,
  value,
  onChange,
  placeholder = "дд.мм.гггг",
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
    } else {
      if (date) {
        setDate(undefined)
        onChange(CLEAR_VALUE)
      }
    }
  }

  return (
    <div className={cn("space-y-2", className)}>
      {label && <Label htmlFor={id}>{label}</Label>}
      <div 
        className="relative flex gap-2"
        onMouseDown={(e) => {
          if (!disabled && (e.target as HTMLElement).tagName !== "BUTTON") {
            setOpen(true)
          }
        }}
      >
        <InputMask
          mask="99.99.9999"
          value={inputValue}
          onChange={handleInputChange}
          maskChar={null}
          disabled={disabled}
        >
          {(inputProps: any) => {
            const { onMouseDown, ...restProps } = inputProps
            return (
              <Input
                id={id}
                name={name}
                placeholder={placeholder}
                className="bg-background pr-10"
                onKeyDown={(event) => {
                  if (event.key === "ArrowDown") {
                    event.preventDefault()
                    setOpen(true)
                  }
                }}
                required={required}
                {...restProps}
              />
            )
          }}
        </InputMask>
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
