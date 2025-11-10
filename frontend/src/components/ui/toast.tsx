import { toast as sonnerToast } from "sonner"

import { Toaster as UiToaster } from "@/components/ui/sonner"

export type ToastType = "error" | "success" | "info"

type NotifyOptions = {
  message: string
  type?: ToastType
  duration?: number
}

export const notify = ({ message, type = "error", duration = 5000 }: NotifyOptions) => {
  const toastFn =
    type === "success" ? sonnerToast.success : type === "info" ? sonnerToast : sonnerToast.error

  return toastFn(message, {
    duration,
  })
}

export const ToastViewport = () => (
  <UiToaster position="top-right" richColors closeButton toastOptions={{ duration: 5000 }} />
)
