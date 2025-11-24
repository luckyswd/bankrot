import {
  ChildInfo,
  FormSections,
  FormValues,
  IntroductionFields,
  PretrialFields,
  PrimaryInfoFields,
  ProcedureFields,
} from "./types";

export const defaultPrimaryInfo: PrimaryInfoFields = {
  lastName: null,
  firstName: null,
  middleName: null,
  firstNameGenitive: null,
  middleNameGenitive: null,
  lastNameGenitive: null,
  isLastNameChanged: null,
  changedLastName: null,
  birthDate: null,
  birthPlace: null,
  snils: null,
  registrationRegion: null,
  registrationDistrict: null,
  registrationCity: null,
  registrationSettlement: null,
  registrationStreet: null,
  registrationHouse: null,
  registrationBuilding: null,
  registrationApartment: null,
  postalCode: null,
  passportSeries: null,
  passportNumber: null,
  passportIssuedBy: null,
  passportIssuedDate: null,
  passportDepartmentCode: null,
  maritalStatus: null,
  spouseFullName: null,
  spouseBirthDate: null,
  marriageTerminationDate: null,
  hasMinorChildren: null,
  children: [],
  isStudent: null,
  employerName: null,
  employerAddress: null,
  employerInn: null,
  socialBenefits: null,
  phone: null,
  email: null,
  mailingAddress: null,
  debtAmount: null,
  hasEnforcementProceedings: null,
  contractNumber: null,
  contractDate: null,
  work: false,
};

export const defaultPretrial: PretrialFields = {
  court: "",
  creditors: [],
  powerOfAttorneyNumber: "",
  powerOfAttorneyDate: "",
  efrsbCabinet: "",
  efrsbDateTime: "",
  hearingDateTime: "",
};

export const defaultIntroduction: IntroductionFields = {
  courtDecisionDate: "",
  gims: "",
  gostechnadzor: "",
  fns: "",
  documentNumber: "",
  caseNumber: "",
  rosaviation: "",
  caseNumber2: "",
  judge: "",
  bailiff: "",
  executionNumber: "",
  executionDate: "",
  specialAccountNumber: "",
};

export const defaultProcedure: ProcedureFields = {
  creditorRequirement: "",
  receivedRequirements: "",
  principalAmount: "",
};

export const createDefaultFormValues = (): FormValues => ({
  primaryInfo: { ...defaultPrimaryInfo },
  pretrial: { ...defaultPretrial },
  introduction: { ...defaultIntroduction },
  procedure: { ...defaultProcedure },
});

export const isRecord = (value: unknown): value is Record<string, unknown> =>
  typeof value === "object" && value !== null;

export const asPartial = <T extends object>(value: unknown): Partial<T> =>
  isRecord(value) ? (value as Partial<T>) : {};

const normalizeCreditors = (value: unknown): number[] => {
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (typeof item === "string" || typeof item === "number") {
          const num = Number(item);
          return Number.isNaN(num) ? null : num;
        }
        if (isRecord(item) && "creditorId" in item && item.creditorId !== undefined) {
          const num = Number(item.creditorId);
          return Number.isNaN(num) ? null : num;
        }
        if (isRecord(item) && "id" in item && item.id !== undefined) {
          const num = Number(item.id);
          return Number.isNaN(num) ? null : num;
        }
        return null;
      })
      .filter((creditorId): creditorId is number => typeof creditorId === "number");
  }

  if (typeof value === "string") {
    return value
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean)
      .map((creditorId) => Number(creditorId))
      .filter((num) => !Number.isNaN(num));
  }

  return [];
};

export const normalizeChild = (child: unknown): ChildInfo => {
  if (!isRecord(child)) {
    return {
      firstName: "",
      lastName: "",
      middleName: null,
      isLastNameChanged: false,
      changedLastName: null,
      birthDate: "",
    };
  }

  const asString = (value: unknown): string =>
    typeof value === "string" ? value : "";
  const asNullableString = (value: unknown): string | null =>
    typeof value === "string" && value.trim() !== "" ? value : null;

  return {
    firstName: asString(child.firstName),
    lastName: asString(child.lastName),
    middleName: asNullableString(child.middleName),
    isLastNameChanged:
      typeof child.isLastNameChanged === "boolean"
        ? child.isLastNameChanged
        : false,
    changedLastName: asNullableString(child.changedLastName),
    birthDate: asString(child.birthDate),
  };
};

export const normalizeChildren = (value: unknown): ChildInfo[] =>
  Array.isArray(value) ? value.map(normalizeChild) : [];

