import { Controller, useFormContext, useWatch } from "react-hook-form"

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion"
import { DatePickerInput } from "@/components/ui/DatePickerInput"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { FormValues } from "../types"
import { calculateRegistryClosingDate, isNegativeNumber } from "../utils/judicialCalculations"

type IntroductionDateField =
  | "judicial_procedure_initiation.procedureInitiationEfrsbMessageDate"
  | "judicial_procedure_initiation.procedureInitiationKommersantPublicationDate"
  | "judicial_procedure_initiation.procedureInitiationCreditorsNotificationDate"

type IntroductionTextField =
  | "judicial_procedure_initiation.procedureInitiationEfrsbMessageNumber"
  | "judicial_procedure_initiation.procedureInitiationKommersantIssueNumber"
  | "judicial_procedure_initiation.procedureInitiationKommersantAdNumber"

const SECTION_VALUES = ["publications", "creditorsNotification", "registryClosing"]

export const IntroductionReportSections = (): JSX.Element => {
  const { control, register } = useFormContext<FormValues>()

  const [publicationDate, publicationCost] = useWatch({
    control,
    name: [
      "judicial_procedure_initiation.procedureInitiationKommersantPublicationDate",
      "judicial_procedure_initiation.procedureInitiationKommersantPublicationCost",
    ],
  })
  const registryClosingDate = calculateRegistryClosingDate(publicationDate)

  const renderDateField = (name: IntroductionDateField, label: string) => (
    <Controller
      name={name}
      control={control}
      render={({ field }) => (
        <DatePickerInput
          id={name}
          name={name}
          label={label}
          value={field.value ?? ""}
          onChange={field.onChange}
        />
      )}
    />
  )

  const renderTextField = (name: IntroductionTextField, label: string, placeholder: string) => (
    <div className="space-y-2">
      <Label htmlFor={name}>{label}</Label>
      <Input id={name} placeholder={placeholder} {...register(name)} />
    </div>
  )

  return (
    <Accordion type="multiple" defaultValue={SECTION_VALUES} className="mt-6">
      <AccordionItem value="publications">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Публикации</h3>
        </AccordionTrigger>
        <AccordionContent className="space-y-6">
          <div className="space-y-4">
            <h4 className="font-semibold">ЕФРСБ</h4>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              {renderTextField("judicial_procedure_initiation.procedureInitiationEfrsbMessageNumber", "Номер сообщения", "3134699")}
              {renderDateField("judicial_procedure_initiation.procedureInitiationEfrsbMessageDate", "Дата сообщения")}
            </div>
          </div>
          <div className="space-y-4">
            <h4 className="font-semibold">Коммерсантъ</h4>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              {renderTextField("judicial_procedure_initiation.procedureInitiationKommersantIssueNumber", "Номер издания", "139 (7101)")}
              {renderTextField("judicial_procedure_initiation.procedureInitiationKommersantAdNumber", "Номер объявления", "78230149776")}
              {renderDateField("judicial_procedure_initiation.procedureInitiationKommersantPublicationDate", "Дата публикации")}
              <div className="space-y-2">
                <Label htmlFor="judicial_procedure_initiation.procedureInitiationKommersantPublicationCost">
                  Стоимость публикации, руб.
                </Label>
                <Input
                  id="judicial_procedure_initiation.procedureInitiationKommersantPublicationCost"
                  type="number"
                  min={0}
                  step="0.01"
                  placeholder="7866.05"
                  {...register("judicial_procedure_initiation.procedureInitiationKommersantPublicationCost")}
                />
                {isNegativeNumber(publicationCost) && (
                  <p className="text-sm text-destructive">Сумма не может быть отрицательной</p>
                )}
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="creditorsNotification">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Уведомление кредиторов почтой</h3>
        </AccordionTrigger>
        <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {renderDateField("judicial_procedure_initiation.procedureInitiationCreditorsNotificationDate", "Дата направления уведомлений")}
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="registryClosing">
        <AccordionTrigger>
          <h3 className="text-xl font-semibold">Дата закрытия реестра</h3>
        </AccordionTrigger>
        <AccordionContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {registryClosingDate ? (
            <div className="space-y-2">
              <Label htmlFor="registryClosingDate">Дата закрытия реестра</Label>
              <Input id="registryClosingDate" value={registryClosingDate} readOnly />
              <p className="text-sm text-muted-foreground">Дата публикации в «Коммерсантъ» плюс два месяца</p>
            </div>
          ) : (
            <p className="text-sm text-muted-foreground">Заполните дату публикации в «Коммерсантъ»</p>
          )}
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  )
}
