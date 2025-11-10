import { useState, useEffect, useCallback } from 'react'
import { apiRequest } from '../../config/api'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { notify } from '../ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search, X } from 'lucide-react'
import Loading from '../shared/Loading'
import { useModalStore } from '../Modals/ModalProvider'
import { CREDITOR_TYPES } from './constants'

export default function CreditorsDatabase() {
  const [creditors, setCreditors] = useState([])
  const [loading, setLoading] = useState(true)
  const { openModal } = useModalStore()

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

  const fetchCreditors = useCallback(async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })

      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }

      const data = await apiRequest(`/creditors?${params.toString()}`)

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
      notify({ message: 'Не удалось загрузить список кредиторов', type: 'error' })
      setCreditors([])
      setTotal(0)
      setPages(1)
    } finally {
      setLoading(false)
    }
  }, [page, debouncedSearch, limit])

  useEffect(() => {
    fetchCreditors()
  }, [fetchCreditors])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const handleCreateClick = () => {
    openModal('creditorForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchCreditors()
      },
      onError: (message: string) => {
        notify({ message, type: 'error' })
      },
    })
  }

  const handleEditClick = (creditor) => {
    openModal('creditorForm', {
      creditor,
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchCreditors()
      },
      onError: (message: string) => {
        notify({ message, type: 'error' })
      },
    })
  }

  const handleDeleteClick = (creditor) => {
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
        await fetchCreditors()
      },
    })
  }

  const getTypeLabel = (type) => {
    const found = CREDITOR_TYPES.find((t) => t.value === type)
    return found ? found.label : type || '-'
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