// Преобразование данных из API формата в формат формы
export const convertApiDataToFormValues = (
  apiData?: Record<string, unknown>
): FormValues => {
  const defaults = createDefaultFormValues();
  if (!apiData) {
    return defaults;
  }

  const basicInfo = asPartial<PrimaryInfoFields>(apiData.basic_info);

  // Преобразуем даты из формата API в строки
  const formatDate = (date: unknown): string | null => {
    if (!date) return null;
    if (typeof date === "string") return date;
    if (date instanceof Date) return date.toISOString().split("T")[0];
    return String(date);
  };

  const asBool = (value: unknown): boolean =>
    value === true || value === 1 || value === "1" || value === "true";

  const pickString = (value: unknown): string | null =>
    typeof value === "string" ? value : null;
  const basicInfoRecord = isRecord(apiData.basic_info)
    ? (apiData.basic_info as Record<string, unknown>)
    : {};

  return {
    ...defaults,
    primaryInfo: {
      ...defaults.primaryInfo,
      lastName: basicInfo.lastName ?? null,
      firstName: basicInfo.firstName ?? null,
      middleName: basicInfo.middleName ?? null,
      lastNameGenitive:
        basicInfo.lastNameGenitive ??
        pickString(basicInfoRecord["last_name_genitive"]) ??
        null,
      firstNameGenitive:
        basicInfo.firstNameGenitive ??
        pickString(basicInfoRecord["first_name_genitive"]) ??
        null,
      middleNameGenitive:
        basicInfo.middleNameGenitive ??
        pickString(basicInfoRecord["middle_name_genitive"]) ??
        null,
      isLastNameChanged: basicInfo.isLastNameChanged ?? null,
      changedLastName: basicInfo.changedLastName ?? null,
      birthDate: formatDate(basicInfo.birthDate),
      birthPlace: basicInfo.birthPlace ?? null,
      snils: basicInfo.snils ?? null,
      registrationRegion: basicInfo.registrationRegion ?? null,
      registrationDistrict: basicInfo.registrationDistrict ?? null,
      registrationCity: basicInfo.registrationCity ?? null,
      registrationSettlement: basicInfo.registrationSettlement ?? null,
      registrationStreet: basicInfo.registrationStreet ?? null,
      registrationHouse: basicInfo.registrationHouse ?? null,
      registrationBuilding: basicInfo.registrationBuilding ?? null,
      registrationApartment: basicInfo.registrationApartment ?? null,
      passportSeries: basicInfo.passportSeries ?? null,
      passportNumber: basicInfo.passportNumber ?? null,
      passportIssuedBy: basicInfo.passportIssuedBy ?? null,
      passportIssuedDate: formatDate(basicInfo.passportIssuedDate),
      passportDepartmentCode: basicInfo.passportDepartmentCode ?? null,
      maritalStatus: basicInfo.maritalStatus ?? null,
      spouseFullName: basicInfo.spouseFullName ?? null,
      spouseBirthDate: formatDate(basicInfo.spouseBirthDate),
      marriageTerminationDate: formatDate(
        basicInfo.marriageTerminationDate
      ),
      hasMinorChildren: basicInfo.hasMinorChildren ?? null,
      children: normalizeChildren(basicInfo.children),
      isStudent: basicInfo.isStudent ?? null,
      employerName: basicInfo.employerName ?? null,
      employerAddress: basicInfo.employerAddress ?? null,
      employerInn: basicInfo.employerInn ?? null,
      socialBenefits: basicInfo.socialBenefits ?? null,
      phone: basicInfo.phone ?? null,
      email: basicInfo.email ?? null,
      mailingAddress: basicInfo.mailingAddress ?? null,
      debtAmount: basicInfo.debtAmount ? String(basicInfo.debtAmount) : null,
      hasEnforcementProceedings: basicInfo.hasEnforcementProceedings ?? null,
      contractNumber: basicInfo.contractNumber ?? null,
      contractDate: formatDate(basicInfo.contractDate),
      work: asBool(basicInfo.work),
    },
    pretrial: {
      ...defaults.pretrial,
      ...asPartial<PretrialFields>(apiData.pre_court),
      creditors: normalizeCreditors(
        isRecord(apiData.pre_court) ? apiData.pre_court.creditors : undefined
      ),
    },
    introduction: {
      ...defaults.introduction,
      ...asPartial<IntroductionFields>(apiData.procedure_initiation),
    },
    procedure: {
      ...defaults.procedure,
      ...asPartial<ProcedureFields>(apiData.procedure),
    },
  };
};

export const buildFormValues = (
  contract?: Record<string, unknown>
): FormValues => {
  const defaults = createDefaultFormValues();
  if (!contract) {
    return defaults;
  }

  // Если данные пришли из API (с basic_info, pre_court и т.д.)
  if (
    contract.basic_info ||
    contract.pre_court ||
    contract.procedure_initiation ||
    contract.procedure
  ) {
    return convertApiDataToFormValues(contract);
  }

  // Если данные в старом формате (primaryInfo, pretrial и т.д.)
  const overrides = contract as Partial<FormSections>;

  const primaryInfoOverrides: Partial<PrimaryInfoFields> = overrides.primaryInfo
    ? { ...overrides.primaryInfo }
    : {};
  const mergedPrimaryInfo = {
    ...defaults.primaryInfo,
    ...primaryInfoOverrides,
  };

  return {
    ...defaults,
    ...contract,
    primaryInfo: {
      ...mergedPrimaryInfo,
      children: normalizeChildren(
        primaryInfoOverrides.children ?? mergedPrimaryInfo.children
      ),
    },
    pretrial: {
      ...defaults.pretrial,
      ...(overrides.pretrial ?? {}),
      creditors: normalizeCreditors(
        overrides.pretrial
          ? (overrides.pretrial as Record<string, unknown>).creditors
          : undefined
      ),
    },
    introduction: {
      ...defaults.introduction,
      ...(overrides.introduction ?? {}),
    },
    procedure: {
      ...defaults.procedure,
      ...(overrides.procedure ?? {}),
    },
  };
};
