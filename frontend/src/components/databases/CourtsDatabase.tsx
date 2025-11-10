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

export default function CourtsDatabase() {
  const [courts, setCourts] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [courtToDelete, setCourtToDelete] = useState(null)
  const [editingCourt, setEditingCourt] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
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
    const loadCourts = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/courts?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setCourts(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setCourts([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке судов:', error)
        setToast({ message: 'Не удалось загрузить список судов', type: 'error' })
        setCourts([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadCourts()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      name: '',
      address: '',
    })
    setEditingCourt(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (court) => {
    setEditingCourt(court)
    setFormData({
      name: court.name || '',
      address: court.address || '',
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
        address: formData.address.trim() || null,
      }

      if (editingCourt) {
        await apiRequest(`/api/v1/courts/${editingCourt.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Суд успешно обновлен', type: 'success' })
      } else {
        await apiRequest('/api/v1/courts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Суд успешно создан', type: 'success' })
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
      const response = await apiRequest(`/api/v1/courts?${params.toString()}`)
      if (response && typeof response === 'object') {
        setCourts(response.items || [])
        setTotal(response.total || 0)
        setPages(response.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при сохранении суда:', error)
      const errorMessage = error.body?.error || 'Не удалось сохранить суд'
      setToast({ message: errorMessage, type: 'error' })
    } finally {
      setSaving(false)
    }
  }

  const handleDeleteClick = (court) => {
    setCourtToDelete(court)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!courtToDelete) return

    try {
      await apiRequest(`/api/v1/courts/${courtToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Суд успешно удален', type: 'success' })
      setDeleteDialogOpen(false)
      setCourtToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/courts?${params.toString()}`)
      if (data && typeof data === 'object') {
        setCourts(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении суда:', error)
      setToast({ message: 'Не удалось удалить суд', type: 'error' })
      setDeleteDialogOpen(false)
      setCourtToDelete(null)
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
            <DialogTitle>Удаление суда</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить суд "{courtToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setCourtToDelete(null)
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
            <DialogTitle>{editingCourt ? 'Редактировать суд' : 'Новый суд'}</DialogTitle>
            <DialogDescription>
              {editingCourt ? 'Измените данные суда' : 'Добавьте новый арбитражный суд в базу данных'}
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
          <h2 className="text-3xl font-bold">Арбитражные суды</h2>
          <p className="text-muted-foreground">Управление базой арбитражных судов</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить суд
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список судов ({total})</CardTitle>
          <CardDescription>Все арбитражные суды в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по наименованию, адресу"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>

          {loading ? (
            <div className="py-8">
              <Loading text="Загрузка судов..." />
            </div>
          ) : (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Наименование</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {courts.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Суды не найдены'
                          : 'Нет судов. Добавьте первый суд!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    courts.map((court) => (
                      <TableRow key={court.id}>
                        <TableCell className="font-medium">{court.name}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{court.address || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(court)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(court)}
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
