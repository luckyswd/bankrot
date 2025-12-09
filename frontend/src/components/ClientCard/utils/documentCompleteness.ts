import type { FormValues, PrimaryInfoFields, PretrialFields, IntroductionFields, ProcedureFields } from "../types";

export type DocumentStatus = "empty" | "partial" | "complete";

export interface MissingField {
  label: string;
  fieldName: string;
  tab: string;
  accordion?: string;
  fieldId: string;
}

export interface DocumentCompleteness {
  status: DocumentStatus;
  percentage: number;
  missingFields: MissingField[];
}

// Маппинг полей к табам и аккордеонам
interface FieldLocation {
  label: string;
  tab: string;
  accordion?: string;
  fieldId: string;
}

const fieldLocations: Record<string, FieldLocation> = {
  // Basic Info - MainInfo
  lastName: { label: "Фамилия", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.lastName" },
  firstName: { label: "Имя", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.firstName" },
  middleName: { label: "Отчество", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.middleName" },
  lastNameGenitive: { label: "Фамилия (род. п.)", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.lastNameGenitive" },
  firstNameGenitive: { label: "Имя (род. п.)", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.firstNameGenitive" },
  middleNameGenitive: { label: "Отчество (род. п.)", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.middleNameGenitive" },
  birthDate: { label: "Дата рождения", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.birthDate" },
  birthPlace: { label: "Место рождения", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.birthPlace" },
  snils: { label: "СНИЛС", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.snils" },
  gender: { label: "Пол", tab: "primary", accordion: "mainInfo", fieldId: "basic_info.gender" },
  // Basic Info - AddressInfo
  registrationRegion: { label: "Субъект РФ", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationRegion" },
  registrationDistrict: { label: "Район", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationDistrict" },
  registrationCity: { label: "Город", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationCity" },
  registrationSettlement: { label: "Населенный пункт", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationSettlement" },
  registrationStreet: { label: "Улица", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationStreet" },
  registrationHouse: { label: "Дом", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationHouse" },
  registrationBuilding: { label: "Корпус", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationBuilding" },
  registrationApartment: { label: "Квартира", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.registrationApartment" },
  postalCode: { label: "Почтовый индекс", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.postalCode" },
  actualPlaceResidence: { label: "Фактическое место проживания", tab: "primary", accordion: "addressInfo", fieldId: "basic_info.actualPlaceResidence"},
  // Basic Info - PassportInfo
  passportSeries: { label: "Серия паспорта", tab: "primary", accordion: "passportInfo", fieldId: "basic_info.passportSeries" },
  passportNumber: { label: "Номер паспорта", tab: "primary", accordion: "passportInfo", fieldId: "basic_info.passportNumber" },
  passportIssuedBy: { label: "Кем выдан паспорт", tab: "primary", accordion: "passportInfo", fieldId: "basic_info.passportIssuedBy" },
  passportIssuedDate: { label: "Дата выдачи паспорта", tab: "primary", accordion: "passportInfo", fieldId: "basic_info.passportIssuedDate" },
  passportDepartmentCode: { label: "Код подразделения", tab: "primary", accordion: "passportInfo", fieldId: "basic_info.passportDepartmentCode" },
  // Basic Info - FamilyInfo
  maritalStatus: { label: "Семейное положение", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.maritalStatus" },
  spouseFullName: { label: "ФИО супруга", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.spouseFullName" },
  spouseBirthDate: { label: "Дата рождения супруга", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.spouseBirthDate" },
  marriageTerminationDate: { label: "Дата расторжения брака", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.marriageTerminationDate" },
  hasMinorChildren: { label: "Наличие несовершеннолетних детей", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.hasMinorChildren" },
  children: { label: "Дети", tab: "primary", accordion: "familyInfo", fieldId: "basic_info.children" },
  // Basic Info - WorkInfo
  isStudent: { label: "Является ли студентом", tab: "primary", accordion: "workInfo", fieldId: "basic_info.isStudent" },
  employerName: { label: "Наименование работодателя", tab: "primary", accordion: "workInfo", fieldId: "basic_info.employerName" },
  employerAddress: { label: "Адрес работодателя", tab: "primary", accordion: "workInfo", fieldId: "basic_info.employerAddress" },
  employerInn: { label: "ИНН работодателя", tab: "primary", accordion: "workInfo", fieldId: "basic_info.employerInn" },
  socialBenefits: { label: "Пенсии и социальные выплаты", tab: "primary", accordion: "workInfo", fieldId: "basic_info.socialBenefits" },
  // Basic Info - ContactInfo
  phone: { label: "Телефон", tab: "primary", accordion: "contactInfo", fieldId: "basic_info.phone" },
  email: { label: "Электронная почта", tab: "primary", accordion: "contactInfo", fieldId: "basic_info.email" },
  mailingAddress: { label: "Адрес для корреспонденции", tab: "primary", accordion: "contactInfo", fieldId: "basic_info.mailingAddress" },
  // Basic Info - DeptsInfo
  debtAmount: { label: "Сумма долга", tab: "primary", accordion: "deptsInfo", fieldId: "basic_info.debtAmount" },
  hasEnforcementProceedings: { label: "Наличие исполнительных производств", tab: "primary", accordion: "deptsInfo", fieldId: "basic_info.hasEnforcementProceedings" },
  // Pre Court
  court: { label: "Суд", tab: "pre_court", fieldId: "pre_court.court" },
  creditors: { label: "Кредиторы", tab: "pre_court", fieldId: "pre_court.creditors" },
  powerOfAttorneyNumber: { label: "Номер доверенности", tab: "pre_court", fieldId: "pre_court.powerOfAttorneyNumber" },
  caseNumber: { label: "Номер дела", tab: "pre_court", fieldId: "pre_court.caseNumber" },
  powerOfAttorneyDate: { label: "Дата доверенности", tab: "pre_court", fieldId: "pre_court.powerOfAttorneyDate" },
  efrsbCabinet: { label: "Кабинет ЕФРСБ", tab: "pre_court", fieldId: "pre_court.efrsbCabinet" },
  efrsbDateTime: { label: "Дата и время ЕФРСБ", tab: "pre_court", fieldId: "pre_court.efrsbDateTime" },
  hearingDateTime: { label: "Дата и время заседания", tab: "pre_court", fieldId: "pre_court.hearingDateTime" },
  // Judicial Procedure Initiation
  procedureInitiationDecisionDate: { label: "Дата принятия решения", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationDecisionDate" },
  procedureInitiationResolutionDate: { label: "Дата постановления", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationResolutionDate" },
  procedureInitiationMchs: { label: "МЧС", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationMchs" },
  procedureInitiationGostekhnadzor: { label: "Гостехнадзор", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationGostekhnadzor" },
  procedureInitiationFns: { label: "ФНС", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationFns" },
  procedureInitiationDocNumber: { label: "Номер документа", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationDocNumber" },
  procedureInitiationCaseNumber: { label: "Номер дела", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationCaseNumber" },
  procedureInitiationDuration: { label: "Срок", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationDuration" },
  procedureInitiationRosgvardia: { label: "Росгвардия", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationRosgvardia" },
  procedureInitiationJudge: { label: "Судья", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationJudge" },
  procedureInitiationBailiff: { label: "Судебный пристав", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationBailiff" },
  executionNumber: { label: "Номер исполнительного производства", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.executionNumber" },
  executionDate: { label: "Дата исполнительного производства", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.executionDate" },
  procedureInitiationSpecialAccountNumber: { label: "Номер спец счёта", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationSpecialAccountNumber" },
  procedureInitiationIPEndings: { label: "Окончания исполнительных производств", tab: "judicial", accordion: "introduction", fieldId: "judicial_procedure_initiation.procedureInitiationIPEndings" },
  // Judicial Procedure
  creditorsClaims: { label: "Требования кредиторов", tab: "judicial", accordion: "procedure", fieldId: "judicial_procedure.creditorsClaims" },
};

// Маппинг названий полей для отображения (для обратной совместимости)
const fieldLabels: Record<string, string> = {
  // Basic Info
  lastName: "Фамилия",
  firstName: "Имя",
  middleName: "Отчество",
  lastNameGenitive: "Фамилия (род. п.)",
  firstNameGenitive: "Имя (род. п.)",
  middleNameGenitive: "Отчество (род. п.)",
  birthDate: "Дата рождения",
  birthPlace: "Место рождения",
  snils: "СНИЛС",
  gender: "Пол",
  registrationRegion: "Субъект РФ",
  registrationDistrict: "Район",
  registrationCity: "Город",
  registrationSettlement: "Населенный пункт",
  registrationStreet: "Улица",
  registrationHouse: "Дом",
  registrationBuilding: "Корпус",
  registrationApartment: "Квартира",
  postalCode: "Почтовый индекс",
  passportSeries: "Серия паспорта",
  passportNumber: "Номер паспорта",
  passportIssuedBy: "Кем выдан паспорт",
  passportIssuedDate: "Дата выдачи паспорта",
  passportDepartmentCode: "Код подразделения",
  maritalStatus: "Семейное положение",
  spouseFullName: "ФИО супруга",
  spouseBirthDate: "Дата рождения супруга",
  marriageTerminationDate: "Дата расторжения брака",
  hasMinorChildren: "Наличие несовершеннолетних детей",
  children: "Дети",
  isStudent: "Является ли студентом",
  employerName: "Наименование работодателя",
  employerAddress: "Адрес работодателя",
  employerInn: "ИНН работодателя",
  socialBenefits: "Пенсии и социальные выплаты",
  phone: "Телефон",
  email: "Электронная почта",
  mailingAddress: "Адрес для корреспонденции",
  debtAmount: "Сумма долга",
  hasEnforcementProceedings: "Наличие исполнительных производств",
  // Pre Court
  court: "Суд",
  creditors: "Кредиторы",
  powerOfAttorneyNumber: "Номер доверенности",
  caseNumber: "Номер дела",
  powerOfAttorneyDate: "Дата доверенности",
  efrsbCabinet: "Кабинет ЕФРСБ",
  efrsbDateTime: "Дата и время ЕФРСБ",
  hearingDateTime: "Дата и время заседания",
  // Judicial Procedure Initiation
  procedureInitiationDecisionDate: "Дата принятия решения",
  procedureInitiationResolutionDate: "Дата постановления",
  procedureInitiationMchs: "МЧС",
  procedureInitiationGostekhnadzor: "Гостехнадзор",
  procedureInitiationFns: "ФНС",
  procedureInitiationDocNumber: "Номер документа",
  procedureInitiationCaseNumber: "Номер дела",
  procedureInitiationDuration: "Срок",
  procedureInitiationRosgvardia: "Росгвардия",
  procedureInitiationJudge: "Судья",
  procedureInitiationBailiff: "Судебный пристав",
  executionNumber: "Номер исполнительного производства",
  executionDate: "Дата исполнительного производства",
  procedureInitiationSpecialAccountNumber: "Номер спец счёта",
  procedureInitiationIPEndings: "Окончания исполнительных производств",
  // Judicial Procedure
  creditorsClaims: "Требования кредиторов",
};

// Список обязательных полей для каждой категории
const requiredFieldsByCategory: Record<string, string[]> = {
  basic_info: [
    "lastName",
    "firstName",
    "middleName",
    "lastNameGenitive",
    "firstNameGenitive",
    "middleNameGenitive",
    "birthDate",
    "snils",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "passportDepartmentCode",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
    "postalCode",
  ],
  pre_court: [
    "court",
    "creditors",
    "powerOfAttorneyNumber",
    "caseNumber",
    "powerOfAttorneyDate",
    "hearingDateTime",
  ],
  judicial_procedure_initiation: [
    "procedureInitiationDecisionDate",
    "procedureInitiationResolutionDate",
    "procedureInitiationMchs",
    "procedureInitiationGostekhnadzor",
    "procedureInitiationFns",
    "procedureInitiationDocNumber",
    "procedureInitiationCaseNumber",
    "procedureInitiationDuration",
    "procedureInitiationRosgvardia",
    "procedureInitiationJudge",
    "procedureInitiationBailiff",
    "procedureInitiationSpecialAccountNumber",
  ],
  judicial_procedure: [
    "creditorsClaims",
  ],
};

// Проверка заполненности значения
const isFieldFilled = (value: unknown): boolean => {
  if (value === null || value === undefined) {
    return false;
  }
  if (typeof value === "string") {
    return value.trim() !== "";
  }
  if (typeof value === "boolean") {
    return true; // boolean всегда заполнен
  }
  if (Array.isArray(value)) {
    return value.length > 0;
  }
  if (typeof value === "object") {
    return Object.keys(value).length > 0;
  }
  return true;
};

// Вычисление заполненности для basic_info
const calculateBasicInfoCompleteness = (data: PrimaryInfoFields): DocumentCompleteness => {
  const requiredFields = requiredFieldsByCategory.basic_info;
  const missingFields: MissingField[] = [];

  requiredFields.forEach((field) => {
    const value = (data as Record<string, unknown>)[field];
    if (!isFieldFilled(value)) {
      const location = fieldLocations[field];
      if (location) {
        missingFields.push({
          label: location.label,
          fieldName: field,
          tab: location.tab,
          accordion: location.accordion,
          fieldId: location.fieldId,
        });
      } else {
        missingFields.push({
          label: fieldLabels[field] || field,
          fieldName: field,
          tab: "primary",
          fieldId: `basic_info.${field}`,
        });
      }
    }
  });

  const filledCount = requiredFields.length - missingFields.length;
  const percentage = Math.round((filledCount / requiredFields.length) * 100);

  let status: DocumentStatus = "empty";
  if (percentage === 100) {
    status = "complete";
  } else if (percentage > 0) {
    status = "partial";
  }

  return {
    status,
    percentage,
    missingFields,
  };
};

// Вычисление заполненности для pre_court
const calculatePreCourtCompleteness = (data: PretrialFields): DocumentCompleteness => {
  const requiredFields = requiredFieldsByCategory.pre_court;
  const missingFields: MissingField[] = [];

  requiredFields.forEach((field) => {
    const value = (data as Record<string, unknown>)[field];
    if (!isFieldFilled(value)) {
      const location = fieldLocations[field];
      if (location) {
        missingFields.push({
          label: location.label,
          fieldName: field,
          tab: location.tab,
          accordion: location.accordion,
          fieldId: location.fieldId,
        });
      } else {
        missingFields.push({
          label: fieldLabels[field] || field,
          fieldName: field,
          tab: "pre_court",
          fieldId: `pre_court.${field}`,
        });
      }
    }
  });

  const filledCount = requiredFields.length - missingFields.length;
  const percentage = Math.round((filledCount / requiredFields.length) * 100);

  let status: DocumentStatus = "empty";
  if (percentage === 100) {
    status = "complete";
  } else if (percentage > 0) {
    status = "partial";
  }

  return {
    status,
    percentage,
    missingFields,
  };
};

// Вычисление заполненности для judicial_procedure_initiation
const calculateJudicialProcedureInitiationCompleteness = (data: IntroductionFields): DocumentCompleteness => {
  const requiredFields = requiredFieldsByCategory.judicial_procedure_initiation;
  const missingFields: MissingField[] = [];

  requiredFields.forEach((field) => {
    const value = (data as Record<string, unknown>)[field];
    if (!isFieldFilled(value)) {
      const location = fieldLocations[field];
      if (location) {
        missingFields.push({
          label: location.label,
          fieldName: field,
          tab: location.tab,
          accordion: location.accordion,
          fieldId: location.fieldId,
        });
      } else {
        missingFields.push({
          label: fieldLabels[field] || field,
          fieldName: field,
          tab: "judicial",
          accordion: "introduction",
          fieldId: `judicial_procedure_initiation.${field}`,
        });
      }
    }
  });

  const filledCount = requiredFields.length - missingFields.length;
  const percentage = Math.round((filledCount / requiredFields.length) * 100);

  let status: DocumentStatus = "empty";
  if (percentage === 100) {
    status = "complete";
  } else if (percentage > 0) {
    status = "partial";
  }

  return {
    status,
    percentage,
    missingFields,
  };
};

// Вычисление заполненности для judicial_procedure
const calculateJudicialProcedureCompleteness = (data: ProcedureFields): DocumentCompleteness => {
  const requiredFields = requiredFieldsByCategory.judicial_procedure;
  const missingFields: MissingField[] = [];

  requiredFields.forEach((field) => {
    const value = (data as Record<string, unknown>)[field];
    if (!isFieldFilled(value)) {
      const location = fieldLocations[field];
      if (location) {
        missingFields.push({
          label: location.label,
          fieldName: field,
          tab: location.tab,
          accordion: location.accordion,
          fieldId: location.fieldId,
        });
      } else {
        missingFields.push({
          label: fieldLabels[field] || field,
          fieldName: field,
          tab: "judicial",
          accordion: "procedure",
          fieldId: `judicial_procedure.${field}`,
        });
      }
    } else if (field === "creditorsClaims" && Array.isArray(value)) {
      // Проверяем, что есть хотя бы одно требование кредитора с заполненными обязательными полями
      const hasValidClaim = value.some((claim: any) => 
        claim && 
        claim.creditorId && 
        (claim.debtAmount || claim.principalAmount)
      );
      if (!hasValidClaim) {
        const location = fieldLocations[field];
        if (location) {
          missingFields.push({
            label: location.label,
            fieldName: field,
            tab: location.tab,
            accordion: location.accordion,
            fieldId: location.fieldId,
          });
        } else {
          missingFields.push({
            label: fieldLabels[field] || field,
            fieldName: field,
            tab: "judicial",
            accordion: "procedure",
            fieldId: `judicial_procedure.${field}`,
          });
        }
      }
    }
  });

  const filledCount = requiredFields.length - missingFields.length;
  const percentage = Math.round((filledCount / requiredFields.length) * 100);

  let status: DocumentStatus = "empty";
  if (percentage === 100) {
    status = "complete";
  } else if (percentage > 0) {
    status = "partial";
  }

  return {
    status,
    percentage,
    missingFields,
  };
};

// Маппинг документов к их обязательным полям из всех категорий
// Ключ - ID документа или категория (если документ не указан)
// Значение - массив полей из разных категорий
const documentFieldsMap: Record<string | number, string[]> = {
  // Документы основной информации требуют только поля basic_info
  basic_info: [
    "lastName",
    "firstName",
    "middleName",
    "lastNameGenitive",
    "firstNameGenitive",
    "middleNameGenitive",
    "birthDate",
    "snils",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "passportDepartmentCode",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
    "postalCode",
  ],
  // Документы досудебки требуют поля pre_court + базовые поля из basic_info
  pre_court: [
    // Поля из pre_court
    "court",
    "creditors",
    "powerOfAttorneyNumber",
    "caseNumber",
    "powerOfAttorneyDate",
    "hearingDateTime",
    // Базовые поля из basic_info (обычно нужны для всех документов)
    "lastName",
    "firstName",
    "middleName",
    "birthDate",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
  ],
  // Документы введения процедуры требуют поля judicial_procedure_initiation + базовые поля
  judicial_procedure_initiation: [
    // Поля из judicial_procedure_initiation
    "procedureInitiationDecisionDate",
    "procedureInitiationResolutionDate",
    "procedureInitiationMchs",
    "procedureInitiationGostekhnadzor",
    "procedureInitiationFns",
    "procedureInitiationDocNumber",
    "procedureInitiationCaseNumber",
    "procedureInitiationDuration",
    "procedureInitiationRosgvardia",
    "procedureInitiationJudge",
    "procedureInitiationBailiff",
    "procedureInitiationSpecialAccountNumber",
    // Базовые поля из basic_info
    "lastName",
    "firstName",
    "middleName",
    "birthDate",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
  ],
  // Документы процедуры требуют поля judicial_procedure + базовые поля
  judicial_procedure: [
    // Поля из judicial_procedure
    "creditorsClaims",
    // Базовые поля из basic_info
    "lastName",
    "firstName",
    "middleName",
    "birthDate",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
  ],
  // Документы отчетов требуют данные процедуры + базовые поля
  judicial_report: [
    "creditorsClaims",
    "lastName",
    "firstName",
    "middleName",
    "birthDate",
    "passportSeries",
    "passportNumber",
    "passportIssuedBy",
    "passportIssuedDate",
    "registrationRegion",
    "registrationCity",
    "registrationStreet",
    "registrationHouse",
  ],
};

// Функция для получения категории поля по его имени
const getFieldCategory = (fieldName: string): string => {
  // Если поле содержит точку, извлекаем категорию из префикса
  if (fieldName.includes(".")) {
    if (fieldName.startsWith("basic_info.")) return "basic_info";
    if (fieldName.startsWith("pre_court.")) return "pre_court";
    if (fieldName.startsWith("judicial_procedure_initiation.")) return "judicial_procedure_initiation";
    if (fieldName.startsWith("judicial_procedure.")) return "judicial_procedure";
  }
  
  // Определяем по имени поля через fieldLocations
  if (fieldLocations[fieldName]) {
    const location = fieldLocations[fieldName];
    if (location.tab === "primary") return "basic_info";
    if (location.tab === "pre_court") return "pre_court";
    if (location.tab === "judicial") {
      if (location.accordion === "introduction") return "judicial_procedure_initiation";
      if (location.accordion === "procedure") return "judicial_procedure";
      return "judicial_report";
    }
  }
  
  // По умолчанию считаем, что поле из basic_info
  return "basic_info";
};

// Функция для получения значения поля из formValues
const getFieldValue = (fieldName: string, formValues: FormValues): unknown => {
  const category = getFieldCategory(fieldName);
  // Извлекаем имя поля без префикса категории
  const actualFieldName = fieldName.includes(".") ? fieldName.split(".").pop() || fieldName : fieldName;
  
  switch (category) {
    case "basic_info":
      return (formValues.basic_info as Record<string, unknown>)[actualFieldName];
    case "pre_court":
      return (formValues.pre_court as Record<string, unknown>)[actualFieldName];
    case "judicial_procedure_initiation":
      return (formValues.judicial_procedure_initiation as Record<string, unknown>)[actualFieldName];
    case "judicial_procedure":
      return (formValues.judicial_procedure as Record<string, unknown>)[actualFieldName];
    default:
      return undefined;
  }
};

// Основная функция для вычисления заполненности документа
export const calculateDocumentCompleteness = (
  category: string,
  formValues: FormValues,
  documentId?: number
): DocumentCompleteness => {
  // Получаем список обязательных полей для документа
  // Если documentId указан и есть в маппинге, используем его, иначе используем category
  const requiredFields = documentFieldsMap[documentId || category] || documentFieldsMap[category] || [];
  
  if (requiredFields.length === 0) {
    // Fallback к старой логике, если документ не найден в маппинге
    switch (category) {
      case "basic_info":
        return calculateBasicInfoCompleteness(formValues.basic_info);
      case "pre_court":
        return calculatePreCourtCompleteness(formValues.pre_court);
      case "judicial_procedure_initiation":
        return calculateJudicialProcedureInitiationCompleteness(formValues.judicial_procedure_initiation);
      case "judicial_procedure":
        return calculateJudicialProcedureCompleteness(formValues.judicial_procedure);
      case "judicial_report":
        const hasClaims = formValues.judicial_procedure?.creditorsClaims && formValues.judicial_procedure.creditorsClaims.length > 0;
        return {
          status: hasClaims ? "complete" : "empty",
          percentage: hasClaims ? 100 : 0,
          missingFields: hasClaims ? [] : [{
            label: "Требования кредиторов",
            fieldName: "creditorsClaims",
            tab: "judicial",
            accordion: "procedure",
            fieldId: "judicial_procedure.creditorsClaims",
          }],
        };
      default:
        return {
          status: "empty",
          percentage: 0,
          missingFields: [],
        };
    }
  }

  // Проверяем все поля из списка
  const missingFields: MissingField[] = [];

  requiredFields.forEach((field) => {
    const value = getFieldValue(field, formValues);
    
    // Специальная обработка для creditorsClaims
    if (field === "creditorsClaims") {
      const claims = formValues.judicial_procedure?.creditorsClaims;
      if (!claims || claims.length === 0) {
        const location = fieldLocations[field];
        if (location) {
          missingFields.push({
            label: location.label,
            fieldName: field,
            tab: location.tab,
            accordion: location.accordion,
            fieldId: location.fieldId,
          });
        }
      } else {
        // Проверяем, что есть хотя бы одно валидное требование
        const hasValidClaim = claims.some((claim: any) => 
          claim && 
          claim.creditorId && 
          (claim.debtAmount || claim.principalAmount)
        );
        if (!hasValidClaim) {
          const location = fieldLocations[field];
          if (location) {
            missingFields.push({
              label: location.label,
              fieldName: field,
              tab: location.tab,
              accordion: location.accordion,
              fieldId: location.fieldId,
            });
          }
        }
      }
    } else if (!isFieldFilled(value)) {
      const location = fieldLocations[field];
      if (location) {
        missingFields.push({
          label: location.label,
          fieldName: field,
          tab: location.tab,
          accordion: location.accordion,
          fieldId: location.fieldId,
        });
      } else {
        // Fallback для полей, которых нет в маппинге
        const category = getFieldCategory(field);
        const fieldId = category === "basic_info" ? `basic_info.${field}` :
                       category === "pre_court" ? `pre_court.${field}` :
                       category === "judicial_procedure_initiation" ? `judicial_procedure_initiation.${field}` :
                       `judicial_procedure.${field}`;
        missingFields.push({
          label: fieldLabels[field] || field,
          fieldName: field,
          tab: category === "basic_info" ? "primary" : category === "pre_court" ? "pre_court" : "judicial",
          fieldId,
        });
      }
    }
  });

  const filledCount = requiredFields.length - missingFields.length;
  const percentage = Math.round((filledCount / requiredFields.length) * 100);

  let status: DocumentStatus = "empty";
  if (percentage === 100) {
    status = "complete";
  } else if (percentage > 0) {
    status = "partial";
  }

  return {
    status,
    percentage,
    missingFields,
  };
};

