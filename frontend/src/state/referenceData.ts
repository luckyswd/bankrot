import { atom } from "recoil"

import type { ReferenceData } from "@/types/reference"

export const referenceDataAtom = atom<ReferenceData>({
  key: "referenceData",
  default: {},
})
