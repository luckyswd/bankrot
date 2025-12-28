import { useEffect, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { apiRequest } from '../../config/api'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { notify } from '../ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import { useModalStore } from '../Modals/ModalProvider'

interface FinancialManager {
  id: number
  fio?: string
  inn?: string
  snils?: string
  email?: string
  phone?: string
  [key: string]: unknown
}

const fetchFinancialManagers = async (page: number = 1, limit: number = 10, search: string = '') => {
  const params = new URLSearchParams({
    page: page.toString(),
    limit: limit.toString(),
  })
  if (search) {
    params.append('search', search)
  }
  const response = await apiRequest(`/financial-managers?${params.toString()}`)
  return response
}

export default function FinancialManagersDatabase() {
  const { openModal } = useModalStore()

  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [page, setPage] = useState(1)
  const limit = 10

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search)
    }, 200)

    return () => clearTimeout(timer)
  }, [search])

  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const { data, isLoading, refetch } = useQuery({
    queryKey: ['financialManagers', page, limit, debouncedSearch],
    queryFn: () => fetchFinancialManagers(page, limit, debouncedSearch),
  })

  const financialManagers = (data?.items as FinancialManager[]) ?? []
  const total = data?.total ?? 0
  const pages = Math.max(1, Math.ceil(total / limit))

  useEffect(() => {
    if (page > pages && pages > 0) {
      setPage(pages)
    }
  }, [page, pages])

  const handleCreateClick = () => {
    openModal('financialManagerForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await refetch()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleEditClick = async (financialManager: FinancialManager) => {
    try {
      const fullData = await apiRequest(`/financial-managers/${financialManager.id}`)
      openModal('financialManagerForm', {
        financialManager: fullData,
        onSuccess: async (message: string) => {
          notify({ message, type: 'success' })
          await refetch()
        },
        onError: (message: string) => notify({ message, type: 'error' }),
      })
    } catch (err) {
      notify({ message: 'Не удалось загрузить данные финансового управляющего', type: 'error' })
    }
  }

  const handleDeleteClick = (financialManager: FinancialManager) => {
    openModal('confirm', {
      title: 'Удаление финансового управляющего',
      description: `Вы уверены, что хотите удалить финансового управляющего "${financialManager.fio || financialManager.id}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/financial-managers/${financialManager.id}`, {
          method: 'DELETE',
        })
        notify({ message: 'Финансовый управляющий успешно удален', type: 'success' })
        await refetch()
      },
    })
  }

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">Финансовые управляющие</h2>
          <p className="text-muted-foreground">Управление базой финансовых управляющих</p>
        </div>
        <Button onClick={handleCreateClick}>
          <Plus className="h-4 w-4 mr-2" />
          Добавить финансового управляющего
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Список финансовых управляющих ({total})</CardTitle>
          <CardDescription>Все финансовые управляющие в базе данных</CardDescription>
        </CardHeader>
        <CardContent>
          {/* Поиск */}
          <div className="mb-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Поиск по ФИО, ИНН, СНИЛС, email"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>

          {isLoading ? (
            <div className="text-center py-12 text-muted-foreground">Загрузка...</div>
          ) : (
            <>
              <div className="relative max-h-[58vh] overflow-auto">
                <Table>
                  <TableHeader className="sticky top-0 z-10 bg-card">
                    <TableRow className="bg-card">
                      <TableHead className="sticky top-0 bg-card">ФИО</TableHead>
                      <TableHead className="sticky top-0 bg-card">ИНН</TableHead>
                      <TableHead className="sticky top-0 bg-card">СНИЛС</TableHead>
                      <TableHead className="sticky top-0 bg-card">Email</TableHead>
                      <TableHead className="sticky top-0 bg-card">Телефон</TableHead>
                      <TableHead className="sticky top-0 w-28 bg-card">Действия</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {financialManagers.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
                          {debouncedSearch ? 'Финансовые управляющие не найдены' : 'Нет финансовых управляющих. Добавьте первого!'}
                        </TableCell>
                      </TableRow>
                    ) : (
                      financialManagers.map((financialManager) => (
                        <TableRow key={financialManager.id}>
                          <TableCell className="font-medium">{financialManager.fio || '-'}</TableCell>
                          <TableCell className="text-sm text-muted-foreground">{financialManager.inn || '-'}</TableCell>
                          <TableCell className="text-sm text-muted-foreground">{financialManager.snils || '-'}</TableCell>
                          <TableCell className="text-sm text-muted-foreground">{financialManager.email || '-'}</TableCell>
                          <TableCell className="text-sm text-muted-foreground">{financialManager.phone || '-'}</TableCell>
                          <TableCell>
                            <div className="flex">
                              <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => handleEditClick(financialManager)}
                                title="Редактировать"
                              >
                                <Edit className="h-4 w-4" />
                              </Button>
                              <Button
                                variant="ghost"
                                size="icon"
                                className="text-red-400"
                                onClick={() => handleDeleteClick(financialManager)}
                                title="Удалить"
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
