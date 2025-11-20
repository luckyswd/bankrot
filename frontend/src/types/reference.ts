export interface ReferenceItem {
  id: number | string
  name: string
  [key: string]: unknown
}

export interface ReferenceData {
  courts?: ReferenceItem[]
  creditors?: ReferenceItem[]
  fns?: ReferenceItem[]
  bailiffs?: ReferenceItem[]
  mchs?: ReferenceItem[]
  rosgvardia?: ReferenceItem[]
  gostekhnadzor?: ReferenceItem[]
  [key: string]: ReferenceItem[] | undefined
}
