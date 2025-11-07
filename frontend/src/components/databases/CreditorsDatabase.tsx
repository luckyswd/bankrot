import { useState } from 'react'
import { useApp } from '../../context/AppContext'
import { Button } from '../ui/button'
import { Input } from '../ui/input'
import { Label } from '../ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../ui/select'
import { Plus, Edit, Trash2, X } from 'lucide-react'

export default function CreditorsDatabase() {
  const { databases, addToDatabase } = useApp()
  const [showForm, setShowForm] = useState(false)
  const [formData, setFormData] = useState({
    name: '',
    inn: '',
    ogrn: '',
    address: '',
    type: 'bank'
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    addToDatabase('creditors', formData)
    setFormData({ name: '', inn: '', ogrn: '', address: '', type: 'bank' })
    setShowForm(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">Кредиторы</h2>
          <p className="text-muted-foreground">Управление базой кредиторов</p>
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
              Добавить кредитора
            </>
          )}
        </Button>
      </div>

      {showForm && (
        <Card>
          <CardHeader>
            <CardTitle>Новый кредитор</CardTitle>
            <CardDescription>Добавьте нового кредитора в базу данных</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="name">Наименование *</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="inn">ИНН *</Label>
                  <Input
                    id="inn"
                    value={formData.inn}
                    onChange={(e) => setFormData({ ...formData, inn: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="ogrn">ОГРН *</Label>
                  <Input
                    id="ogrn"
                    value={formData.ogrn}
                    onChange={(e) => setFormData({ ...formData, ogrn: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="address">Адрес</Label>
                  <Input
                    id="address"
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="type">Тип</Label>
                  <Select
                    value={formData.type}
                    onValueChange={(value) => setFormData({ ...formData, type: value })}
                  >
                    <SelectTrigger id="type">
                      <SelectValue placeholder="Выберите тип" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="bank">Банк</SelectItem>
                      <SelectItem value="tax">Налоговая</SelectItem>
                      <SelectItem value="other">Другое</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
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
              <TableHead>Наименование</TableHead>
              <TableHead>ИНН</TableHead>
              <TableHead>ОГРН</TableHead>
              <TableHead>Тип</TableHead>
              <TableHead>Адрес</TableHead>
              <TableHead className="w-20">Действия</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {databases.creditors?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center py-12 text-muted-foreground">
                  Нет записей
                </TableCell>
              </TableRow>
            ) : (
              databases.creditors?.map((creditor, index) => (
                <TableRow key={creditor.id}>
                  <TableCell>{index + 1}</TableCell>
                  <TableCell className="font-medium">{creditor.name}</TableCell>
                  <TableCell className="font-mono text-sm">{creditor.inn}</TableCell>
                  <TableCell className="font-mono text-sm">{creditor.ogrn}</TableCell>
                  <TableCell>
                    <span className="capitalize">{creditor.type === 'bank' ? 'Банк' : creditor.type === 'tax' ? 'Налоговая' : 'Другое'}</span>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">{creditor.address}</TableCell>
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

