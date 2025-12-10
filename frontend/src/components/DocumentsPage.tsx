import { useState, useEffect, useCallback } from 'react'
import { apiRequest } from '../config/api'
import { Button } from './ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import { Badge } from './ui/badge'
import { Input } from './ui/input'
import { Label } from './ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select'
import { notify } from './ui/toast'
import { Upload, Download, FileText, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import Loading from './shared/Loading'
import { useModalStore } from './Modals/ModalProvider'

const CATEGORIES = [
  { value: 'basic_info', label: 'Основная информация' },
  { value: 'pre_court', label: 'Досудебка' },
  { value: 'judicial_procedure_initiation', label: 'Судебка введение процедуры' },
  { value: 'judicial_procedure', label: 'Судебка процедура' },
  { value: 'judicial_report', label: 'Судебка отчет' },
]

interface Template {
  id: number
  name: string
  category: string
  [key: string]: unknown
}

export default function DocumentsPage() {
  const [templates, setTemplates] = useState<Template[]>([])
  const [loading, setLoading] = useState(true)
  const [uploading, setUploading] = useState(false)
  const [selectedFile, setSelectedFile] = useState<File | null>(null)
  const [templateName, setTemplateName] = useState('')
  const [templateCategory, setTemplateCategory] = useState('')
  const { openModal } = useModalStore()
  
  // Поиск и фильтры
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [categoryFilter, setCategoryFilter] = useState('')
  const [page, setPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [pages, setPages] = useState(1)
  const limit = 10

  useEffect(() => {
    const timer = setTimeout(() => {
      if (search.length >= 3 || search.length === 0) {
        setDebouncedSearch(search)
      }
    }, 200)

    return () => clearTimeout(timer)
  }, [search])

  const fetchTemplates = useCallback(async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })

      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }

      if (categoryFilter && categoryFilter !== '') {
        params.append('category', categoryFilter)
      }

      const data = await apiRequest(`/document-templates?${params.toString()}`)

      if (data && typeof data === 'object' && Array.isArray(data.items)) {
        setTemplates(data.items)
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      } else if (Array.isArray(data)) {
        setTemplates(data)
        setTotal(data.length)
        setPages(1)
      } else {
        setTemplates([])
        setTotal(0)
        setPages(1)
      }
    } catch (error) {
      console.error('Ошибка при загрузке шаблонов:', error)
      notify({ message: 'Не удалось загрузить список шаблонов', type: 'error' })
      setTemplates([])
      setTotal(0)
      setPages(1)
    } finally {
      setLoading(false)
    }
  }, [page, debouncedSearch, categoryFilter, limit])

  // Загрузка данных при изменении страницы, поиска или фильтра
  useEffect(() => {
    fetchTemplates()
  }, [fetchTemplates])

  // Сброс на первую страницу при изменении поиска или фильтра
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch, categoryFilter])

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return

    if (!file.name.endsWith('.docx')) {
      notify({ message: 'Поддерживаются только DOCX файлы', type: 'error' })
      e.target.value = ''
      return
    }

    setSelectedFile(file)
    // Автоматически устанавливаем название из имени файла
    const fileNameWithoutExtension = file.name.replace(/\.docx$/i, '')
    setTemplateName(fileNameWithoutExtension)
  }

  const handleFileUpload = async () => {
    if (!selectedFile) {
      notify({ message: 'Выберите файл', type: 'error' })
      return
    }

    if (!templateCategory) {
      notify({ message: 'Выберите категорию', type: 'error' })
      return
    }

    try {
      setUploading(true)

      const formData = new FormData()
      formData.append('file', selectedFile)
      formData.append('name', templateName.trim())
      formData.append('category', templateCategory)

      await apiRequest('/document-templates', {
        method: 'POST',
        body: formData,
        headers: {},
      })

      setSelectedFile(null)
      setTemplateName('')
      setTemplateCategory('')
      const fileInput = document.getElementById('file-upload') as HTMLInputElement
      if (fileInput) {
        fileInput.value = ''
      }
      notify({ message: 'Шаблон успешно загружен', type: 'success' })
      
      // Перезагружаем список шаблонов
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      if (categoryFilter && categoryFilter !== '') {
        params.append('category', categoryFilter)
      }
      const data = await apiRequest(`/document-templates?${params.toString()}`)
      if (data && typeof data === 'object') {
        setTemplates(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error: unknown) {
      console.error('Ошибка при загрузке шаблона:', error)
      const errorMessage = (error as { body?: { error?: string } })?.body?.error || 'Не удалось загрузить шаблон'
      notify({ message: errorMessage, type: 'error' })
    } finally {
      setUploading(false)
    }
  }

  const handleDownload = async (template: Template) => {
    try {
      const response = await fetch(
        `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}document-templates/${template.id}`,
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
      // Для шаблона с ID=200 используем расширение .xlsx
      link.download = `${template.name}.${template.id === 200 ? 'xlsx' : 'docx'}`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error('Ошибка при скачивании шаблона:', error)
      notify({ message: 'Не удалось скачать шаблон', type: 'error' })
    }
  }

  const handleDeleteClick = (template: Template) => {
    openModal('confirm', {
      title: 'Удаление шаблона',
      description: `Вы уверены, что хотите удалить шаблон "${template.name}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/document-templates/${template.id}`, {
          method: 'DELETE',
        })
        notify({ message: 'Шаблон успешно удален', type: 'success' })
        await fetchTemplates()
      },
    })
  }

  const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
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
    <div className="space-y-6 p-6">
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
                    placeholder="Введите название"
                    disabled={true}
                    className="bg-muted"
                  />
                  <p className="text-xs text-muted-foreground">
                    Название берётся из названия файла
                  </p>
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
          <CardTitle>Список шаблонов ({total})</CardTitle>
          <CardDescription>
            Все доступные шаблоны для генерации документов
          </CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск и фильтры */}
          <div className="mb-4 space-y-4">
            <div className="flex gap-4 items-center">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Поиск по названию шаблона"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              <div className="w-64">
                <Select value={categoryFilter || 'all'} onValueChange={(value) => setCategoryFilter(value === 'all' ? '' : value)}>
                  <SelectTrigger className="h-10">
                    <SelectValue placeholder="Все категории" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Все категории</SelectItem>
                    {CATEGORIES.map((cat) => (
                      <SelectItem key={cat.value} value={cat.value}>
                        {cat.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>

          {loading ? (
            <div className="py-8">
              <Loading text="Загрузка документов..." />
            </div>
          ) : (
            <>
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
                        {(debouncedSearch && debouncedSearch.length >= 3) || (categoryFilter && categoryFilter !== '')
                          ? 'Шаблоны не найдены'
                          : 'Нет шаблонов. Загрузите первый шаблон!'}
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
                          <div className="flex">
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
                              className="text-red-400"
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>

              {/* Пагинация */}
              {pages > 1 && (
                <div className="flex items-center justify-between mt-4 p-4 border-t">
                  <div className="text-sm text-muted-foreground">
                    Показано {(page - 1) * limit + 1} - {Math.min(page * limit, total)} из {total}
                  </div>
                  <div className="flex gap-2 items-center">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setPage(page - 1)}
                      disabled={page === 1}
                    >
                      <ChevronLeft className="h-4 w-4" />
                      Назад
                    </Button>
                    <div className="flex items-center gap-1">
                      {(() => {
                        const pageNumbers: (number | string)[] = []
                        
                        if (pages <= 7) {
                          // Если страниц 7 или меньше, показываем все
                          for (let i = 1; i <= pages; i++) {
                            pageNumbers.push(i)
                          }
                        } else {
                          // Всегда показываем первую страницу
                          pageNumbers.push(1)
                          
                          if (page > 3) {
                            pageNumbers.push('...')
                          }
                          
                          // Показываем страницы вокруг текущей
                          const start = Math.max(2, page - 1)
                          const end = Math.min(pages - 1, page + 1)
                          
                          for (let i = start; i <= end; i++) {
                            if (i !== 1 && i !== pages) {
                              pageNumbers.push(i)
                            }
                          }
                          
                          if (page < pages - 2) {
                            pageNumbers.push('...')
                          }
                          
                          // Всегда показываем последнюю страницу
                          if (pages > 1) {
                            pageNumbers.push(pages)
                          }
                        }
                        
                        return pageNumbers.map((pageNum, idx) => {
                          if (pageNum === '...') {
                            return (
                              <span key={`ellipsis-${idx}`} className="px-2 text-muted-foreground">
                                ...
                              </span>
                            )
                          }
                          return (
                            <Button
                              key={pageNum}
                              variant={page === pageNum ? 'default' : 'outline'}
                              size="sm"
                              onClick={() => setPage(pageNum as number)}
                              className="w-10"
                            >
                              {pageNum}
                            </Button>
                          )
                        })
                      })()}
                    </div>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setPage(page + 1)}
                      disabled={page === pages}
                    >
                      Вперёд
                      <ChevronRight className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              )}
            </>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
