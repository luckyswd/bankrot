import { useEffect, useMemo, useState } from 'react'
import { useQueryClient } from '@tanstack/react-query'
import { apiRequest } from '../../config/api'
import { useApp } from '@/context/AppContext'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { notify } from '../ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import { useModalStore } from '../Modals/ModalProvider'
interface Creditor {
  id: number
  name: string
  address: string | null
  headFullName: string | null
  bankDetails: string | null
  inn?: string | null
  ogrn?: string | null
  type?: string | null
  [key: string]: unknown
}

export default function CreditorsDatabase() {
  const { referenceData } = useApp()
  const queryClient = useQueryClient()
  const { openModal } = useModalStore()

  // Поиск и пагинация
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [page, setPage] = useState(1)
  const limit = 10
  const creditors = (referenceData.creditors as Creditor[] | undefined) ?? []

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search)
    }, 200)

    return () => clearTimeout(timer)
  }, [search])

  const filteredCreditors = useMemo(() => {
    const term = debouncedSearch.trim().toLowerCase()
    if (!term) return creditors

    return creditors.filter((c) =>
      [c.name, c.address, c.headFullName, c.bankDetails]
        .filter(Boolean)
        .some((field) => String(field).toLowerCase().includes(term))
    )
  }, [creditors, debouncedSearch])

  const total = filteredCreditors.length
  const pages = Math.max(1, Math.ceil(total / limit))
  const pageSafe = Math.min(page, pages)
  const paginated = useMemo(
    () => filteredCreditors.slice((pageSafe - 1) * limit, pageSafe * limit),
    [filteredCreditors, pageSafe, limit]
  )

  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  useEffect(() => {
    if (page > pages) {
      setPage(pages)
    }
  }, [page, pages])

  const refreshReferences = async () => {
    await queryClient.invalidateQueries({ queryKey: ["references", "all"] })
  }

  const handleCreateClick = () => {
    openModal('creditorForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await refreshReferences()
      },
      onError: (message: string) => {
        notify({ message, type: 'error' })
      },
    })
  }

  const handleEditClick = (creditor: Creditor) => {
    openModal('creditorForm', {
      creditor,
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await refreshReferences()
      },
      onError: (message: string) => {
        notify({ message, type: 'error' })
      },
    })
  }

  const handleDeleteClick = (creditor: Creditor) => {
    openModal('confirm', {
      title: 'Удаление кредитора',
      description: `Вы уверены, что хотите удалить кредитора "${creditor.name}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/creditors/${creditor.id}`, {
          method: 'DELETE',
        })
        notify({ message: 'Кредитор успешно удален', type: 'success' })
        await refreshReferences()
      },
    })
  }


  return (
    <div className="space-y-6 p-6">
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
                placeholder="Поиск по наименованию"
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
                  <TableHead className="sticky top-0 bg-card">ФИО руководителя</TableHead>
                  <TableHead className="sticky top-0 bg-card">Банковские реквизиты</TableHead>
                  <TableHead className="sticky top-0 w-28 bg-card">Действия</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {paginated.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={5} className="text-center py-12 text-muted-foreground">
                      {debouncedSearch ? 'Кредиторы не найдены' : 'Нет кредиторов. Добавьте первого кредитора!'}
                    </TableCell>
                  </TableRow>
                ) : (
                  paginated.map((creditor) => (
                    <TableRow key={creditor.id}>
                      <TableCell className="font-medium">{creditor.name}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{creditor.address || '-'}</TableCell>
                      <TableCell>{creditor.headFullName || '-'}</TableCell>
                      <TableCell className="text-sm">{creditor.bankDetails || '-'}</TableCell>
                      <TableCell>
                        <div className="flex">
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

          {/* Пагинация */}
          {pages > 1 && (
            <div className="flex items-center justify-between mt-4 p-4 border-t">
              <div className="text-sm text-muted-foreground">
                Показано {(pageSafe - 1) * limit + 1} - {Math.min(pageSafe * limit, total)} из {total}
              </div>
              <div className="flex gap-2 items-center">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setPage(pageSafe - 1)}
                  disabled={pageSafe === 1}
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

                      if (pageSafe > 3) {
                        pageNumbers.push('...')
                      }

                      const start = Math.max(2, pageSafe - 1)
                      const end = Math.min(pages - 1, pageSafe + 1)

                      for (let i = start; i <= end; i++) {
                        if (i !== 1 && i !== pages) {
                          pageNumbers.push(i)
                        }
                      }

                      if (pageSafe < pages - 2) {
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
                          variant={pageSafe === pageNum ? 'default' : 'outline'}
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
                  onClick={() => setPage(pageSafe + 1)}
                  disabled={pageSafe === pages}
                >
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
