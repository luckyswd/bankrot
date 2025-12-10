import { useEffect, useMemo, useState } from 'react'
import { useQueryClient } from '@tanstack/react-query'

import { apiRequest } from '../../config/api'
import { useApp } from '@/context/AppContext'
import { Button } from '@ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@ui/table'
import { Input } from '@ui/input'
import { notify } from '@ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import { useModalStore } from '../Modals/ModalProvider'

interface RosgvardiaItem {
  id: number
  name: string
  address: string | null
  phone: string | null
  [key: string]: unknown
}

export function RosgvardiaDatabase() {
  const { referenceData } = useApp()
  const queryClient = useQueryClient()
  const { openModal } = useModalStore()

  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [page, setPage] = useState(1)
  const limit = 10
  const rosgvardia = referenceData.rosgvardia as RosgvardiaItem[] ?? []

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search), 200)
    return () => clearTimeout(timer)
  }, [search])

  const filteredRosgvardia = useMemo(() => {
    const term = debouncedSearch.trim().toLowerCase()
    if (!term) return rosgvardia

    return rosgvardia.filter((item) =>
      [item.name, item.address, item.phone]
        .filter(Boolean)
        .some((field) => String(field).toLowerCase().includes(term))
    )
  }, [rosgvardia, debouncedSearch])

  const total = filteredRosgvardia.length
  const pages = Math.max(1, Math.ceil(total / limit))
  const pageSafe = Math.min(page, pages)
  const paginated = useMemo(
    () => filteredRosgvardia.slice((pageSafe - 1) * limit, pageSafe * limit),
    [filteredRosgvardia, pageSafe, limit]
  )

  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  useEffect(() => {
    if (page > pages) setPage(pages)
  }, [page, pages])

  const refreshReferences = async () => {
    await queryClient.invalidateQueries({ queryKey: ["references", "all"] })
  }

  const handleCreateClick = () => {
    openModal('rosgvardiaForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await refreshReferences()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleEditClick = (rosgvardiaItem: RosgvardiaItem) => {
    openModal('rosgvardiaForm', {
      branch: rosgvardiaItem,
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await refreshReferences()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleDeleteClick = (rosgvardiaItem: RosgvardiaItem) => {
    openModal('confirm', {
      title: 'Удаление отделения',
      description: `Вы уверены, что хотите удалить отделение "${rosgvardiaItem.name}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/rosgvardia/${rosgvardiaItem.id}`, { method: 'DELETE' })
        notify({ message: 'Отделение успешно удалено', type: 'success' })
        await refreshReferences()
      },
    })
  }

  return (
    <div className="space-y-6 p-6">
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
                  <TableHead className="sticky top-0 bg-card">Телефон</TableHead>
                  <TableHead className="sticky top-0 w-28 bg-card">Действия</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {paginated.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                      {debouncedSearch ? 'Отделения не найдены' : 'Нет отделений. Добавьте первое отделение!'}
                    </TableCell>
                  </TableRow>
                ) : (
                  paginated.map((rosgvardiaItem) => (
                    <TableRow key={rosgvardiaItem.id}>
                      <TableCell className="font-medium">{rosgvardiaItem.name}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{rosgvardiaItem.address || '-'}</TableCell>
                      <TableCell className="text-sm">{rosgvardiaItem.phone || '-'}</TableCell>
                      <TableCell>
                        <div className="flex">
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
