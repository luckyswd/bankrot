import { Trash2 } from "lucide-react"
import { Controller, useFormContext, useWatch } from "react-hook-form"

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion"
import { Button } from "@/components/ui/button"
import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { FormValues, ProcedureExtensionStatus } from "../types"
import {
  calculateClaimsConsidered,
  calculateUnpaid,
  formatMoney,
  isPaidOverAmount,
  toDateOnly,
} from "../utils/judicialCalculations"

type ProcedureDateField =
  | "judicial_procedure.propertyInventoryDate"
  | "judicial_procedure.zagsCertificatePeriodFrom"
  | "judicial_procedure.zagsCertificatePeriodTo"
  | "judicial_procedure.bankruptcySignsEfrsbPublicationDate"

type ExpenseAmountField =
  | "judicial_procedure.efrsbExpensesAmount"
  | "judicial_procedure.postalExpensesAmount"
  | "judicial_procedure.futureEfrsbExpensesAmount"

type ExpensePaidField =
  | "judicial_procedure.efrsbExpensesPaid"
  | "judicial_procedure.postalExpensesPaid"
  | "judicial_procedure.newspaperExpensesPaid"
  | "judicial_procedure.futureEfrsbExpensesPaid"

type ExpenseRow = {
  title: string
  amountField: ExpenseAmountField | null
  paidField: ExpensePaidField
}

const EXPENSE_FIELDS = [
  "judicial_procedure.efrsbExpensesAmount",
  "judicial_procedure.efrsbExpensesPaid",
  "judicial_procedure.postalExpensesAmount",
  "judicial_procedure.postalExpensesPaid",
  "judicial_procedure.newspaperExpensesPaid",
  "judicial_procedure.futureEfrsbExpensesAmount",
  "judicial_procedure.futureEfrsbExpensesPaid",
] as const

const EXPENSE_ROWS: ExpenseRow[] = [
  {
    title: "Расходы на опубликование сообщений на ЕФРСБ",
    amountField: "judicial_procedure.efrsbExpensesAmount",
    paidField: "judicial_procedure.efrsbExpensesPaid",
  },
  {
    title: "Почтовые расходы",
    amountField: "judicial_procedure.postalExpensesAmount",
    paidField: "judicial_procedure.postalExpensesPaid",
  },
  {
    title: "Расходы на опубликование сообщений в газетах",
    amountField: null,
    paidField: "judicial_procedure.newspaperExpensesPaid",
  },
  {
    title: "Будущие расходы на опубликование сообщений на ЕФРСБ",
    amountField: "judicial_procedure.futureEfrsbExpensesAmount",
    paidField: "judicial_procedure.futureEfrsbExpensesPaid",
  },
]

const NOT_SELECTED = "none"

const EXTENSION_OPTIONS: Array<{ value: ProcedureExtensionStatus; label: string }> = [
  { value: "extended", label: "Процедура продлевалась" },
  { value: "not_extended", label: "Процедура реализации имущества не продлевалась" },
  { value: "no_acts", label: "Судебные акты не выносились" },
]

const SECTION_VALUES = [
  "procedureExtension",
  "propertyInventory",
  "governmentResponses",
  "claimsResults",
  "procedureExpenses",
  "bankruptcySigns",
]

