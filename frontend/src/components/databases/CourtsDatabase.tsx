import { useState, useEffect, useCallback } from 'react'
import { apiRequest } from '../../config/api'
import { Button } from '../ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Input } from '../ui/input'
import { notify } from '../ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import Loading from '../shared/Loading'
import { useModalStore } from '../Modals/ModalProvider'

interface Court {
  id: number
  name: string
  address: string
  [key: string]: unknown
}

export default function CourtsDatabase() {
  const [courts, setCourts] = useState<Court[]>([])
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

  // Загрузка данных при изменении страницы или поиска
  const fetchCourts = useCallback(async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })

      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }

      const data = await apiRequest(`/courts?${params.toString()}`)

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
      notify({ message: 'Не удалось загрузить список судов', type: 'error' })
      setCourts([])
      setTotal(0)
      setPages(1)
    } finally {
      setLoading(false)
    }
  }, [page, debouncedSearch, limit])

  useEffect(() => {
    fetchCourts()
  }, [fetchCourts])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const handleCreateClick = () => {
    openModal('courtForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchCourts()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleEditClick = (court: Court) => {
    openModal('courtForm', {
      court,
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchCourts()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleDeleteClick = (court: Court) => {
    openModal('confirm', {
      title: 'Удаление суда',
      description: `Вы уверены, что хотите удалить суд "${court.name}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/courts/${court.id}`, {
          method: 'DELETE',
        })
        notify({ message: 'Суд успешно удален', type: 'success' })
        await fetchCourts()
      },
    })
  }

  return (
    <div className="space-y-6 p-6">
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
                          <div className="flex">
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
