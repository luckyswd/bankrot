import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { FileText } from 'lucide-react'

function Login() {
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [apiStatus, setApiStatus] = useState('testing')
  const { login } = useApp()
  const navigate = useNavigate()

  // Тест API при загрузке компонента
  useEffect(() => {
    const testApi = async () => {
      const apiUrl = import.meta.env.VITE_API_URL;
      console.log('🔍 API URL:', apiUrl);
      console.log('🚀 Отправляю запрос на backend...');

      try {
        const response = await fetch(`${apiUrl}/api/test`);
        
        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', Object.fromEntries(response.headers));
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('✅ API работает! Данные получены:', data);
        console.log('📦 Backend:', data.data.backend);
        console.log('🐘 PHP Version:', data.data.php_version);
        
        setApiStatus('success');
      } catch (err) {
        console.error('❌ Ошибка при запросе к API:', err);
        setApiStatus('error');
      }
    };

    testApi();
  }, [])

  const handleSubmit = (e) => {
    e.preventDefault()
    setError('')

    if (!username || !password) {
      setError('Пожалуйста, заполните все поля')
      return
    }

    const success = login(username, password)
    if (success) {
      navigate('/')
    } else {
      setError('Неверный логин или пароль')
    }
  }

  return (
    <div className="dark min-h-screen bg-background flex items-center justify-center p-4">
      <div className="w-full max-w-md space-y-6">
        {/* Logo / Brand */}
        <div className="flex flex-col items-center space-y-2">
          <div className="rounded-full bg-primary/10 p-3">
            <FileText className="h-10 w-10 text-primary" />
          </div>
          <h1 className="text-3xl font-bold">Legal Docs</h1>
          <p className="text-muted-foreground">Система управления документами</p>
        </div>

        {/* Login Form */}
        <Card>
          <CardHeader>
            <CardTitle>Вход в систему</CardTitle>
            <CardDescription>
              Введите свои учетные данные для доступа
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="username">Логин</Label>
                <Input
                  id="username"
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="Введите логин"
                  autoComplete="username"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">Пароль</Label>
                <Input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Введите пароль"
                  autoComplete="current-password"
                />
              </div>

              {error && (
                <div className="p-3 rounded-md bg-destructive/10 border border-destructive/20 text-destructive text-sm">
                  {error}
                </div>
              )}

              <Button type="submit" className="w-full">
                Войти
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* API Status */}
        <Card className={apiStatus === 'success' ? 'bg-green-500/10 border-green-500/20' : apiStatus === 'error' ? 'bg-red-500/10 border-red-500/20' : 'bg-muted/50'}>
          <CardContent className="pt-6">
            <p className="text-sm font-medium mb-2">🔌 Статус API:</p>
            <div className="flex items-center gap-2">
              {apiStatus === 'testing' && <span className="text-yellow-500">⏳ Проверка соединения...</span>}
              {apiStatus === 'success' && <span className="text-green-500">✅ Подключено к {import.meta.env.VITE_API_URL}</span>}
              {apiStatus === 'error' && <span className="text-red-500">❌ Ошибка подключения</span>}
            </div>
            <p className="text-xs text-muted-foreground mt-2">Проверь консоль браузера (F12) для деталей</p>
          </CardContent>
        </Card>

        {/* Test Credentials Hint */}
        <Card className="bg-muted/50">
          <CardContent className="pt-6">
            <p className="text-sm font-medium mb-3">Тестовые данные:</p>
            <div className="space-y-2 text-sm text-muted-foreground">
              <div className="flex justify-between">
                <span>Логин:</span>
                <code className="bg-background px-2 py-1 rounded text-foreground">admin</code>
              </div>
              <div className="flex justify-between">
                <span>Пароль:</span>
                <code className="bg-background px-2 py-1 rounded text-foreground">admin123</code>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

export default Login
