import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Badge } from './ui/badge'
import { Separator } from './ui/separator'
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs'
import { ArrowLeft, FileText, Save, Plus, Trash2 } from 'lucide-react'

function ClientCard() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { contracts, updateContract, databases } = useApp()
  
  const contract = contracts.find(c => c.id === parseInt(id))
  const [formData, setFormData] = useState(contract || {})
  const [saveStatus, setSaveStatus] = useState('')

  // Автосохранение
  useEffect(() => {
    if (!contract) return

    const timer = setTimeout(() => {
      if (JSON.stringify(formData) !== JSON.stringify(contract)) {
        setSaveStatus('saving')
        updateContract(contract.id, formData)
        setTimeout(() => {
          setSaveStatus('saved')
          setTimeout(() => setSaveStatus(''), 2000)
        }, 500)
      }
    }, 1000)

    return () => clearTimeout(timer)
  }, [formData, contract, updateContract])

  if (!contract) {
    return (
      <div className="flex items-center justify-center h-full">
        <Card className="p-8 text-center">
          <p className="text-lg mb-4">Договор не найден</p>
          <Button onClick={() => navigate('/contracts')}>
            <ArrowLeft className="h-4 w-4 mr-2" />
            Вернуться к списку
          </Button>
        </Card>
      </div>
    )
  }

  const handleChange = (path, value) => {
    const keys = path.split('.')
    setFormData(prev => {
      const newData = { ...prev }
      let current = newData
      
      for (let i = 0; i < keys.length - 1; i++) {
        if (!current[keys[i]]) current[keys[i]] = {}
        current = current[keys[i]]
      }
      
      current[keys[keys.length - 1]] = value
      return newData
    })
  }

  const getValue = (path) => {
    const keys = path.split('.')
    let current = formData
    
    for (const key of keys) {
      if (!current || current[key] === undefined) return ''
      current = current[key]
    }
    
    return current
  }

  const openDocument = (docType) => {
    navigate(`/document/${contract.id}/${docType}`)
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="outline" onClick={() => navigate('/contracts')}>
            <ArrowLeft className="h-4 w-4 mr-2" />
            Назад
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Договор № {contract.contractNumber}</h1>
            <p className="text-sm text-muted-foreground">
              {getValue('primaryInfo.lastName')} {getValue('primaryInfo.firstName')} {getValue('primaryInfo.middleName')}
            </p>
          </div>
        </div>
        <div className="flex items-center gap-3">
          {saveStatus && (
            <div className={`flex items-center gap-2 px-3 py-2 rounded-md text-sm ${
              saveStatus === 'saving' ? 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-500' : 'bg-green-500/10 text-green-600 dark:text-green-500'
            }`}>
              <Save className="h-4 w-4" />
              {saveStatus === 'saving' ? 'Сохранение...' : 'Сохранено'}
            </div>
          )}
          <Badge variant={contract.status === 'active' ? 'default' : 'success'}>
            {contract.status === 'active' ? 'В работе' : 'Завершено'}
          </Badge>
        </div>
      </div>

      {/* Tabs for stages */}
      <Tabs defaultValue="primary" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="primary">Основная информация</TabsTrigger>
          <TabsTrigger value="pretrial">Досудебка</TabsTrigger>
          <TabsTrigger value="introduction">Введение процедуры</TabsTrigger>
          <TabsTrigger value="procedure">Процедура</TabsTrigger>
        </TabsList>

        {/* Основная информация */}
        <TabsContent value="primary" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Основная информация</CardTitle>
              <CardDescription>Заполните все необходимые поля</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label>1. ФИО *</Label>
                  <Input
                    placeholder="Фамилия Имя Отчество"
                    value={getValue('primaryInfo.fullName')}
                    onChange={(e) => handleChange('primaryInfo.fullName', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>2. Изменялось ли ФИО</Label>
                  <select
                    value={getValue('primaryInfo.nameChanged')}
                    onChange={(e) => handleChange('primaryInfo.nameChanged', e.target.value)}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">Выбрать</option>
                    <option value="yes">Да</option>
                    <option value="no">Нет</option>
                  </select>
                </div>

                <div className="space-y-2">
                  <Label>3. Дата рождения *</Label>
                  <Input
                    type="date"
                    value={getValue('primaryInfo.birthDate')}
                    onChange={(e) => handleChange('primaryInfo.birthDate', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>4. Место рождения</Label>
                  <Input
                    placeholder="Город, страна"
                    value={getValue('primaryInfo.birthPlace')}
                    onChange={(e) => handleChange('primaryInfo.birthPlace', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>5. СНИЛС</Label>
                  <Input
                    placeholder="123-456-789 00"
                    value={getValue('primaryInfo.snils')}
                    onChange={(e) => handleChange('primaryInfo.snils', e.target.value)}
                  />
                </div>

                <div className="space-y-2 lg:col-span-3">
                  <Label>6. Адрес регистрации</Label>
                  <Input
                    placeholder="Субъект РФ, район, город, населенный пункт, улица, дом, корпус, квартира"
                    value={getValue('primaryInfo.registrationAddress')}
                    onChange={(e) => handleChange('primaryInfo.registrationAddress', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>7. Паспорт (серия и номер)</Label>
                  <Input
                    placeholder="1234 567890"
                    value={getValue('primaryInfo.passport')}
                    onChange={(e) => handleChange('primaryInfo.passport', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>8. Состоит в браке</Label>
                  <select
                    value={getValue('primaryInfo.married')}
                    onChange={(e) => handleChange('primaryInfo.married', e.target.value)}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">Выбрать</option>
                    <option value="yes">Да (нет, но состоял в течение 3 лет)</option>
                    <option value="no">Нет (состоял в течении 3 лет)</option>
                  </select>
                </div>

                <div className="space-y-2">
                  <Label>9. Супруг(а) (опционально)</Label>
                  <Input
                    placeholder="ФИО супруга/супруги"
                    value={getValue('primaryInfo.spouseName')}
                    onChange={(e) => handleChange('primaryInfo.spouseName', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>10. Несовершеннолетние дети</Label>
                  <select
                    value={getValue('primaryInfo.hasMinorChildren')}
                    onChange={(e) => handleChange('primaryInfo.hasMinorChildren', e.target.value)}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">Выбрать</option>
                    <option value="yes">Да</option>
                    <option value="no">Нет</option>
                  </select>
                </div>

                <div className="space-y-2">
                  <Label>11. ФИО (ребёнка)</Label>
                  <Input
                    placeholder="ФИО ребенка"
                    value={getValue('primaryInfo.childName')}
                    onChange={(e) => handleChange('primaryInfo.childName', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>12. Студент</Label>
                  <select
                    value={getValue('primaryInfo.isStudent')}
                    onChange={(e) => handleChange('primaryInfo.isStudent', e.target.value)}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">Выбрать</option>
                    <option value="yes">Да</option>
                    <option value="no">Нет</option>
                  </select>
                </div>

                <div className="space-y-2 lg:col-span-3">
                  <Label>13. Работа</Label>
                  <Input
                    placeholder="Наименование, адрес, ИНН (может если начинает работать более 5 букв, то мы знаем что 'Да' если не заполняет, то нет и в графе ставится 'не работает')"
                    value={getValue('primaryInfo.work')}
                    onChange={(e) => handleChange('primaryInfo.work', e.target.value)}
                  />
                </div>

                <div className="space-y-2 lg:col-span-3">
                  <Label>14. Пенсии и соц.выплаты</Label>
                  <Input
                    placeholder="Алименты, пособие, ЕДВ, прочее. Уточнить, выбор ли или в свободная форма?"
                    value={getValue('primaryInfo.socialPayments')}
                    onChange={(e) => handleChange('primaryInfo.socialPayments', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>15. Телефон</Label>
                  <Input
                    type="tel"
                    placeholder="+7 (999) 123-45-67"
                    value={getValue('primaryInfo.phone')}
                    onChange={(e) => handleChange('primaryInfo.phone', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>16. Электронная почта</Label>
                  <Input
                    type="email"
                    placeholder="email@example.com"
                    value={getValue('primaryInfo.email')}
                    onChange={(e) => handleChange('primaryInfo.email', e.target.value)}
                  />
                </div>

                <div className="space-y-2 lg:col-span-3">
                  <Label>17. Адрес для направления корреспонденции</Label>
                  <Input
                    placeholder="Пример: 196084, г. Санкт-Петербург, ул. Смоленская, 9-418"
                    value={getValue('primaryInfo.correspondenceAddress')}
                    onChange={(e) => handleChange('primaryInfo.correspondenceAddress', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>18. Сумма долга</Label>
                  <Input
                    type="number"
                    placeholder="в руб"
                    value={getValue('primaryInfo.debtAmount')}
                    onChange={(e) => handleChange('primaryInfo.debtAmount', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>19. Исполнительные производства</Label>
                  <select
                    value={getValue('primaryInfo.hasExecutions')}
                    onChange={(e) => handleChange('primaryInfo.hasExecutions', e.target.value)}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">Выбрать</option>
                    <option value="yes">Да</option>
                    <option value="no">Нет</option>
                  </select>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Досудебка */}
        <TabsContent value="pretrial" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Досудебка</CardTitle>
              <CardDescription>Информация о досудебном этапе</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>1. Арбитражный суд</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('pretrial.court')}
                    onChange={(e) => handleChange('pretrial.court', e.target.value)}
                    list="courts-list"
                  />
                  <datalist id="courts-list">
                    {databases.courts?.map(c => (
                      <option key={c.id} value={c.name} />
                    ))}
                  </datalist>
                </div>

                <div className="space-y-2">
                  <Label>2. Кредиторы</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('pretrial.creditors')}
                    onChange={(e) => handleChange('pretrial.creditors', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>3. Доверенность</Label>
                  <div className="grid grid-cols-2 gap-2">
                    <Input
                      type="text"
                      placeholder="Номер"
                      value={getValue('pretrial.powerOfAttorneyNumber')}
                      onChange={(e) => handleChange('pretrial.powerOfAttorneyNumber', e.target.value)}
                    />
                    <Input
                      type="date"
                      value={getValue('pretrial.powerOfAttorneyDate')}
                      onChange={(e) => handleChange('pretrial.powerOfAttorneyDate', e.target.value)}
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>4. Кредитор</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('pretrial.creditor')}
                    onChange={(e) => handleChange('pretrial.creditor', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>5. № Дела</Label>
                  <Input
                    value={getValue('pretrial.caseNumber')}
                    onChange={(e) => handleChange('pretrial.caseNumber', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>6. Дата и время заседания</Label>
                  <div className="grid grid-cols-2 gap-2">
                    <Input
                      type="date"
                      value={getValue('pretrial.hearingDate')}
                      onChange={(e) => handleChange('pretrial.hearingDate', e.target.value)}
                    />
                    <Input
                      type="time"
                      value={getValue('pretrial.hearingTime')}
                      onChange={(e) => handleChange('pretrial.hearingTime', e.target.value)}
                    />
                  </div>
                </div>
              </div>

              <Separator className="my-6" />

              <div className="space-y-3">
                <h4 className="font-semibold">Документы досудебного этапа:</h4>
                <Button
                  onClick={() => openDocument('bankruptcyApplication')}
                  variant="outline"
                  className="w-full justify-start"
                >
                  <FileText className="h-4 w-4 mr-2" />
                  Заявление о признании банкротом
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Введение процедуры */}
        <TabsContent value="introduction" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>1. Введение процедуры</CardTitle>
              <CardDescription>Данные для введения процедуры банкротства</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>1. Дата решения суда</Label>
                  <Input
                    type="date"
                    value={getValue('introduction.courtDecisionDate')}
                    onChange={(e) => handleChange('introduction.courtDecisionDate', e.target.value)}
                  />
                  <p className="text-xs text-muted-foreground">
                    Если ставится дата суда, то информация (резолютивная часть: обЪявлена 20.06.2025 г.) НЕ печатается
                  </p>
                </div>

                <div className="space-y-2">
                  <Label>2. ГИМС</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('introduction.gims')}
                    onChange={(e) => handleChange('introduction.gims', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>3. Гостехнадзор</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('introduction.gostechnadzor')}
                    onChange={(e) => handleChange('introduction.gostechnadzor', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>4. ФНС</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('introduction.fns')}
                    onChange={(e) => handleChange('introduction.fns', e.target.value)}
                    list="fns-list"
                  />
                  <datalist id="fns-list">
                    {databases.fns?.map(f => (
                      <option key={f.id} value={f.name} />
                    ))}
                  </datalist>
                </div>

                <div className="space-y-2">
                  <Label>5. Номер документа</Label>
                  <Input
                    placeholder="Выбор даты"
                    value={getValue('introduction.documentNumber')}
                    onChange={(e) => handleChange('introduction.documentNumber', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>6. Номер дела</Label>
                  <Input
                    value={getValue('introduction.caseNumber')}
                    onChange={(e) => handleChange('introduction.caseNumber', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>7. Росаввиация</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('introduction.rosaviation')}
                    onChange={(e) => handleChange('introduction.rosaviation', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>8. Номер дела</Label>
                  <Input
                    value={getValue('introduction.caseNumber2')}
                    onChange={(e) => handleChange('introduction.caseNumber2', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>9. Судья</Label>
                  <Input
                    value={getValue('introduction.judge')}
                    onChange={(e) => handleChange('introduction.judge', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>10. Судебный пристав</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('introduction.bailiff')}
                    onChange={(e) => handleChange('introduction.bailiff', e.target.value)}
                  />
                </div>

                <div className="space-y-2 md:col-span-2">
                  <Label>12. Окончание исполнительных производств</Label>
                  <div className="flex gap-2">
                    <Input
                      placeholder="Номер"
                      value={getValue('introduction.executionNumber')}
                      onChange={(e) => handleChange('introduction.executionNumber', e.target.value)}
                    />
                    <Input
                      type="date"
                      value={getValue('introduction.executionDate')}
                      onChange={(e) => handleChange('introduction.executionDate', e.target.value)}
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>13. Номер спец счёта</Label>
                  <Input
                    value={getValue('introduction.specialAccountNumber')}
                    onChange={(e) => handleChange('introduction.specialAccountNumber', e.target.value)}
                  />
                </div>
              </div>

              <Separator className="my-6" />

              <div className="space-y-2">
                <h4 className="font-semibold">Документы этапа введения:</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <Button onClick={() => openDocument('efrsbPublication')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Публикация ЕФРСБ
                  </Button>
                  <Button onClick={() => openDocument('kommersantPublication')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Заявка Коммерсантъ
                  </Button>
                  <Button onClick={() => openDocument('spouseNotification')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Уведомление супруга
                  </Button>
                  <Button onClick={() => openDocument('gibddRequest')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Запрос ГИБДД, ГИМС, РОС
                  </Button>
                  <Button onClick={() => openDocument('rosgvardiaRequest')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Запрос Росгвардия
                  </Button>
                  <Button onClick={() => openDocument('efrsbNotification')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Уведомление о публикации
                  </Button>
                  <Button onClick={() => openDocument('fsspApplication')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Заявление в ФССП
                  </Button>
                  <Button onClick={() => openDocument('specialAccountApplication')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Заявка на спецсчет
                  </Button>
                  <Button onClick={() => openDocument('accountingApplication')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Заявление в бухгалтерию
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Процедура */}
        <TabsContent value="procedure" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>2. Процедура</CardTitle>
              <CardDescription>Данные процедуры реализации</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>1. Требование кредитора</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('procedure.creditorRequirement')}
                    onChange={(e) => handleChange('procedure.creditorRequirement', e.target.value)}
                  />
                  <p className="text-xs text-muted-foreground">
                    наименование кредитора ОГРН, ИНН, Адрес (база)
                  </p>
                </div>

                <div className="space-y-2">
                  <Label>2. Полученные требования кредитора</Label>
                  <Input
                    placeholder="Выбор из списка"
                    value={getValue('procedure.receivedRequirements')}
                    onChange={(e) => handleChange('procedure.receivedRequirements', e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label>3. Основная сумма</Label>
                  <Input
                    type="number"
                    value={getValue('procedure.principalAmount')}
                    onChange={(e) => handleChange('procedure.principalAmount', e.target.value)}
                  />
                </div>
              </div>

              <Separator className="my-6" />

              <div className="space-y-2">
                <h4 className="font-semibold">Документы процедуры:</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <Button onClick={() => openDocument('receivedRequirement')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Публикация о получении требования
                  </Button>
                  <Button onClick={() => openDocument('includedRequirement')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Публикация о включении в реестр
                  </Button>
                  <Button onClick={() => openDocument('financialReport')} variant="outline" className="justify-start" size="sm">
                    <FileText className="h-4 w-4 mr-2" />
                    Отчет финансового управляющего
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}

export default ClientCard
