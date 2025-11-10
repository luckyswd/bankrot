import { createContext, useCallback, useContext, useMemo, useState, type ComponentType, type ReactNode } from "react"

import { ConfirmModal } from "./ConfirmModal"
import { CreateContractModal } from "./CreateContractModal"
import { CreditorFormModal } from "./CreditorFormModal"
import { BailiffFormModal } from "./BailiffFormModal"
import { CourtFormModal } from "./CourtFormModal"
import { FnsBranchModal } from "./FnsBranchModal"
import { MchsBranchModal } from "./MchsBranchModal"
import { RosgvardiaBranchModal } from "./RosgvardiaBranchModal"

export type ModalKey =
  | "confirm"
  | "createContract"
  | "creditorForm"
  | "bailiffForm"
  | "courtForm"
  | "fnsForm"
  | "mchsForm"
  | "rosgvardiaForm"

type ModalState = {
  isOpen: boolean
  props?: Record<string, unknown>
}

type ModalStoreState = Partial<Record<ModalKey, ModalState>>

type ModalContextValue = {
  openModal: (key: ModalKey, props?: Record<string, unknown>) => void
  closeModal: (key: ModalKey) => void
  state: ModalStoreState
}

const ModalContext = createContext<ModalContextValue | undefined>(undefined)

type ModalComponentProps = {
  isOpen: boolean
  onClose: () => void
} & Record<string, unknown>

type ModalComponentMap = Record<ModalKey, ComponentType<ModalComponentProps>>

const modalComponents: ModalComponentMap = {
  confirm: ConfirmModal,
  createContract: CreateContractModal,
  creditorForm: CreditorFormModal,
  bailiffForm: BailiffFormModal,
  courtForm: CourtFormModal,
  fnsForm: FnsBranchModal,
  mchsForm: MchsBranchModal,
  rosgvardiaForm: RosgvardiaBranchModal,
}

interface ModalRendererProps {
  state: ModalStoreState
  closeModal: (key: ModalKey) => void
}

const ModalRenderer = ({ state, closeModal }: ModalRendererProps) => (
  <>
    {Object.entries(modalComponents).map(([key, Component]) => {
      const typedKey = key as ModalKey
      const modalState = state[typedKey]
      if (!modalState?.isOpen) return null

      return (
        <Component
          key={typedKey}
          isOpen={modalState.isOpen}
          onClose={() => closeModal(typedKey)}
          {...(modalState.props ?? {})}
        />
      )
    })}
  </>
)

interface ModalProviderProps {
  children: ReactNode
}

export const ModalProvider = ({ children }: ModalProviderProps) => {
  const [state, setState] = useState<ModalStoreState>({})

  const openModal = useCallback((key: ModalKey, props?: Record<string, unknown>) => {
    setState((prev) => ({
      ...prev,
      [key]: {
        isOpen: true,
        props,
      },
    }))
  }, [])

  const closeModal = useCallback((key: ModalKey) => {
    setState((prev) => ({
      ...prev,
      [key]: {
        ...prev[key],
        isOpen: false,
      },
    }))
  }, [])

  const value = useMemo<ModalContextValue>(
    () => ({
      openModal,
      closeModal,
      state,
    }),
    [openModal, closeModal, state],
  )

  return (
    <ModalContext.Provider value={value}>
      {children}
      <ModalRenderer state={state} closeModal={closeModal} />
    </ModalContext.Provider>
  )
}

export const useModalStore = () => {
  const context = useContext(ModalContext)
  if (!context) {
    throw new Error("useModalStore must be used within ModalProvider")
  }
  return context
}
