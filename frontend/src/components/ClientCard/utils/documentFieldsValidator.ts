import type { FormValues } from "../types";

export type DocumentStatus = "empty" | "partial" | "complete";

export interface MissingField {
  fieldId: string;
  label: string;
  tab: string;
  category: string;
  accordion?: string;
}

export interface DocumentCompleteness {
  status: DocumentStatus;
  totalFields: number;
  filledFields: number;
  missingFields: MissingField[];
}

/**
 * Проверяет, заполнено ли значение поля
 */
const isFieldFilled = (value: unknown): boolean => {
  if (value === null || value === undefined) {
    return false;
  }
  if (typeof value === "string") {
    return value.trim() !== "";
  }
  if (typeof value === "boolean") {
    return true;
  }
  if (typeof value === "number") {
    return true;
  }
  if (Array.isArray(value)) {
    return value.length > 0;
  }
  if (typeof value === "object") {
    return Object.keys(value).length > 0;
  }
  return false;
};

/**
 * Получает значение поля по fieldId из formValues
 */
const getFieldValueByFieldId = (fieldId: string, formValues: FormValues): unknown => {
  const parts = fieldId.split(".");
  let current: any = formValues;

  for (const part of parts) {
    if (current === null || current === undefined) {
      return null;
    }
    current = current[part];
  }

  return current;
};

/**
 * Получает лейбл инпута по fieldId из DOM
 */
const getInputLabelByFieldId = (fieldId: string): string | null => {
  // Специальная обработка для массивов
  const lastPart = fieldId.split(".").pop() || "";
  
  if (["creditors", "creditorsClaims"].includes(lastPart)) {
    if (lastPart === "creditors") {
      return "Кредиторы";
    }
    if (lastPart === "creditorsClaims") {
      return "Требования кредиторов";
    }
  }

  // Ищем label по атрибуту for
  const label = document.querySelector(`[for="${fieldId}"]`);
  
  if (label) {
    const text = label.textContent?.trim();
    if (text) {
      // Убираем звёздочки и лишние пробелы
      return text.replace(/\*/g, '').trim();
    }
  }

  // Если не нашли - возвращаем null (поле не будет показано)
  return null;
};

/**
 * Определяет местоположение поля (таб и аккордеон)
 */
const getFieldLocation = (fieldId: string): { tab: string; category: string; accordion?: string } => {
  const parts = fieldId.split(".");
  const category = parts[0];
  const tab = category;
  let accordion: string | undefined;

  let element = document.getElementById(fieldId);
  if (!element) {
    element = document.querySelector(`[name="${fieldId}"]`) as HTMLElement | null;
  }

  if (element) {
    const accordionItem = element.closest('[data-radix-accordion-item]');
    if (accordionItem) {
      accordion = accordionItem.getAttribute('data-value') || undefined;
    }
  }

  return { tab, category, accordion };
};

/**
 * Проверяет заполненность документа на основе полей из бека
 */
export const calculateDocumentCompleteness = (
  documentFields: string[],
  formValues: FormValues
): DocumentCompleteness => {
  const missingFields: MissingField[] = [];
  const totalFields = documentFields.length;

  documentFields.forEach((fieldId) => {
    const value = getFieldValueByFieldId(fieldId, formValues);

    if (!isFieldFilled(value)) {
      const label = getInputLabelByFieldId(fieldId);
      
      // Если label не найден (поле условно рендерится), пропускаем
      if (!label) {
        return;
      }
      
      const location = getFieldLocation(fieldId);
      missingFields.push({
        fieldId,
        label,
        ...location,
      });
    }
  });

  const filledFields = totalFields - missingFields.length;

  let status: DocumentStatus = "empty";
  if (filledFields === totalFields && totalFields > 0) {
    status = "complete";
  } else if (filledFields > 0) {
    status = "partial";
  }

  return {
    status,
    totalFields,
    filledFields,
    missingFields,
  };
};

