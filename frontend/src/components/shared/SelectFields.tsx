import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

interface SelectFieldProps {
  value: string | boolean | null | undefined
  onChange: (value: string | boolean | null) => void
  options: SelectOption[]
  placeholder?: string
}
export type SelectOption = { value: string | boolean; label: string }

const CLEAR_VALUE = "__clear__"

export const SelectField = ({ value, onChange, options, placeholder = "Не указано" }: SelectFieldProps) => {
  const stringValue = value === undefined || value === null ? undefined : String(value)
  const optionValueMap = options.reduce<Record<string, string | boolean>>((acc, option) => {
    acc[String(option.value)] = option.value
    return acc
  }, {})

  return (
    <Select
      value={stringValue || CLEAR_VALUE}
      onValueChange={(selected) => {
        if (selected === CLEAR_VALUE) {
          onChange(null)
          return
        }
        if (Object.prototype.hasOwnProperty.call(optionValueMap, selected)) {
          onChange(optionValueMap[selected])
          return
        }
        onChange(selected)
      }}
    >
      <SelectTrigger>
        <SelectValue placeholder={placeholder} />
      </SelectTrigger>
      <SelectContent>
        <SelectItem value={CLEAR_VALUE}>Не указано</SelectItem>
        {options.map((option) => (
          <SelectItem key={String(option.value)} value={String(option.value)}>
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}