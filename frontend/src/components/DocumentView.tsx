import { useParams, useNavigate } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { documentTemplates } from '../data/mockData'
import { Button } from './ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { ArrowLeft, Download, X } from 'lucide-react'

function DocumentView() {
  const { clientId, docType } = useParams()
  const navigate = useNavigate()
  const { contracts } = useApp()
  
  const contract = contracts.find(c => c.id === parseInt(clientId))
  const template = documentTemplates[docType]

  if (!contract || !template) {
    return (
      <div className="flex items-center justify-center h-full">
        <Card className="p-8 text-center">
          <p className="text-lg mb-4">Документ не найден</p>
          <Button onClick={() => navigate(-1)}>
            <ArrowLeft className="h-4 w-4 mr-2" />
            Вернуться назад
          </Button>
        </Card>
      </div>
    )
  }

  const documentContent = template.template(contract)

  const handleDownload = () => {
    const blob = new Blob([documentContent], { type: 'text/plain;charset=utf-8' })
    const url = URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.download = `${template.name} - ${contract.contractNumber}.txt`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  }

  const handleClose = () => {
    navigate(-1)
  }

  return (
    <div className="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm flex items-center justify-center p-4">
      <Card className="w-full max-w-4xl max-h-[90vh] flex flex-col">
        {/* Header */}
        <CardHeader className="border-b">
          <div className="flex items-start justify-between">
            <div>
              <CardTitle className="text-2xl">{template.name}</CardTitle>
              <CardDescription>Договор № {contract.contractNumber}</CardDescription>
            </div>
            <div className="flex gap-2">
              <Button onClick={handleDownload} size="sm">
                <Download className="h-4 w-4 mr-2" />
                Скачать
              </Button>
              <Button onClick={handleClose} variant="outline" size="sm">
                <X className="h-4 w-4 mr-2" />
                Закрыть
              </Button>
            </div>
          </div>
        </CardHeader>

        {/* Content */}
        <CardContent className="flex-1 overflow-y-auto p-6">
          <div className="bg-accent/20 rounded-lg p-6">
            <pre className="text-sm font-mono whitespace-pre-wrap">
              {documentContent}
            </pre>
          </div>
        </CardContent>

        {/* Footer */}
        <div className="border-t p-4 bg-muted/50">
          <p className="text-sm text-muted-foreground mb-3">
            💡 Документ автоматически заполнен данными из карточки клиента. 
            Пустые поля отмечены как [Название поля] - их нужно заполнить в карточке клиента.
          </p>
          <div className="flex justify-between">
            <Button onClick={handleClose} variant="outline">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Вернуться к карточке
            </Button>
            <Button onClick={handleDownload}>
              <Download className="h-4 w-4 mr-2" />
              Скачать документ
            </Button>
          </div>
        </div>
      </Card>
    </div>
  )
}

export default DocumentView
