import { useState, useEffect } from 'react'
import { apiRequest } from '../../config/api'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { Label } from '../ui/label'
import { Toast } from '../ui/toast'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '../ui/dialog'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import Loading from '../Loading'

export default function BailiffsDatabase() {
  const [bailiffs, setBailiffs] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [bailiffToDelete, setBailiffToDelete] = useState(null)
  const [editingBailiff, setEditingBailiff] = useState(null)
  const [formData, setFormData] = useState({
    department: '',
    address: '',
    phone: '',
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
    const loadBailiffs = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/bailiffs?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setBailiffs(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setBailiffs([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке отделений приставов:', error)
        setToast({ message: 'Не удалось загрузить список отделений', type: 'error' })
        setBailiffs([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadBailiffs()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      department: '',
      address: '',
      phone: '',
    })
    setEditingBailiff(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (bailiff) => {
    setEditingBailiff(bailiff)
    setFormData({
      department: bailiff.department || '',
      address: bailiff.address || '',
      phone: bailiff.phone || '',
    })
    setEditDialogOpen(true)
  }

  const handleSave = async () => {
    if (!formData.department.trim()) {
      setToast({ message: 'Отделение обязательно', type: 'error' })
      return
    }

    try {
      setSaving(true)

      const data = {
        department: formData.department.trim(),
        address: formData.address.trim() || null,
        phone: formData.phone.trim() || null,
      }

      if (editingBailiff) {
        await apiRequest(`/api/v1/bailiffs/${editingBailiff.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Отделение успешно обновлено', type: 'success' })
      } else {
        await apiRequest('/api/v1/bailiffs', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Отделение успешно создано', type: 'success' })
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
      const response = await apiRequest(`/api/v1/bailiffs?${params.toString()}`)
      if (response && typeof response === 'object') {
        setBailiffs(response.items || [])
        setTotal(response.total || 0)
        setPages(response.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при сохранении отделения:', error)
      const errorMessage = error.body?.error || 'Не удалось сохранить отделение'
      setToast({ message: errorMessage, type: 'error' })
    } finally {
      setSaving(false)
    }
  }

  const handleDeleteClick = (bailiff) => {
    setBailiffToDelete(bailiff)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!bailiffToDelete) return

    try {
      await apiRequest(`/api/v1/bailiffs/${bailiffToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Отделение успешно удалено', type: 'success' })
      setDeleteDialogOpen(false)
      setBailiffToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/bailiffs?${params.toString()}`)
      if (data && typeof data === 'object') {
        setBailiffs(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении отделения:', error)
      setToast({ message: 'Не удалось удалить отделение', type: 'error' })
      setDeleteDialogOpen(false)
      setBailiffToDelete(null)
    }
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
            <DialogTitle>Удаление отделения</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить отделение "{bailiffToDelete?.department}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setBailiffToDelete(null)
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
            <DialogTitle>{editingBailiff ? 'Редактировать отделение' : 'Новое отделение'}</DialogTitle>
            <DialogDescription>
              {editingBailiff ? 'Измените данные отделения' : 'Добавьте новое отделение судебных приставов в базу данных'}
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="space-y-2">
              <Label htmlFor="department">Отделение *</Label>
              <Input
                id="department"
                value={formData.department}
                onChange={(e) => setFormData({ ...formData, department: e.target.value })}
                placeholder="Введите наименование отделения"
              />
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
            <div className="space-y-2">
              <Label htmlFor="phone">Телефон</Label>
              <Input
                id="phone"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="Введите телефон"
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
          <h2 className="text-3xl font-bold">Судебные приставы</h2>
          <p className="text-muted-foreground">Управление базой ФССП</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить отделение
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список отделений ({total})</CardTitle>
          <CardDescription>Все отделения судебных приставов в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по отделению, адресу, телефону"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>

          {loading ? (
            <div className="py-8">
              <Loading text="Загрузка отделений..." />
            </div>
          ) : (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Отделение</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead>Телефон</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {bailiffs.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Отделения не найдены'
                          : 'Нет отделений. Добавьте первое отделение!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    bailiffs.map((bailiff) => (
                      <TableRow key={bailiff.id}>
                        <TableCell className="font-medium">{bailiff.department}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{bailiff.address || '-'}</TableCell>
                        <TableCell className="text-sm">{bailiff.phone || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(bailiff)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(bailiff)}
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
