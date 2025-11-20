import { useEffect, useMemo, useState } from 'react'
import { useQueryClient } from '@tanstack/react-query'

import { apiRequest } from '../../config/api'
import { useApp } from '@/context/AppContext'
import { Button } from '@ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@ui/table'
import { Input } from '@ui/input'
import { Label } from '@ui/label'
import { notify } from '@ui/toast'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '../ui/dialog'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'

interface GostekhnadzorItem {
  id: number
  name: string
  address: string
  [key: string]: unknown
}

export default function GostekhnadzorDatabase() {
  const { referenceData } = useApp()
  const queryClient = useQueryClient()
  const [saving, setSaving] = useState(false)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [editDialogOpen, setEditDialogOpen] = useState(false)
  const [gostekhnadzorToDelete, setGostekhnadzorToDelete] = useState<GostekhnadzorItem | null>(null)
  const [editingGostekhnadzor, setEditingGostekhnadzor] = useState<GostekhnadzorItem | null>(null)
  const [formData, setFormData] = useState({ name: '', address: '' })

  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [page, setPage] = useState(1)
  const limit = 10
  const gostekhnadzor = referenceData.gostekhnadzor ?? []

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search), 200)
    return () => clearTimeout(timer)
  }, [search])

  const filteredItems = useMemo(() => {
    const term = debouncedSearch.trim().toLowerCase()
    if (!term) return gostekhnadzor

    return gostekhnadzor.filter((item) =>
      [item.name, item.address]
        .filter(Boolean)
        .some((field) => String(field).toLowerCase().includes(term))
    )
  }, [gostekhnadzor, debouncedSearch])

  const total = filteredItems.length
  const pages = Math.max(1, Math.ceil(total / limit))
  const pageSafe = Math.min(page, pages)
  const paginated = useMemo(
    () => filteredItems.slice((pageSafe - 1) * limit, pageSafe * limit),
    [filteredItems, pageSafe, limit]
  )

  useEffect(() => setPage(1), [debouncedSearch])
  useEffect(() => {
    if (page > pages) setPage(pages)
  }, [page, pages])

  const refreshReferences = async () => {
    await queryClient.invalidateQueries({ queryKey: ["references", "all"] })
  }

  const resetForm = () => {
    setFormData({ name: '', address: '' })
    setEditingGostekhnadzor(null)
  }

  const handleCreateClick = () => {
    resetForm()
    setEditDialogOpen(true)
  }

  const handleEditClick = (item: GostekhnadzorItem) => {
    setEditingGostekhnadzor(item)
    setFormData({ name: item.name || '', address: item.address || '' })
    setEditDialogOpen(true)
  }

  const handleSave = async () => {
    if (!formData.name.trim()) {
      notify({ message: 'Наименование обязательно', type: 'error' })
      return
    }

    try {
      setSaving(true)
      const data = { name: formData.name.trim(), address: formData.address.trim() || null }

      if (editingGostekhnadzor) {
        await apiRequest(`/gostekhnadzor/${editingGostekhnadzor.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        })
        notify({ message: 'Отделение успешно обновлено', type: 'success' })
      } else {
        await apiRequest('/gostekhnadzor', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        })
        notify({ message: 'Отделение успешно создано', type: 'success' })
      }

      setEditDialogOpen(false)
      resetForm()
      await refreshReferences()
    } catch (error: unknown) {
      console.error('Ошибка при сохранении отделения:', error)
      const errorMessage = (error as { body?: { error?: string } })?.body?.error || 'Не удалось сохранить отделение'
      notify({ message: errorMessage, type: 'error' })
    } finally {
      setSaving(false)
    }
  }

  const handleDeleteClick = (item: GostekhnadzorItem) => {
    setGostekhnadzorToDelete(item)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!gostekhnadzorToDelete) return

    try {
      await apiRequest(`/gostekhnadzor/${gostekhnadzorToDelete.id}`, { method: 'DELETE' })
      notify({ message: 'Отделение успешно удалено', type: 'success' })
      setDeleteDialogOpen(false)
      setGostekhnadzorToDelete(null)
      await refreshReferences()
    } catch (error) {
      console.error('Ошибка при удалении отделения:', error)
      notify({ message: 'Не удалось удалить отделение', type: 'error' })
      setDeleteDialogOpen(false)
      setGostekhnadzorToDelete(null)
    }
  }

  return (
    <div className="space-y-6 p-6">
      <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Удаление отделения</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить отделение "{gostekhnadzorToDelete?.name}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setGostekhnadzorToDelete(null)
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
        if (!open) resetForm()
      }}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>{editingGostekhnadzor ? 'Редактировать отделение' : 'Новое отделение'}</DialogTitle>
            <DialogDescription>
              {editingGostekhnadzor ? 'Измените данные отделения' : 'Добавьте новое отделение Гостехнадзора в базу данных'}
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
            <Button variant="outline" onClick={() => { setEditDialogOpen(false); resetForm() }}>
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
          <h2 className="text-3xl font-bold">Гостехнадзор</h2>
          <p className="text-muted-foreground">Управление базой Гостехнадзора</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить отделение
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список отделений ({total})</CardTitle>
          <CardDescription>Все отделения Гостехнадзора в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
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

          <div className="relative max-h-[58vh] overflow-auto">
            <Table>
              <TableHeader className="sticky top-0 z-10 bg-card">
                <TableRow className="bg-card">
                  <TableHead className="sticky top-0 bg-card">Наименование</TableHead>
                  <TableHead className="sticky top-0 bg-card">Адрес</TableHead>
                  <TableHead className="sticky top-0 w-28 bg-card">Действия</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {paginated.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={3} className="text-center py-12 text-muted-foreground">
                      {debouncedSearch ? 'Отделения не найдены' : 'Нет отделений. Добавьте первое отделение!'}
                    </TableCell>
                  </TableRow>
                ) : (
                  paginated.map((item) => (
                    <TableRow key={item.id}>
                      <TableCell className="font-medium">{item.name}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{item.address || '-'}</TableCell>
                      <TableCell>
                        <div className="flex">
                          <Button variant="ghost" size="icon" onClick={() => handleEditClick(item)} title="Редактировать">
                            <Edit className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" onClick={() => handleDeleteClick(item)} title="Удалить">
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>

          {pages > 1 && (
            <div className="flex items-center justify-between mt-4 p-4 border-t">
              <div className="text-sm text-muted-foreground">
                Показано {(pageSafe - 1) * limit + 1} - {Math.min(pageSafe * limit, total)} из {total}
              </div>
              <div className="flex gap-2 items-center">
                <Button variant="outline" size="sm" onClick={() => setPage(pageSafe - 1)} disabled={pageSafe === 1}>
                  <ChevronLeft className="h-4 w-4" />
                  Назад
                </Button>
                <div className="flex items-center gap-1">
                  {(() => {
                    const pageNumbers: (number | string)[] = []
                    if (pages <= 7) {
                      for (let i = 1; i <= pages; i++) pageNumbers.push(i)
                    } else {
                      pageNumbers.push(1)
                      if (pageSafe > 3) pageNumbers.push('...')
                      const start = Math.max(2, pageSafe - 1)
                      const end = Math.min(pages - 1, pageSafe + 1)
                      for (let i = start; i <= end; i++) {
                        if (i !== 1 && i !== pages) pageNumbers.push(i)
                      }
                      if (pageSafe < pages - 2) pageNumbers.push('...')
                      if (pages > 1) pageNumbers.push(pages)
                    }
                    return pageNumbers.map((pageNum, idx) =>
                      pageNum === '...'
                        ? <span key={`ellipsis-${idx}`} className="px-2 text-muted-foreground">...</span>
                        : (
                          <Button
                            key={pageNum}
                            variant={pageSafe === pageNum ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setPage(pageNum as number)}
                            className="w-10"
                          >
                            {pageNum}
                          </Button>
                        )
                    )
                  })()}
                </div>
                <Button variant="outline" size="sm" onClick={() => setPage(pageSafe + 1)} disabled={pageSafe === pages}>
                  Вперёд
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
