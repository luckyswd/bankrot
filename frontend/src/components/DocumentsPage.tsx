import { useState, useEffect } from 'react'
import { apiRequest } from '../config/api'
import { Button } from './ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import { Badge } from './ui/badge'
import { Input } from './ui/input'
import { Label } from './ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select'
import { Toast } from './ui/toast'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from './ui/dialog'
import { Upload, Download, FileText, Trash2 } from 'lucide-react'

const CATEGORIES = [
  { value: 'basic_info', label: 'Основная информация' },
  { value: 'pre_court', label: 'Досудебка' },
  { value: 'judicial', label: 'Судебка' },
  { value: 'realization', label: 'Реализация' },
  { value: 'procedure_initiation', label: 'Введение процедуры' },
  { value: 'procedure', label: 'Процедура' },
  { value: 'report', label: 'Отчет' },
]

export default function DocumentsPage() {
  const [templates, setTemplates] = useState([])
  const [loading, setLoading] = useState(true)
  const [uploading, setUploading] = useState(false)
  const [selectedFile, setSelectedFile] = useState(null)
  const [templateName, setTemplateName] = useState('')
  const [templateCategory, setTemplateCategory] = useState('')
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [templateToDelete, setTemplateToDelete] = useState(null)

  useEffect(() => {
    loadTemplates()
  }, [])

  const loadTemplates = async () => {
    try {
      setLoading(true)
      const data = await apiRequest('/api/v1/document-templates')
      setTemplates(data)
    } catch (error) {
      console.error('Ошибка при загрузке шаблонов:', error)
      setToast({ message: 'Не удалось загрузить список шаблонов', type: 'error' })
    } finally {
      setLoading(false)
    }
  }

  const handleFileSelect = (e) => {
    const file = e.target.files[0]
    if (!file) return

    if (!file.name.endsWith('.docx')) {
      setToast({ message: 'Поддерживаются только DOCX файлы', type: 'error' })
      e.target.value = ''
      return
    }

    setSelectedFile(file)
    if (!templateName) {
      setTemplateName(file.name.replace('.docx', ''))
    }
  }

  const handleFileUpload = async () => {
    if (!selectedFile) {
      setToast({ message: 'Выберите файл', type: 'error' })
      return
    }

    if (!templateName.trim()) {
      setToast({ message: 'Введите название шаблона', type: 'error' })
      return
    }

    if (!templateCategory) {
      setToast({ message: 'Выберите категорию', type: 'error' })
      return
    }

    try {
      setUploading(true)

      const formData = new FormData()
      formData.append('file', selectedFile)
      formData.append('name', templateName.trim())
      formData.append('category', templateCategory)

      await apiRequest('/api/v1/document-templates', {
        method: 'POST',
        body: formData,
        headers: {},
      })

      setSelectedFile(null)
      setTemplateName('')
      setTemplateCategory('')
      document.getElementById('file-upload').value = ''
      setToast({ message: 'Шаблон успешно загружен', type: 'success' })
      await loadTemplates()
    } catch (error) {
      console.error('Ошибка при загрузке шаблона:', error)
      const errorMessage = error.body?.error || 'Не удалось загрузить шаблон'
      setToast({ message: errorMessage, type: 'error' })
    } finally {
      setUploading(false)
    }
  }

  const handleDownload = async (template) => {
    try {
      const response = await fetch(
        `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/api/v1/document-templates/${template.id}`,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      )

      if (!response.ok) {
        throw new Error('Ошибка при скачивании файла')
      }

      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${template.name}.docx`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error('Ошибка при скачивании шаблона:', error)
      setToast({ message: 'Не удалось скачать шаблон', type: 'error' })
    }
  }

  const handleDeleteClick = (template) => {
    setTemplateToDelete(template)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!templateToDelete) return

    try {
      await apiRequest(`/api/v1/document-templates/${templateToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Шаблон успешно удален', type: 'success' })
      setDeleteDialogOpen(false)
      setTemplateToDelete(null)
      await loadTemplates()
    } catch (error) {
      console.error('Ошибка при удалении шаблона:', error)
      setToast({ message: 'Не удалось удалить шаблон', type: 'error' })
      setDeleteDialogOpen(false)
      setTemplateToDelete(null)
    }
  }

  const getCategoryColor = (category) => {
    const colors = {
      'Основная информация': 'bg-gray-500/10 text-gray-600 dark:text-gray-400',
      'Досудебка': 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
      'Судебка': 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
      'Реализация': 'bg-red-500/10 text-red-600 dark:text-red-400',
      'Введение процедуры': 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
      'Процедура': 'bg-green-500/10 text-green-600 dark:text-green-400',
      'Отчет': 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    }
    return colors[category] || colors['Основная информация']
  }

  return (
    <div className="space-y-6">
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          onClose={() => setToast(null)}
        />
      )}

      <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Удаление шаблона</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить шаблон "{templateToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setTemplateToDelete(null)
              }}
            >
              Отмена
            </Button>
            <Button
              variant="destructive"
              onClick={handleDeleteConfirm}
            >
              Удалить
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      {/* Upload Card */}
      <Card>
        <CardHeader>
          <CardTitle>Загрузить новый шаблон</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-center gap-4">
              <input
                type="file"
                id="file-upload"
                className="hidden"
                onChange={handleFileSelect}
                accept=".docx"
                disabled={uploading}
              />
              <label htmlFor="file-upload">
                <Button asChild disabled={uploading} variant="outline">
                  <span>
                    <Upload className="h-4 w-4 mr-2" />
                    {selectedFile ? selectedFile.name : 'Выбрать шаблон'}
                  </span>
                </Button>
              </label>
              <p className="text-sm text-muted-foreground">
                Поддерживаются только .docx файлы
              </p>
            </div>

            {selectedFile && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="template-name">Название шаблона</Label>
                  <Input
                    id="template-name"
                    value={templateName}
                    onChange={(e) => setTemplateName(e.target.value)}
                    placeholder="Введите название"
                    disabled={uploading}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="template-category">Категория</Label>
                  <Select value={templateCategory} onValueChange={setTemplateCategory} disabled={uploading}>
                    <SelectTrigger id="template-category">
                      <SelectValue placeholder="Выберите категорию" />
                    </SelectTrigger>
                    <SelectContent>
                      {CATEGORIES.map((cat) => (
                        <SelectItem key={cat.value} value={cat.value}>
                          {cat.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
            )}

            {selectedFile && (
              <Button onClick={handleFileUpload} disabled={uploading}>
                {uploading ? 'Загрузка...' : 'Загрузить шаблон'}
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Templates Table */}
      <Card>
        <CardHeader>
          <CardTitle>Список шаблонов ({templates.length})</CardTitle>
          <CardDescription>
            Все доступные шаблоны для генерации документов
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="text-center py-12 text-muted-foreground">
              Загрузка...
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Название шаблона</TableHead>
                  <TableHead>Категория</TableHead>
                  <TableHead className="w-28">Действия</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {templates.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={3} className="text-center py-12 text-muted-foreground">
                      Нет шаблонов. Загрузите первый шаблон!
                    </TableCell>
                  </TableRow>
                ) : (
                  templates.map((template) => (
                    <TableRow key={template.id}>
                      <TableCell className="font-medium">
                        <div className="flex items-center gap-2">
                          <FileText className="h-4 w-4 text-blue-500" />
                          {template.name}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge className={getCategoryColor(template.category) + ' pointer-events-none'}>
                          {template.category}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex gap-1">
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => handleDownload(template)}
                            title="Скачать шаблон"
                          >
                            <Download className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => handleDeleteClick(template)}
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
          )}
        </CardContent>
      </Card>
    </div>
  )
}
