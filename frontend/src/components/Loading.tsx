import { Loader2 } from 'lucide-react'

interface LoadingProps {
  text?: string
  size?: 'sm' | 'md' | 'lg'
  fullScreen?: boolean
}

export default function Loading({ text = 'Загрузка...', size = 'md', fullScreen = false }: LoadingProps) {
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8',
    lg: 'h-12 w-12',
  }

  const textSizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
  }

  const content = (
    <div className="flex flex-col items-center justify-center gap-3">
      <Loader2 className={`${sizeClasses[size]} animate-spin text-primary`} />
      {text && <p className={`${textSizeClasses[size]} text-muted-foreground`}>{text}</p>}
    </div>
  )

  if (fullScreen) {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
        {content}
      </div>
    )
  }

  return (
    <div className="flex min-h-[200px] items-center justify-center py-8">
      {content}
    </div>
  )
}

