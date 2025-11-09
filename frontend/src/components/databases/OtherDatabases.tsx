import { useState, useEffect } from 'react'
import { apiRequest } from '../../config/api'
import { useApp } from '../../context/AppContext'
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
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search, X } from 'lucide-react'
import Loading from '../Loading'

export function FnsDatabase() {
  const [fns, setFns] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [fnsToDelete, setFnsToDelete] = useState(null)
  const [editingFns, setEditingFns] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    address: '',
    code: '',
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
    const loadFns = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/fns?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setFns(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setFns([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке отделений ФНС:', error)
        setToast({ message: 'Не удалось загрузить список отделений', type: 'error' })
        setFns([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadFns()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      name: '',
      address: '',
      code: '',
    })
    setEditingFns(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (fnsItem) => {
    setEditingFns(fnsItem)
    setFormData({
      name: fnsItem.name || '',
      address: fnsItem.address || '',
      code: fnsItem.code || '',
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
        code: formData.code.trim() || null,
      }

      if (editingFns) {
        await apiRequest(`/api/v1/fns/${editingFns.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Отделение успешно обновлено', type: 'success' })
      } else {
        await apiRequest('/api/v1/fns', {
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
      const response = await apiRequest(`/api/v1/fns?${params.toString()}`)
      if (response && typeof response === 'object') {
        setFns(response.items || [])
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

  const handleDeleteClick = (fnsItem) => {
    setFnsToDelete(fnsItem)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!fnsToDelete) return

    try {
      await apiRequest(`/api/v1/fns/${fnsToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Отделение успешно удалено', type: 'success' })
      setDeleteDialogOpen(false)
      setFnsToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/fns?${params.toString()}`)
      if (data && typeof data === 'object') {
        setFns(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении отделения:', error)
      setToast({ message: 'Не удалось удалить отделение', type: 'error' })
      setDeleteDialogOpen(false)
      setFnsToDelete(null)
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
              Вы уверены, что хотите удалить отделение "{fnsToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setFnsToDelete(null)
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
            <DialogTitle>{editingFns ? 'Редактировать отделение' : 'Новое отделение'}</DialogTitle>
            <DialogDescription>
              {editingFns ? 'Измените данные отделения' : 'Добавьте новое отделение ФНС в базу данных'}
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
            <div className="space-y-2">
              <Label htmlFor="code">Код</Label>
              <Input
                id="code"
                value={formData.code}
                onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                placeholder="Введите код"
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
          <h2 className="text-3xl font-bold">ФНС</h2>
          <p className="text-muted-foreground">Управление базой инспекций ФНС</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить отделение
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список отделений ({total})</CardTitle>
          <CardDescription>Все отделения ФНС в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по наименованию, адресу, коду"
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
                    <TableHead>Наименование</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead>Код</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {fns.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Отделения не найдены'
                          : 'Нет отделений. Добавьте первое отделение!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    fns.map((fnsItem) => (
                      <TableRow key={fnsItem.id}>
                        <TableCell className="font-medium">{fnsItem.name}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{fnsItem.address || '-'}</TableCell>
                        <TableCell className="text-sm font-mono">{fnsItem.code || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(fnsItem)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(fnsItem)}
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

// Generic database component for other databases (MCHS, Rosgvardia)
export function GenericDatabase({ dbKey, title, description, fields }) {
  const { databases, addToDatabase } = useApp()
  const [showForm, setShowForm] = useState(false)
  const [formData, setFormData] = useState(
    fields.reduce((acc, field) => ({ ...acc, [field.key]: '' }), {})
  )

  const handleSubmit = (e) => {
    e.preventDefault()
    addToDatabase(dbKey, formData)
    setFormData(fields.reduce((acc, field) => ({ ...acc, [field.key]: '' }), {}))
    setShowForm(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">{title}</h2>
          <p className="text-muted-foreground">{description}</p>
        </div>
        <Button onClick={() => setShowForm(!showForm)}>
          {showForm ? (
            <>
              <X className="h-4 w-4 mr-2" />
              Отмена
            </>
          ) : (
            <>
              <Plus className="h-4 w-4 mr-2" />
              Добавить запись
            </>
          )}
        </Button>
      </div>

      {showForm && (
        <Card>
          <CardHeader>
            <CardTitle>Новая запись</CardTitle>
            <CardDescription>Добавьте новую запись в базу данных</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 gap-4">
                {fields.map((field) => (
                  <div key={field.key} className="space-y-2">
                    <Label htmlFor={field.key}>
                      {field.label} {field.required && '*'}
                    </Label>
                    <Input
                      id={field.key}
                      value={formData[field.key]}
                      onChange={(e) => setFormData({ ...formData, [field.key]: e.target.value })}
                      required={field.required}
                    />
                  </div>
                ))}
              </div>
              <Button type="submit">Сохранить</Button>
            </form>
          </CardContent>
        </Card>
      )}

      <Card>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>№</TableHead>
              {fields.map((field) => (
                <TableHead key={field.key}>{field.label}</TableHead>
              ))}
              <TableHead className="w-20">Действия</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {databases[dbKey]?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={fields.length + 2} className="text-center py-12 text-muted-foreground">
                  Нет записей
                </TableCell>
              </TableRow>
            ) : (
              databases[dbKey]?.map((item, index) => (
                <TableRow key={item.id}>
                  <TableCell>{index + 1}</TableCell>
                  {fields.map((field) => (
                    <TableCell key={field.key} className={field.key === fields[0].key ? 'font-medium' : 'text-sm text-muted-foreground'}>
                      {item[field.key]}
                    </TableCell>
                  ))}
                  <TableCell>
                    <div className="flex gap-2">
                      <Button variant="ghost" size="icon">
                        <Edit className="h-4 w-4" />
                      </Button>
                      <Button variant="ghost" size="icon">
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </Card>
    </div>
  )
}

export function MchsDatabase() {
  const [mchs, setMchs] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [mchsToDelete, setMchsToDelete] = useState(null)
  const [editingMchs, setEditingMchs] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
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
    const loadMchs = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/mchs?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setMchs(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setMchs([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке отделений ГИМС МЧС:', error)
        setToast({ message: 'Не удалось загрузить список отделений', type: 'error' })
        setMchs([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadMchs()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      name: '',
      address: '',
      phone: '',
    })
    setEditingMchs(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (mchsItem) => {
    setEditingMchs(mchsItem)
    setFormData({
      name: mchsItem.name || '',
      address: mchsItem.address || '',
      phone: mchsItem.phone || '',
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
        phone: formData.phone.trim() || null,
      }

      if (editingMchs) {
        await apiRequest(`/api/v1/mchs/${editingMchs.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Отделение успешно обновлено', type: 'success' })
      } else {
        await apiRequest('/api/v1/mchs', {
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
      const response = await apiRequest(`/api/v1/mchs?${params.toString()}`)
      if (response && typeof response === 'object') {
        setMchs(response.items || [])
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

  const handleDeleteClick = (mchsItem) => {
    setMchsToDelete(mchsItem)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!mchsToDelete) return

    try {
      await apiRequest(`/api/v1/mchs/${mchsToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Отделение успешно удалено', type: 'success' })
      setDeleteDialogOpen(false)
      setMchsToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/mchs?${params.toString()}`)
      if (data && typeof data === 'object') {
        setMchs(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении отделения:', error)
      setToast({ message: 'Не удалось удалить отделение', type: 'error' })
      setDeleteDialogOpen(false)
      setMchsToDelete(null)
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
              Вы уверены, что хотите удалить отделение "{mchsToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setMchsToDelete(null)
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
            <DialogTitle>{editingMchs ? 'Редактировать отделение' : 'Новое отделение'}</DialogTitle>
            <DialogDescription>
              {editingMchs ? 'Измените данные отделения' : 'Добавьте новое отделение ГИМС МЧС в базу данных'}
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
          <h2 className="text-3xl font-bold">ГИМС МЧС</h2>
          <p className="text-muted-foreground">Управление базой ГИМС МЧС России</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить отделение
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список отделений ({total})</CardTitle>
          <CardDescription>Все отделения ГИМС МЧС в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по наименованию, адресу, телефону"
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
                    <TableHead>Наименование</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead>Телефон</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mchs.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Отделения не найдены'
                          : 'Нет отделений. Добавьте первое отделение!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    mchs.map((mchsItem) => (
                      <TableRow key={mchsItem.id}>
                        <TableCell className="font-medium">{mchsItem.name}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{mchsItem.address || '-'}</TableCell>
                        <TableCell className="text-sm">{mchsItem.phone || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(mchsItem)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(mchsItem)}
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

export function RosgvardiaDatabase() {
  const [rosgvardia, setRosgvardia] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [rosgvardiaToDelete, setRosgvardiaToDelete] = useState(null)
  const [editingRosgvardia, setEditingRosgvardia] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
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
    const loadRosgvardia = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
        })

        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch)
        }

        const data = await apiRequest(`/api/v1/rosgvardia?${params.toString()}`)

        if (data && typeof data === 'object' && Array.isArray(data.items)) {
          setRosgvardia(data.items)
          setTotal(data.total || 0)
          setPages(data.pages || 1)
        } else {
          setRosgvardia([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке отделений Росгвардии:', error)
        setToast({ message: 'Не удалось загрузить список отделений', type: 'error' })
        setRosgvardia([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadRosgvardia()
  }, [page, debouncedSearch, limit])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const resetForm = () => {
    setFormData({
      name: '',
      address: '',
      phone: '',
    })
    setEditingRosgvardia(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (rosgvardiaItem) => {
    setEditingRosgvardia(rosgvardiaItem)
    setFormData({
      name: rosgvardiaItem.name || '',
      address: rosgvardiaItem.address || '',
      phone: rosgvardiaItem.phone || '',
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
        phone: formData.phone.trim() || null,
      }

      if (editingRosgvardia) {
        await apiRequest(`/api/v1/rosgvardia/${editingRosgvardia.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        })
        setToast({ message: 'Отделение успешно обновлено', type: 'success' })
      } else {
        await apiRequest('/api/v1/rosgvardia', {
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
      const response = await apiRequest(`/api/v1/rosgvardia?${params.toString()}`)
      if (response && typeof response === 'object') {
        setRosgvardia(response.items || [])
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

  const handleDeleteClick = (rosgvardiaItem) => {
    setRosgvardiaToDelete(rosgvardiaItem)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!rosgvardiaToDelete) return

    try {
      await apiRequest(`/api/v1/rosgvardia/${rosgvardiaToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Отделение успешно удалено', type: 'success' })
      setDeleteDialogOpen(false)
      setRosgvardiaToDelete(null)

      // Перезагружаем список
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }
      const data = await apiRequest(`/api/v1/rosgvardia?${params.toString()}`)
      if (data && typeof data === 'object') {
        setRosgvardia(data.items || [])
        setTotal(data.total || 0)
        setPages(data.pages || 1)
      }
    } catch (error) {
      console.error('Ошибка при удалении отделения:', error)
      setToast({ message: 'Не удалось удалить отделение', type: 'error' })
      setDeleteDialogOpen(false)
      setRosgvardiaToDelete(null)
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
              Вы уверены, что хотите удалить отделение "{rosgvardiaToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setRosgvardiaToDelete(null)
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
            <DialogTitle>{editingRosgvardia ? 'Редактировать отделение' : 'Новое отделение'}</DialogTitle>
            <DialogDescription>
              {editingRosgvardia ? 'Измените данные отделения' : 'Добавьте новое отделение Росгвардии в базу данных'}
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
          <h2 className="text-3xl font-bold">Росгвардия</h2>
          <p className="text-muted-foreground">Управление базой Росгвардии</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить отделение
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список отделений ({total})</CardTitle>
          <CardDescription>Все отделения Росгвардии в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по наименованию, адресу, телефону"
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
                    <TableHead>Наименование</TableHead>
                    <TableHead>Адрес</TableHead>
                    <TableHead>Телефон</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {rosgvardia.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3)
                          ? 'Отделения не найдены'
                          : 'Нет отделений. Добавьте первое отделение!'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    rosgvardia.map((rosgvardiaItem) => (
                      <TableRow key={rosgvardiaItem.id}>
                        <TableCell className="font-medium">{rosgvardiaItem.name}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{rosgvardiaItem.address || '-'}</TableCell>
                        <TableCell className="text-sm">{rosgvardiaItem.phone || '-'}</TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleEditClick(rosgvardiaItem)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDeleteClick(rosgvardiaItem)}
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
