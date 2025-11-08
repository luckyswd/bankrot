import * as React from "react"
import { cn } from "@/lib/utils"
import { X } from "lucide-react"

export interface ToastProps {
  message: string
  type?: 'error' | 'success' | 'info'
  onClose: () => void
  duration?: number
}

export const Toast: React.FC<ToastProps> = ({ message, type = 'error', onClose, duration = 5000 }) => {
  React.useEffect(() => {
    if (duration > 0) {
      const timer = setTimeout(() => {
        onClose()
      }, duration)

      return () => clearTimeout(timer)
    }
  }, [duration, onClose])

  const bgColor = {
    error: 'bg-red-500',
    success: 'bg-green-500',
    info: 'bg-blue-500',
  }[type]

  return (
    <div
      className={cn(
        'fixed top-4 right-4 z-50 flex items-center gap-3 rounded-lg px-4 py-3 text-white shadow-lg min-w-[300px] max-w-[500px] animate-in slide-in-from-top-5',
        bgColor
      )}
    >
      <span className="flex-1">{message}</span>
      <button
        onClick={onClose}
        className="hover:opacity-80 transition-opacity"
        aria-label="Закрыть"
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  )
}

