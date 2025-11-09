import { useState, useEffect } from 'react'
import { apiRequest } from '../../config/api'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { Label } from '../ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select'
import { Toast } from '../ui/toast'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '../ui/dialog'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search, X } from 'lucide-react'
import Loading from '../Loading'

const CREDITOR_TYPES = [
  { value: 'bank', label: 'Банк' },
  { value: 'tax', label: 'Налоговая' },
  { value: 'other', label: 'Другое' },
]

export default function CreditorsDatabase() {
  const [creditors, setCreditors] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [creditorToDelete, setCreditorToDelete] = useState(null)
  const [editingCreditor, setEditingCreditor] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    inn: '',
    ogrn: '',
    type: '',
    address: '',
  })

  // Поиск и пагинация
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
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

  // Загрузка данных при изменении страницы или поиска
  useEffect(() => {
    const loadCreditors = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/creditors?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setCreditors(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setCreditors([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке кредиторов:', error)
        setToast({ message: 'Не удалось загрузить список кредиторов', type: 'error' })
        setCreditors([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadCreditors()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      name: '',
      inn: '',
      ogrn: '',
      type: '',
      address: '',
    })
    setEditingCreditor(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (creditor) => {
    setEditingCreditor(creditor)
    setFormData({
      name: creditor.name || '',
      inn: creditor.inn || '',
      ogrn: creditor.ogrn || '',
      type: creditor.type || '',
      address: creditor.address || '',
    })
    setEditDialogOpen(true)
  }

  const handleSave = async () => {
    if (!formData.name.trim()) {
      setToast({ message: 'Наименование обязательно', type: 'error' })
      return
    }

    try {
      setSaving(true)

      const data = {
        name: formData.name.trim(),
        inn: formData.inn.trim() || null,
        ogrn: formData.ogrn.trim() || null,
        type: formData.type || null,
        address: formData.address.trim() || null,
      }

      if (editingCreditor) {
        await apiRequest(`/api/v1/creditors/${editingCreditor.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Кредитор успешно обновлен', type: 'success' })
      } else {
        await apiRequest('/api/v1/creditors', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Кредитор успешно создан', type: 'success' })
      }

      setEditDialogOpen(false)
      resetForm()

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const response = await apiRequest(`/api/v1/creditors?${params.toString()}`)
      if (response && typeof response === 'object') {
        setCreditors(response.items || [])
        setTotal(response.total || 0)
        setPages(response.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при сохранении кредитора:', error)
      const errorMessage = error.body?.error || 'Не удалось сохранить кредитора'
      setToast({ message: errorMessage, type: 'error' })
    } finally {
      setSaving(false)
    }
  }

  const handleDeleteClick = (creditor) => {
    setCreditorToDelete(creditor)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!creditorToDelete) return

    try {
      await apiRequest(`/api/v1/creditors/${creditorToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Кредитор успешно удален', type: 'success' })
      setDeleteDialogOpen(false)
      setCreditorToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/creditors?${params.toString()}`)
      if (data && typeof data === 'object') {
        setCreditors(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении кредитора:', error)
      setToast({ message: 'Не удалось удалить кредитора', type: 'error' })
      setDeleteDialogOpen(false)
      setCreditorToDelete(null)
    }
  }

  const getTypeLabel = (type) => {
    const found = CREDITOR_TYPES.find((t) => t.value === type)
    return found ? found.label : type || '-'
  }

  return (
    <div className="space-y-6 p-6">
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
            <DialogTitle>Удаление кредитора</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить кредитора "{creditorToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setCreditorToDelete(null)
              }}
            >
              Отмена
            </Button>
            <Button variant="destructive" onClick={handleDeleteConfirm}>
              Удалить
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={editDialogOpen} onOpenChange={(open) => {
        setEditDialogOpen(open)
        if (!open) {
          resetForm()
        }
      }}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>{editingCreditor ? 'Редактировать кредитора' : 'Новый кредитор'}</DialogTitle>
            <DialogDescription>
              {editingCreditor ? 'Измените данные кредитора' : 'Добавьте нового кредитора в базу данных'}
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="space-y-2">
              <Label htmlFor="name">Наименование *</Label>
              <Input
                id="name"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="Введите наименование"
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="inn">ИНН</Label>
                <Input
                  id="inn"
                  value={formData.inn}
                  onChange={(e) => setFormData({ ...formData, inn: e.target.value })}
                  placeholder="Введите ИНН"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="ogrn">ОГРН</Label>
                <Input
                  id="ogrn"
                  value={formData.ogrn}
                  onChange={(e) => setFormData({ ...formData, ogrn: e.target.value })}
                  placeholder="Введите ОГРН"
                />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="type">Тип</Label>
              <Select 
                value={formData.type || undefined} 
                onValueChange={(value) => setFormData({ ...formData, type: value || '' })}
              >
                <SelectTrigger id="type">
                  <SelectValue placeholder="Выберите тип" />
                </SelectTrigger>
                <SelectContent>
                  {CREDITOR_TYPES.map((type) => (
                    <SelectItem key={type.value} value={type.value}>
                      {type.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="address">Адрес</Label>
              <Input
                id="address"
                value={formData.address}
                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                placeholder="Введите адрес"
              />
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setEditDialogOpen(false)
                resetForm()
              }}
            >
              Отмена
            </Button>
            <Button onClick={handleSave} disabled={saving}>
              {saving ? 'Сохранение...' : 'Сохранить'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">Кредиторы</h2>
          <p className="text-muted-foreground">Управление базой кредиторов</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить кредитора
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список кредиторов ({total})</CardTitle>
          <CardDescription>Все кредиторы в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по наименованию, ИНН, ОГРН"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>

          {loading ? (
            <div className="py-8">
              <Loading text="Загрузка кредиторов..." />
            </div>
          ) : (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Наименование</TableHead>
                    <TableHead>ИНН</TableHead>
                    <TableHead>ОГРН</TableHead>
                    <TableHead>Тип</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {creditors.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Кредиторы не найдены'
                          : 'Нет кредиторов. Добавьте первого кредитора!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    creditors.map((creditor) => (
                      <TableRow key={creditor.id}>
                        <TableCell className="font-medium">{creditor.name}</TableCell>
                        <TableCell className="font-mono text-sm">{creditor.inn || '-'}</TableCell>
                        <TableCell className="font-mono text-sm">{creditor.ogrn || '-'}</TableCell>
                        <TableCell>{getTypeLabel(creditor.type)}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{creditor.address || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(creditor)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(creditor)}
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
                          for (let i = 1; i <= pages; i++) {
                            pageNumbers.push(i)
                          }
                        } else {
                          pageNumbers.push(1)

                          if (page > 3) {
                            pageNumbers.push('...')
                          }

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