export const ProcedureReportSections = (): JSX.Element => {
  const { control, register } = useFormContext<FormValues>()

  const [extensionStatus, zagsPeriodFrom, zagsPeriodTo] = useWatch({
    control,
    name: [
      "judicial_procedure.procedureExtensionStatus",
      "judicial_procedure.zagsCertificatePeriodFrom",
      "judicial_procedure.zagsCertificatePeriodTo",
    ],
  })
  const [claimsIncludedCount, claimsRejectedCount, kommersantPublicationCost] = useWatch({
    control,
    name: [
      "judicial_procedure.claimsIncludedCount",
      "judicial_procedure.claimsRejectedCount",
      "judicial_procedure_initiation.procedureInitiationKommersantPublicationCost",
    ],
  })
  const expenseValues = useWatch({ control, name: EXPENSE_FIELDS })
  const expenseValueByField = Object.fromEntries(
    EXPENSE_FIELDS.map((field, index) => [field, expenseValues[index]])
  ) as Record<ExpenseAmountField | ExpensePaidField, string | null | undefined>
  const claimsConsideredCount = calculateClaimsConsidered(claimsIncludedCount, claimsRejectedCount)

  const isZagsPeriodReversed =
    Boolean(zagsPeriodFrom) && Boolean(zagsPeriodTo) && toDateOnly(zagsPeriodFrom) > toDateOnly(zagsPeriodTo)

  const renderDateField = (name: ProcedureDateField, label: string) => (
    <Controller
      name={name}
      control={control}
      render={({ field }) => (
        <DatePickerInput id={name} name={name} label={label} value={field.value ?? ""} onChange={field.onChange} />
      )}
    />
  )

  return (
    <Accordion type="multiple" defaultValue={SECTION_VALUES} className="mt-6">
      <AccordionItem value="procedureExtension">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Продление процедуры</h3>
        </AccordionTrigger>
        <AccordionContent className="space-y-4">
          <Controller
            name="judicial_procedure.procedureExtensionStatus"
            control={control}
            render={({ field }) => (
              <div className="space-y-2 md:w-1/2">
                <Label htmlFor="judicial_procedure.procedureExtensionStatus">Продление процедуры</Label>
                <Select
                  value={field.value ?? NOT_SELECTED}
                  onValueChange={(value) => field.onChange(value === NOT_SELECTED ? null : value)}
                >
                  <SelectTrigger id="judicial_procedure.procedureExtensionStatus">
                    <SelectValue placeholder="Выберите вариант" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value={NOT_SELECTED}>Не выбрано</SelectItem>
                    {EXTENSION_OPTIONS.map((option) => (
                      <SelectItem key={option.value} value={option.value}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            )}
          />

          {extensionStatus === "extended" && (
            <Controller
              name="judicial_procedure.procedureExtensionDates"
              control={control}
              render={({ field }) => {
                const dates = field.value ?? []

                return (
                  <div className="space-y-3">
                    <p className="text-sm font-medium">Даты судебных актов о продлении</p>
                    {dates.map((date, index) => (
                      <div key={index} className="flex items-end gap-3 md:w-1/2">
                        <DatePickerInput
                          className="flex-1"
                          value={date}
                          onChange={(value) => field.onChange(dates.map((item, i) => (i === index ? value : item)))}
                        />
                        <Button
                          type="button"
                          variant="ghost"
                          size="icon"
                          className="text-red-400"
                          onClick={() => field.onChange(dates.filter((_, i) => i !== index))}
                          aria-label={`Удалить дату ${index + 1}`}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    ))}
                    <Button type="button" variant="outline" onClick={() => field.onChange([...dates, ""])}>
                      Добавить дату
                    </Button>
                  </div>
                )
              }}
            />
          )}
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="propertyInventory">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Опись имущества</h3>
        </AccordionTrigger>
        <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {renderDateField("judicial_procedure.propertyInventoryDate", "Дата описи")}
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="governmentResponses">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Ответы из госорганов</h3>
        </AccordionTrigger>
        <AccordionContent className="space-y-4">
          <h4 className="font-semibold">ЗАГС</h4>
          <div className="space-y-2">
            <Label htmlFor="judicial_procedure.zagsDepartment">Отдел ЗАГС</Label>
            <Input id="judicial_procedure.zagsDepartment" {...register("judicial_procedure.zagsDepartment")} />
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {renderDateField("judicial_procedure.zagsCertificatePeriodFrom", "Справка за период с")}
            <div className="space-y-2">
              {renderDateField("judicial_procedure.zagsCertificatePeriodTo", "Справка за период по")}
              {isZagsPeriodReversed && (
                <p className="text-sm text-destructive">Дата начала периода позже даты окончания</p>
              )}
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="claimsResults">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Результаты рассмотрения требований</h3>
        </AccordionTrigger>
        <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div className="space-y-2">
            <Label htmlFor="judicial_procedure.claimsIncludedCount">Включено в реестр</Label>
            <Input
              id="judicial_procedure.claimsIncludedCount"
              type="number"
              min={0}
              step={1}
              {...register("judicial_procedure.claimsIncludedCount")}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="judicial_procedure.claimsRejectedCount">Отказано во включении в реестр</Label>
            <Input
              id="judicial_procedure.claimsRejectedCount"
              type="number"
              min={0}
              step={1}
              {...register("judicial_procedure.claimsRejectedCount")}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="claimsConsideredCount">Всего рассмотрено в арбитражном суде</Label>
            <Input id="claimsConsideredCount" value={claimsConsideredCount} readOnly />
          </div>
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="procedureExpenses">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Расходы на проведение процедуры</h3>
        </AccordionTrigger>
        <AccordionContent className="space-y-4">
          <div className="hidden gap-4 text-sm font-medium text-muted-foreground md:grid md:grid-cols-4">
            <span>Позиция</span>
            <span>Размер обязательства, руб.</span>
            <span>Погашено, руб.</span>
            <span>Непогашенный остаток, руб.</span>
          </div>
          {EXPENSE_ROWS.map((row) => {
            const amount = row.amountField ? expenseValueByField[row.amountField] : kommersantPublicationCost
            const paid = expenseValueByField[row.paidField]

            return (
              <div key={row.paidField} className="grid grid-cols-1 items-start gap-4 border-b pb-4 md:grid-cols-4">
                <p className="text-sm font-medium">{row.title}</p>
                <div className="space-y-1">
                  {row.amountField ? (
                    <>
                      <Label htmlFor={row.amountField} className="md:sr-only">
                        {row.title} — размер обязательства
                      </Label>
                      <Input id={row.amountField} type="number" min={0} step="0.01" {...register(row.amountField)} />
                    </>
                  ) : (
                    <>
                      <Input value={formatMoney(kommersantPublicationCost)} readOnly aria-label={`${row.title} — размер обязательства`} />
                      <p className="text-xs text-muted-foreground">Из публикации в «Коммерсантъ»</p>
                    </>
                  )}
                </div>
                <div className="space-y-1">
                  <Label htmlFor={row.paidField} className="md:sr-only">
                    {row.title} — погашено
                  </Label>
                  <Input id={row.paidField} type="number" min={0} step="0.01" {...register(row.paidField)} />
                  {isPaidOverAmount(amount, paid) && (
                    <p className="text-sm text-destructive">Погашено больше размера обязательства</p>
                  )}
                </div>
                <Input value={calculateUnpaid(amount, paid)} readOnly aria-label={`${row.title} — непогашенный остаток`} />
              </div>
            )
          })}
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="bankruptcySigns">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Фиктивные и преднамеренные признаки банкротства</h3>
        </AccordionTrigger>
        <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {renderDateField("judicial_procedure.bankruptcySignsEfrsbPublicationDate", "Дата публикации в ЕФРСБ")}
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  )
}
