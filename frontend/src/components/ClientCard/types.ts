export type ChildInfo = {
  firstName: string
  lastName: string
  middleName?: string | null
  isLastNameChanged: boolean
  changedLastName?: string | null
  birthDate: string
}

export type PrimaryInfoFields = {
  lastName?: string | null
  firstName?: string | null
  middleName?: string | null
  firstNameGenitive?: string | null
  middleNameGenitive?: string | null
  lastNameGenitive?: string | null
  isLastNameChanged?: boolean | null
  changedLastName?: string | null
  birthDate?: string | null
  birthPlace?: string | null
  snils?: string | null
  gender?: string | null
  registrationRegion?: string | null
  registrationDistrict?: string | null
  registrationCity?: string | null
  registrationSettlement?: string | null
  registrationStreet?: string | null
  registrationHouse?: string | null
  registrationBuilding?: string | null
  registrationApartment?: string | null
  postalCode?: string | null
  passportSeries?: string | null
  passportNumber?: string | null
  passportIssuedBy?: string | null
  passportIssuedDate?: string | null
  passportDepartmentCode?: string | null
  maritalStatus?: string | null
  spouseFullName?: string | null
  spouseBirthDate?: string | null
  marriageTerminationDate?: string | null
  hasMinorChildren?: boolean | null
  children: ChildInfo[]
  isStudent?: boolean | null
  employerName?: string | null
  employerAddress?: string | null
  employerInn?: string | null
  socialBenefits?: string | null
  phone?: string | null
  email?: string | null
  mailingAddress?: string | null
  debtAmount?: string | null
  hasEnforcementProceedings?: boolean | null
  contractNumber?: string | null
  contractDate?: string | null
  work: boolean
}

export type PretrialFields = {
  court: string
  creditors: number[]
  powerOfAttorneyNumber: string
  caseNumber: string
  powerOfAttorneyDate: string
  efrsbCabinet?: string
  efrsbDateTime: string
  hearingDateTime: string
}

export type IntroductionFields = {
  procedureInitiationDecisionDate: string
  procedureInitiationResolutionDate: string
  procedureInitiationMchs: string
  procedureInitiationGostekhnadzor: string
  procedureInitiationFns: string
  procedureInitiationDocNumber: string
  procedureInitiationCaseNumber: string
  procedureInitiationDuration: string
  procedureInitiationRoszdrav: string
  procedureInitiationJudge: string
  procedureInitiationBailiff: string
  executionNumber: string
  executionDate: string
  procedureInitiationSpecialAccountNumber: string
}

export type BasisItem = {
  number: string
  date: string
}

export type CreditorsClaimItem = {
  id?: number | null
  creditorId: number
  debtAmount?: string | null
  principalAmount?: string | null
  interest?: string | null
  penalty?: string | null
  lateFee?: string | null
  forfeiture?: string | null
  stateDuty?: string | null
  basis?: BasisItem[] | null
  inclusion?: boolean | null
}

export type ProcedureFields = {
  creditorsClaims?: CreditorsClaimItem[]
}

export type FormSections = {
  basic_info: PrimaryInfoFields
  pre_court: PretrialFields
  judicial_procedure_initiation: IntroductionFields
  judicial_procedure: ProcedureFields
}

export type FormValues = FormSections
