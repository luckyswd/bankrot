import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { apiRequest } from '../config/api'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Badge } from './ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from './ui/dialog'
import { Toast } from './ui/toast'
import { Plus, Edit, Trash2, ChevronLeft, ChevronRight, Search } from 'lucide-react'
import Loading from './Loading'

function Dashboard() {
  const navigate = useNavigate()
  const [contracts, setContracts] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchQuery, setSearchQuery] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [filterStatus, setFilterStatus] = useState('all')
  const [page, setPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [pages, setPages] = useState(1)
  const [counts, setCounts] = useState({ all: 0, my: 0, in_progress: 0, completed: 0 })
  const [toast, setToast] = useState<{ message: string; type: 'error' | 'success' | 'info' } | null>(null)
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [contractToDelete, setContractToDelete] = useState(null)
  const limit = 20

  // Дебаунс для поиска (200ms, только от 3 символов)
  useEffect(() => {
    const timer = setTimeout(() => {
      if (searchQuery.length >= 3 || searchQuery.length === 0) {
        setDebouncedSearch(searchQuery)
      }
    }, 200)

    return () => clearTimeout(timer)
  }, [searchQuery])

  // Загрузка данных при изменении страницы, поиска или фильтра
  useEffect(() => {
    const loadContracts = async () => {
      try {
        setLoading(true)
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
          filter: filterStatus,
        })

        // Поиск только если 3 и более символов
        if (debouncedSearch && debouncedSearch.length >= 3) {
          params.append('search', debouncedSearch.trim())
        }

        const data = await apiRequest(`/api/v1/contracts?${params.toString()}`)

        if (data && typeof data === 'object') {
          setContracts(data.data || [])
          setTotal(data.pagination?.total || 0)
          setPages(data.pagination?.pages || 1)
          setCounts(data.counts || { all: 0, my: 0, in_progress: 0, completed: 0 })
        } else {
          setContracts([])
          setTotal(0)
          setPages(1)
        }
      } catch (error) {
        console.error('Ошибка при загрузке контрактов:', error)
        setToast({ message: 'Не удалось загрузить список контрактов', type: 'error' })
        setContracts([])
        setTotal(0)
        setPages(1)
      } finally {
        setLoading(false)
      }
    }

    loadContracts()
  }, [page, debouncedSearch, filterStatus, limit])

  // Сброс на первую страницу при изменении поиска или фильтра
  useEffect(() => {
    setPage(1)
  }, [debouncedSearch, filterStatus])

  const handleRowClick = (contractId: number) => {
    navigate(`/contract/${contractId}`)
  }

  const handleEdit = (contractId: number, e: React.MouseEvent) => {
    e.stopPropagation()
    navigate(`/contract/${contractId}`)
  }

  const handleDeleteClick = (contract: any, e: React.MouseEvent) => {
    e.stopPropagation()
    setContractToDelete(contract)
    setDeleteDialogOpen(true)
  }

  const handleDeleteConfirm = async () => {
    if (!contractToDelete) return

    try {
      await apiRequest(`/api/v1/contracts/${contractToDelete.id}`, {
        method: 'DELETE',
      })

      setToast({ message: 'Контракт успешно удален', type: 'success' })
      setDeleteDialogOpen(false)
      setContractToDelete(null)

      // Перезагружаем список контрактов
      const params = new URLSearchParams({
        page: page.toString(),
        limit: limit.toString(),
        filter: filterStatus,
      })
      if (debouncedSearch && debouncedSearch.length >= 3) {
        params.append('search', debouncedSearch.trim())
      }
      const data = await apiRequest(`/api/v1/contracts?${params.toString()}`)
      if (data && typeof data === 'object') {
        setContracts(data.data || [])
        setTotal(data.pagination?.total || 0)
        setPages(data.pagination?.pages || 1)
        setCounts(data.counts || { all: 0, my: 0, in_progress: 0, completed: 0 })
      }
    } catch (error) {
      console.error('Ошибка при удалении контракта:', error)
      setToast({ message: 'Не удалось удалить контракт', type: 'error' })
      setDeleteDialogOpen(false)
      setContractToDelete(null)
    }
  }

  const handleCreateContract = () => {
    navigate('/contract/new')
  }

  // Генерация номеров страниц для пагинации (как в DocumentsPage, но с первой и последней)
  const getPageNumbers = () => {
    const pageNumbers: (number | string)[] = []
    
    if (pages <= 7) {
      // Если страниц 7 или меньше, показываем все
      for (let i = 1; i <= pages; i++) {
        pageNumbers.push(i)
      }
    } else {
      // Всегда показываем первую страницу
      pageNumbers.push(1)
      
      if (page > 3) {
        pageNumbers.push('...')
      }
      
      // Показываем страницы вокруг текущей
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
      
      // Всегда показываем последнюю страницу
      if (pages > 1) {
        pageNumbers.push(pages)
      }
    }
    
    return pageNumbers
  }

  return (
    <div className="space-y-6">
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
            <DialogTitle>Удаление контракта</DialogTitle>
            <DialogDescription>
              Вы уверены, что хотите удалить контракт "{contractToDelete?.contractNumber}"? Это действие нельзя отменить.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setDeleteDialogOpen(false)
                setContractToDelete(null)
              }}
            >
              Отмена
            </Button>
            <Button
              variant="destructive"
              onClick={handleDeleteConfirm}
            >
              Удалить
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Controls */}
      <Card>
        <CardHeader>
          <CardTitle>Список контрактов ({total})</CardTitle>
          <CardDescription>
            Все договоры для управления
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="mb-4 space-y-4">
            <div className="flex gap-4 items-center">
              {/* Search */}
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Поиск по ФИО или номеру договора"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>

              {/* Filter Buttons */}
              <div className="flex gap-2">
                <Button
                  variant={filterStatus === 'all' ? 'default' : 'outline'}
                  onClick={() => setFilterStatus('all')}
                  className="h-10"
                >
                  Все ({counts.all || 0})
                </Button>
                <Button
                  variant={filterStatus === 'my' ? 'default' : 'outline'}
                  onClick={() => setFilterStatus('my')}
                  className="h-10"
                >
                  Мои ({counts.my || 0})
                </Button>
                <Button
                  variant={filterStatus === 'in_progress' ? 'default' : 'outline'}
                  onClick={() => setFilterStatus('in_progress')}
                  className="h-10"
                >
                  В работе ({counts.in_progress || 0})
                </Button>
                <Button
                  variant={filterStatus === 'completed' ? 'default' : 'outline'}
                  onClick={() => setFilterStatus('completed')}
                  className="h-10"
                >
                  Завершено ({counts.completed || 0})
                </Button>
              </div>

              {/* Create Button */}
              <Button onClick={handleCreateContract} className="h-10">
                <Plus className="h-4 w-4 mr-2" />
                Новый договор
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="py-8">
              <Loading text="Загрузка контрактов..." />
            </div>
          ) : (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">№</TableHead>
                    <TableHead>Номер договора</TableHead>
                    <TableHead>ФИО клиента</TableHead>
                    <TableHead>Дата договора</TableHead>
                    <TableHead>Управляющий</TableHead>
                    <TableHead>Создал</TableHead>
                    <TableHead>Статус</TableHead>
                    <TableHead className="w-28">Действия</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {contracts.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center py-12 text-muted-foreground">
                        {(debouncedSearch && debouncedSearch.length >= 3) || filterStatus !== 'all'
                          ? 'Контракты не найдены'
                          : 'Нет контрактов'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    contracts.map((contract, index) => (
                      <TableRow
                        key={contract.id}
                        onClick={() => handleRowClick(contract.id)}
                        className="cursor-pointer"
                      >
                        <TableCell className="font-medium">{(page - 1) * limit + index + 1}</TableCell>
                        <TableCell>
                          <span className="font-mono text-sm">{contract.contractNumber || '-'}</span>
                        </TableCell>
                        <TableCell className="font-medium">
                          {contract.fullName || '-'}
                        </TableCell>
                        <TableCell>
                          {contract.contractDate ? new Date(contract.contractDate).toLocaleDateString('ru-RU') : '-'}
                        </TableCell>
                        <TableCell>{contract.manager || '-'}</TableCell>
                        <TableCell>{contract.author || '-'}</TableCell>
                        <TableCell>
                          <Badge variant={contract.status === 'В работе' ? 'blue' : contract.status === 'Завершено' ? 'green' : 'secondary'}>
                            {contract.status || '-'}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="flex gap-1" onClick={(e) => e.stopPropagation()}>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={(e) => handleEdit(contract.id, e)}
                              title="Редактировать"
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={(e) => handleDeleteClick(contract, e)}
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
                      {getPageNumbers().map((pageNum, idx) => {
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
                      })}
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

export default Dashboard
