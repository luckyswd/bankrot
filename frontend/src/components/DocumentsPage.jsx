import { useState } from 'react'
import { useApp } from '../context/AppContext'
import { Button } from './ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import { Badge } from './ui/badge'
import { Upload, Download, FileText, Trash2, Edit } from 'lucide-react'

// Моковые ШАБЛОНЫ документов
const mockTemplates = [
  {
    id: 1,
    name: 'Шаблон - Заявление о признании банкротом.docx',
    description: 'Основное заявление в суд',
    size: 28400,
    category: 'Досудебка',
    variables: ['{ФИО}', '{ДАТА_РОЖДЕНИЯ}', '{АДРЕС}', '{ИНН}', '{СУММА_ДОЛГА}'],
    uploadedAt: '2024-10-20T09:00:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 2,
    name: 'Шаблон - Публикация ЕФРСБ.docx',
    description: 'Публикация в ЕФРСБ',
    size: 22100,
    category: 'Введение процедуры',
    variables: ['{ФИО}', '{НОМЕР_ДЕЛА}', '{ДАТА_РЕШЕНИЯ}', '{СУД}', '{СУДЬЯ}'],
    uploadedAt: '2024-10-20T09:15:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 3,
    name: 'Шаблон - Уведомление супруга о РИ.docx',
    description: 'Уведомление супругу о введении реализации имущества',
    size: 19800,
    category: 'Введение процедуры',
    variables: ['{ФИО_СУПРУГА}', '{ФИО_ДОЛЖНИКА}', '{ДАТА_РЕШЕНИЯ}', '{НОМЕР_ДЕЛА}'],
    uploadedAt: '2024-10-20T09:30:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 4,
    name: 'Шаблон - Запрос в ГИБДД.docx',
    description: 'Запрос информации о транспортных средствах',
    size: 18200,
    category: 'Введение процедуры',
    variables: ['{ФИО}', '{ПАСПОРТ}', '{ДАТА_РОЖДЕНИЯ}'],
    uploadedAt: '2024-10-20T10:00:00',
    uploadedBy: 'Администратор'
  },
  {
    id: 5,
    name: 'Шаблон - Отчет финансового управляющего.docx',
    description: 'Отчет о результатах реализации имущества',
    size: 45600,
    category: 'Отчёты',
    variables: ['{ФИО}', '{НОМЕР_ДЕЛА}', '{ДАТА_ПРОЦЕДУРЫ}', '{КРЕДИТОРЫ}', '{КОНКУРСНАЯ_МАССА}'],
    uploadedAt: '2024-10-20T10:30:00',
    uploadedBy: 'Администратор'
  }
]

