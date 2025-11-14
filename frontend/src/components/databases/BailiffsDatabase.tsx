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

interface Bailiff {
  id: number
  department: string
  address: string
  phone: string
  [key: string]: unknown
}

export default function BailiffsDatabase() {
  const [bailiffs, setBailiffs] = useState<Bailiff[]>([])
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

  const fetchBailiffs = useCallback(async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
      })

      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch)
      }

      const data = await apiRequest(`/bailiffs?${params.toString()}`)

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
      notify({ message: 'Не удалось загрузить список отделений', type: 'error' })
      setBailiffs([])
      setTotal(0)
      setPages(1)
    } finally {
      setLoading(false)
    }
  }, [page, debouncedSearch, limit])

  useEffect(() => {
    fetchBailiffs()
  }, [fetchBailiffs])

  // Сброс на первую страницу при изменении поиска
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch])

  const handleCreateClick = () => {
    openModal('bailiffForm', {
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchBailiffs()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleEditClick = (bailiff: Bailiff) => {
    openModal('bailiffForm', {
      bailiff,
      onSuccess: async (message: string) => {
        notify({ message, type: 'success' })
        await fetchBailiffs()
      },
      onError: (message: string) => notify({ message, type: 'error' }),
    })
  }

  const handleDeleteClick = (bailiff: Bailiff) => {
    openModal('confirm', {
      title: 'Удаление отделения',
      description: `Вы уверены, что хотите удалить отделение "${bailiff.department}"? Это действие нельзя отменить.`,
      confirmLabel: 'Удалить',
      confirmVariant: 'destructive',
      onConfirm: async () => {
        await apiRequest(`/bailiffs/${bailiff.id}`, {
          method: 'DELETE',
        })
        notify({ message: 'Отделение успешно удалено', type: 'success' })
        await fetchBailiffs()
      },
    })
  }

  return (
    <div className="space-y-6 p-6">
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
                          <div className="flex">
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
