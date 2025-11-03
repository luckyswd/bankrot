import { useState, useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Card } from './ui/card'
import { Badge } from './ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table'
import { Plus, Search } from 'lucide-react'

function Dashboard() {
  const { contracts, createContract, currentUser } = useApp()
  const navigate = useNavigate()
  const [searchQuery, setSearchQuery] = useState('')
  const [filterStatus, setFilterStatus] = useState('all')

  const filteredContracts = useMemo(() => {
    return contracts.filter(contract => {
      if (filterStatus === 'active' && contract.status !== 'active') return false
      if (filterStatus === 'completed' && contract.status !== 'completed') return false

      if (searchQuery) {
        const query = searchQuery.toLowerCase()
        const fullName = `${contract.clientData.lastName} ${contract.clientData.firstName} ${contract.clientData.middleName}`.toLowerCase()
        const contractNum = contract.contractNumber.toLowerCase()
        
        return fullName.includes(query) || contractNum.includes(query)
      }

      return true
    })
  }, [contracts, searchQuery, filterStatus])

  const handleRowClick = (contractId) => {
    navigate(`/client/${contractId}`)
  }

  const handleCreateContract = () => {
    const newContract = createContract({
      contractNumber: `2024/${String(contracts.length + 1).padStart(3, '0')}`,
      clientData: {
        lastName: '',
        firstName: '',
        middleName: '',
        gender: 'male',
        maritalStatus: 'single'
      },
      contractDate: new Date().toISOString().split('T')[0],
      caseManager: currentUser.fullName,
      preTrialData: {},
      trialData: {},
      creditors: [],
      executionProceedings: []
    })
    navigate(`/client/${newContract.id}`)
  }

  return (
    <div className="space-y-6">
      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card className="p-6">
          <div className="text-2xl font-bold">{contracts.length}</div>
          <div className="text-sm text-muted-foreground">Всего договоров</div>
        </Card>
        <Card className="p-6">
          <div className="text-2xl font-bold text-blue-500">
            {contracts.filter(c => c.status === 'active').length}
          </div>
          <div className="text-sm text-muted-foreground">Дела в работе</div>
        </Card>
        <Card className="p-6">
          <div className="text-2xl font-bold text-green-500">
            {contracts.filter(c => c.status === 'completed').length}
          </div>
          <div className="text-sm text-muted-foreground">Завершено</div>
        </Card>
      </div>

      {/* Controls */}
      <Card className="p-6">
        <div className="flex flex-col md:flex-row gap-4">
          {/* Search */}
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              type="text"
              placeholder="Поиск по ФИО или номеру дела..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>

          {/* Filter Buttons */}
          <div className="flex gap-2">
            <Button
              variant={filterStatus === 'all' ? 'default' : 'outline'}
              onClick={() => setFilterStatus('all')}
              size="sm"
            >
              Все ({contracts.length})
            </Button>
            <Button
              variant={filterStatus === 'active' ? 'default' : 'outline'}
              onClick={() => setFilterStatus('active')}
              size="sm"
            >
              В работе ({contracts.filter(c => c.status === 'active').length})
            </Button>
            <Button
              variant={filterStatus === 'completed' ? 'default' : 'outline'}
              onClick={() => setFilterStatus('completed')}
              size="sm"
            >
              Завершено ({contracts.filter(c => c.status === 'completed').length})
            </Button>
          </div>

          {/* Create Button */}
          <Button onClick={handleCreateContract}>
            <Plus className="h-4 w-4 mr-2" />
            Новый договор
          </Button>
        </div>
      </Card>

      {/* Table */}
      <Card>
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
            </TableRow>
          </TableHeader>
          <TableBody>
            {filteredContracts.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center py-12 text-muted-foreground">
                  {searchQuery ? 'Ничего не найдено' : 'Нет договоров'}
                </TableCell>
              </TableRow>
            ) : (
              filteredContracts.map((contract, index) => (
                <TableRow
                  key={contract.id}
                  onClick={() => handleRowClick(contract.id)}
                  className="cursor-pointer"
                >
                  <TableCell className="font-medium">{index + 1}</TableCell>
                  <TableCell>
                    <span className="font-mono text-sm">{contract.contractNumber}</span>
                  </TableCell>
                  <TableCell className="font-medium">
                    {contract.clientData.lastName} {contract.clientData.firstName} {contract.clientData.middleName}
                  </TableCell>
                  <TableCell>
                    {new Date(contract.contractDate).toLocaleDateString('ru-RU')}
                  </TableCell>
                  <TableCell>{contract.caseManager}</TableCell>
                  <TableCell>{contract.createdBy}</TableCell>
                  <TableCell>
                    <Badge variant={contract.status === 'active' ? 'default' : 'success'}>
                      {contract.status === 'active' ? 'В работе' : 'Завершено'}
                    </Badge>
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

export default Dashboard