export default function DocumentsPage() {
  const { templates = mockTemplates, addTemplate, deleteTemplate } = useApp()
  const [uploading, setUploading] = useState(false)

  const handleFileUpload = (e) => {
    const file = e.target.files[0]
    if (!file) return

    // Проверка формата - только Word
    if (!file.name.endsWith('.doc') && !file.name.endsWith('.docx')) {
      alert('Можно загружать только Word документы (.doc, .docx)')
      e.target.value = ''
      return
    }

    setUploading(true)
    
    const reader = new FileReader()
    reader.onload = (event) => {
      const newTemplate = {
        id: Date.now(),
        name: file.name,
        description: 'Новый шаблон',
        size: file.size,
        category: 'Другое',
        variables: [],
        uploadedAt: new Date().toISOString(),
        uploadedBy: 'Текущий пользователь',
        data: event.target.result
      }
      
      addTemplate(newTemplate)
      setUploading(false)
      e.target.value = ''
    }
    reader.readAsDataURL(file)
  }

  const handleDownload = (template) => {
    // Создаем ссылку для скачивания
    const link = document.createElement('a')
    link.href = template.data || '#'
    link.download = template.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
  }

  const getCategoryColor = (category) => {
    const colors = {
      'Досудебка': 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
      'Введение процедуры': 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
      'Процедура': 'bg-green-500/10 text-green-600 dark:text-green-400',
      'Отчёты': 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
      'Другое': 'bg-gray-500/10 text-gray-600 dark:text-gray-400'
    }
    return colors[category] || colors['Другое']
  }

  return (
    <div className="space-y-6">
      {/* Info Card */}
      <Card className="bg-blue-500/10 border-blue-500/20">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <FileText className="h-5 w-5 text-blue-500 mt-0.5" />
            <div>
              <h3 className="font-semibold text-blue-600 dark:text-blue-400">Шаблоны документов</h3>
              <p className="text-sm text-muted-foreground mt-1">
                Здесь хранятся шаблоны Word документов с переменными (например: <code className="bg-background px-1 rounded">{'{ФИО}'}</code>, <code className="bg-background px-1 rounded">{'{ДАТА_РОЖДЕНИЯ}'}</code>).
                При генерации документа эти переменные автоматически заменяются на данные из карточки договора.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Upload Card */}
      <Card>
        <CardHeader>
          <CardTitle>Загрузить новый шаблон</CardTitle>
          <CardDescription>
            Загрузите Word документ с переменными в фигурных скобках: {'{ФИО}'}, {'{АДРЕС}'}, {'{ДАТА_РОЖДЕНИЯ}'} и т.д.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4">
            <input
              type="file"
              id="file-upload"
              className="hidden"
              onChange={handleFileUpload}
              accept=".doc,.docx"
              disabled={uploading}
            />
            <label htmlFor="file-upload">
              <Button asChild disabled={uploading}>
                <span>
                  <Upload className="h-4 w-4 mr-2" />
                  {uploading ? 'Загрузка...' : 'Выбрать шаблон'}
                </span>
              </Button>
            </label>
            <p className="text-sm text-muted-foreground">
              Поддерживаются только .doc и .docx файлы
            </p>
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
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Название шаблона</TableHead>
                <TableHead>Описание</TableHead>
                <TableHead>Категория</TableHead>
                <TableHead>Переменные</TableHead>
                <TableHead>Размер</TableHead>
                <TableHead className="w-28">Действия</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {templates.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
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
                    <TableCell className="text-sm text-muted-foreground">
                      {template.description}
                    </TableCell>
                    <TableCell>
                      <Badge className={getCategoryColor(template.category)}>
                        {template.category}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-wrap gap-1">
                        {template.variables.slice(0, 3).map((variable, idx) => (
                          <code key={idx} className="text-xs bg-muted px-1.5 py-0.5 rounded">
                            {variable}
                          </code>
                        ))}
                        {template.variables.length > 3 && (
                          <span className="text-xs text-muted-foreground">
                            +{template.variables.length - 3}
                          </span>
                        )}
                      </div>
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {formatFileSize(template.size)}
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
                          title="Редактировать"
                        >
                          <Edit className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => {
                            if (window.confirm('Удалить этот шаблон?')) {
                              deleteTemplate(template.id)
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

      {/* Variables Reference */}
      <Card>
        <CardHeader>
          <CardTitle>Справка по переменным</CardTitle>
          <CardDescription>
            Используйте эти переменные в ваших Word шаблонах
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
            <div className="space-y-1">
              <h4 className="font-semibold">Основная информация:</h4>
              <ul className="space-y-1 text-muted-foreground">
                <li><code className="bg-muted px-1 rounded">{'{ФИО}'}</code> - Полное имя</li>
                <li><code className="bg-muted px-1 rounded">{'{ДАТА_РОЖДЕНИЯ}'}</code> - Дата рождения</li>
                <li><code className="bg-muted px-1 rounded">{'{МЕСТО_РОЖДЕНИЯ}'}</code> - Место рождения</li>
                <li><code className="bg-muted px-1 rounded">{'{СНИЛС}'}</code> - СНИЛС</li>
                <li><code className="bg-muted px-1 rounded">{'{ПАСПОРТ}'}</code> - Паспорт</li>
                <li><code className="bg-muted px-1 rounded">{'{ИНН}'}</code> - ИНН</li>
              </ul>
            </div>
            <div className="space-y-1">
              <h4 className="font-semibold">Адреса и контакты:</h4>
              <ul className="space-y-1 text-muted-foreground">
                <li><code className="bg-muted px-1 rounded">{'{АДРЕС_РЕГИСТРАЦИИ}'}</code> - Адрес</li>
                <li><code className="bg-muted px-1 rounded">{'{АДРЕС_КОРРЕСПОНДЕНЦИИ}'}</code> - Адрес для писем</li>
                <li><code className="bg-muted px-1 rounded">{'{ТЕЛЕФОН}'}</code> - Телефон</li>
                <li><code className="bg-muted px-1 rounded">{'{EMAIL}'}</code> - Email</li>
              </ul>
            </div>
            <div className="space-y-1">
              <h4 className="font-semibold">Судебные данные:</h4>
              <ul className="space-y-1 text-muted-foreground">
                <li><code className="bg-muted px-1 rounded">{'{СУД}'}</code> - Название суда</li>
                <li><code className="bg-muted px-1 rounded">{'{НОМЕР_ДЕЛА}'}</code> - № дела</li>
                <li><code className="bg-muted px-1 rounded">{'{СУДЬЯ}'}</code> - ФИО судьи</li>
                <li><code className="bg-muted px-1 rounded">{'{ДАТА_РЕШЕНИЯ}'}</code> - Дата решения</li>
                <li><code className="bg-muted px-1 rounded">{'{СУММА_ДОЛГА}'}</code> - Сумма долга</li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
