import { useState } from 'react'
import { useApp } from '../../context/AppContext'
import { Button } from '../ui/button'
import { Input } from '../ui/input'
import { Label } from '../ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table'
import { Plus, Edit, Trash2, X } from 'lucide-react'

export default function BailiffsDatabase() {
  const { databases, addToDatabase } = useApp()
  const [showForm, setShowForm] = useState(false)
  const [formData, setFormData] = useState({
    department: '',
    address: '',
    phone: ''
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    addToDatabase('bailiffs', formData)
    setFormData({ department: '', address: '', phone: '' })
    setShowForm(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">Судебные приставы</h2>
          <p className="text-muted-foreground">Управление базой ФССП</p>
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
              Добавить отделение
            </>
          )}
        </Button>
      </div>

      {showForm && (
        <Card>
          <CardHeader>
            <CardTitle>Новое отделение ФССП</CardTitle>
            <CardDescription>Добавьте новое отделение в базу данных</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="department">Наименование отделения *</Label>
                  <Input
                    id="department"
                    value={formData.department}
                    onChange={(e) => setFormData({ ...formData, department: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="address">Адрес</Label>
                  <Input
                    id="address"
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="phone">Телефон</Label>
                  <Input
                    id="phone"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  />
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
              <TableHead>Отделение</TableHead>
              <TableHead>Адрес</TableHead>
              <TableHead>Телефон</TableHead>
              <TableHead className="w-20">Действия</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {databases.bailiffs?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={5} className="text-center py-12 text-muted-foreground">
                  Нет записей
                </TableCell>
              </TableRow>
            ) : (
              databases.bailiffs?.map((bailiff, index) => (
                <TableRow key={bailiff.id}>
                  <TableCell>{index + 1}</TableCell>
                  <TableCell className="font-medium">{bailiff.department}</TableCell>
                  <TableCell className="text-sm text-muted-foreground">{bailiff.address}</TableCell>
                  <TableCell className="text-sm">{bailiff.phone}</TableCell>
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


