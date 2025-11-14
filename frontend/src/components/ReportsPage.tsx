import { useState } from 'react'
import { useApp, type Report } from '../context/AppContext'
import { Button } from './ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import { Upload, Download, FileText, Trash2, Eye } from 'lucide-react'

export default function ReportsPage() {
  const { reports = [], addReport, deleteReport } = useApp()
  const [uploading, setUploading] = useState(false)

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>): void => {
    const file = e.target.files?.[0]
    if (!file) return

    setUploading(true)
    
    // Симулируем загрузку файла
    const reader = new FileReader()
    reader.onload = (event: ProgressEvent<FileReader>) => {
      const result = event.target?.result
      if (!result) return
      
      const newReport = {
        id: Date.now(),
        name: file.name,
        size: file.size,
        type: file.type,
        uploadedAt: new Date().toISOString(),
        uploadedBy: 'Текущий пользователь',
        // В реальности здесь был бы URL или данные файла
        data: result as string | ArrayBuffer
      }
      
      addReport(newReport)
      setUploading(false)
      e.target.value = '' // Сброс input
    }
    reader.readAsDataURL(file)
  }

  const handleDownload = (report: Report) => {
    // Создаем ссылку для скачивания
    if (typeof report.data !== 'string') return
    const link = document.createElement('a')
    link.href = report.data
    link.download = report.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  const handleView = (report: Report) => {
    // Открываем в новой вкладке
    if (typeof report.data !== 'string') return
    window.open(report.data, '_blank')
  }

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
  }

  const getFileIcon = (type: string): string => {
    if (type.includes('pdf')) return '📄'
    if (type.includes('word') || type.includes('document')) return '📝'
    if (type.includes('excel') || type.includes('spreadsheet')) return '📊'
    if (type.includes('image')) return '🖼️'
    return '📁'
  }

  return (
    <div className="space-y-6">
      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card className="p-6">
          <div className="text-2xl font-bold">{reports.length}</div>
          <div className="text-sm text-muted-foreground">Всего отчётов</div>
        </Card>
        <Card className="p-6">
          <div className="text-2xl font-bold">
            {formatFileSize(reports.reduce((sum, r) => sum + r.size, 0))}
          </div>
          <div className="text-sm text-muted-foreground">Общий размер</div>
        </Card>
        <Card className="p-6">
          <div className="text-2xl font-bold">
            {reports.filter(r => {
              const date = new Date(r.uploadedAt)
              const now = new Date()
              return date.getMonth() === now.getMonth()
            }).length}
          </div>
          <div className="text-sm text-muted-foreground">За этот месяц</div>
        </Card>
      </div>

      {/* Upload Card */}
      <Card>
        <CardHeader>
          <CardTitle>Загрузить новый отчёт</CardTitle>
          <CardDescription>
            Поддерживаются форматы: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4">
            <input
              type="file"
              id="file-upload"
              className="hidden"
              onChange={handleFileUpload}
              accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
              disabled={uploading}
            />
            <label htmlFor="file-upload">
              <Button asChild disabled={uploading}>
                <span>
                  <Upload className="h-4 w-4 mr-2" />
                  {uploading ? 'Загрузка...' : 'Выбрать файл'}
                </span>
              </Button>
            </label>
            <p className="text-sm text-muted-foreground">
              Максимальный размер файла: 50 МБ
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Reports Table */}
      <Card>
        <CardHeader>
          <CardTitle>Список отчётов</CardTitle>
          <CardDescription>
            Все загруженные отчёты и документы
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-12">Тип</TableHead>
                <TableHead>Название</TableHead>
                <TableHead>Размер</TableHead>
                <TableHead>Загружен</TableHead>
                <TableHead>Кто загрузил</TableHead>
                <TableHead className="w-32">Действия</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {reports.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
                    Нет отчётов. Загрузите первый файл!
                  </TableCell>
                </TableRow>
              ) : (
                reports.map((report) => (
                  <TableRow key={report.id}>
                    <TableCell>
                      <span className="text-2xl">{getFileIcon(report.type)}</span>
                    </TableCell>
                    <TableCell className="font-medium">
                      <div className="flex items-center gap-2">
                        <FileText className="h-4 w-4 text-muted-foreground" />
                        {report.name}
                      </div>
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {formatFileSize(report.size)}
                    </TableCell>
                    <TableCell className="text-sm">
                      {new Date(report.uploadedAt).toLocaleString('ru-RU')}
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {report.uploadedBy}
                    </TableCell>
                    <TableCell>
                      <div className="flex gap-2">
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => handleView(report)}
                          title="Просмотр"
                        >
                          <Eye className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => handleDownload(report)}
                          title="Скачать"
                        >
                          <Download className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => {
                            if (window.confirm('Удалить этот отчёт?')) {
                              deleteReport(report.id)
                            }
                          }}
                          title="Удалить"
                        >
                          <Trash2 className="h-4 w-4 text-destructive" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  )
}

