import { useState } from 'react'
import { useApp } from '../../context/AppContext'
import { Button } from '@ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@ui/table'
import { Input } from '@ui/input'
import { Label } from '@ui/label'
import { Plus, Edit, Trash2, X } from 'lucide-react'

// Generic database component for other databases (MCHS, Rosgvardia)
export function GenericDatabase({ dbKey, title, description, fields }) {
  const { databases, addToDatabase } = useApp()
  const [showForm, setShowForm] = useState(false)
  const [formData, setFormData] = useState(
    fields.reduce((acc, field) => ({ ...acc, [field.key]: '' }), {})
  )

  const handleSubmit = (e) => {
    e.preventDefault()
    addToDatabase(dbKey, formData)
    setFormData(fields.reduce((acc, field) => ({ ...acc, [field.key]: '' }), {}))
    setShowForm(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold">{title}</h2>
          <p className="text-muted-foreground">{description}</p>
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
              Добавить запись
            </>
          )}
        </Button>
      </div>

      {showForm && (
        <Card>
          <CardHeader>
            <CardTitle>Новая запись</CardTitle>
            <CardDescription>Добавьте новую запись в базу данных</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 gap-4">
                {fields.map((field) => (
                  <div key={field.key} className="space-y-2">
                    <Label htmlFor={field.key}>
                      {field.label} {field.required && '*'}
                    </Label>
                    <Input
                      id={field.key}
                      value={formData[field.key]}
                      onChange={(e) => setFormData({ ...formData, [field.key]: e.target.value })}
                      required={field.required}
                    />
                  </div>
                ))}
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
              {fields.map((field) => (
                <TableHead key={field.key}>{field.label}</TableHead>
              ))}
              <TableHead className="w-20">Действия</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {databases[dbKey]?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={fields.length + 2} className="text-center py-12 text-muted-foreground">
                  Нет записей
                </TableCell>
              </TableRow>
            ) : (
              databases[dbKey]?.map((item, index) => (
                <TableRow key={item.id}>
                  <TableCell>{index + 1}</TableCell>
                  {fields.map((field) => (
                    <TableCell key={field.key} className={field.key === fields[0].key ? 'font-medium' : 'text-sm text-muted-foreground'}>
                      {item[field.key]}
                    </TableCell>
                  ))}
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