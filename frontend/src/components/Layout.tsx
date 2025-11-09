import { useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import { Button } from './ui/button'
import { Separator } from './ui/separator'
import { 
  FileText, 
  Users, 
  Scale, 
  Building2, 
  Shield, 
  Flame,
  UserCircle,
  LogOut,
  Menu,
  X,
  Database,
  ChevronDown,
  ChevronRight,
  Sun,
  Moon,
  FileBarChart
} from 'lucide-react'

export default function Layout({ children }) {
  const location = useLocation()
  const navigate = useNavigate()
  const { user, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [databasesOpen, setDatabasesOpen] = useState(false)

  const databaseItems = [
    { icon: Users, label: 'Кредиторы', path: '/databases/creditors' },
    { icon: Scale, label: 'Арбитражные суды', path: '/databases/courts' },
    { icon: Shield, label: 'Судебные приставы', path: '/databases/bailiffs' },
    { icon: Building2, label: 'ФНС', path: '/databases/fns' },
    { icon: Flame, label: 'ГИМС МЧС', path: '/databases/mchs' },
    { icon: Shield, label: 'Росгвардия', path: '/databases/rosgvardia' },
  ]

  const handleLogout = () => {
    if (window.confirm('Вы уверены, что хотите выйти?')) {
      logout()
      navigate('/login')
    }
  }

  return (
    <div className="min-h-screen bg-background text-foreground">
      <div className="flex h-screen overflow-hidden">
        {/* Sidebar */}
        <aside 
          className={`${
            sidebarOpen ? 'w-64' : 'w-0'
          } transition-all duration-300 border-r border-border bg-card flex flex-col overflow-hidden`}
        >

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {/* Договоры */}
            <Link
              to="/contracts"
              className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                location.pathname === '/contracts' || location.pathname === '/'
                  ? 'bg-primary text-primary-foreground'
                  : 'hover:bg-accent hover:text-accent-foreground'
              }`}
            >
              <FileText className="h-5 w-5" />
              <span className="text-sm font-medium">Договоры</span>
            </Link>

            {user?.roles?.includes('ROLE_ADMIN') && (
              <>
                <Link
                  to="/documents"
                  className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                    location.pathname === '/documents'
                      ? 'bg-primary text-primary-foreground'
                      : 'hover:bg-accent hover:text-accent-foreground'
                  }`}
                >
                  <FileBarChart className="h-5 w-5" />
                  <span className="text-sm font-medium">Шаблоны документов</span>
                </Link>

                <Separator className="my-2" />
              </>
            )}

            {user?.roles?.includes('ROLE_ADMIN') && (
              <div>
                <button
                  onClick={() => setDatabasesOpen(!databasesOpen)}
                  className="w-full flex items-center justify-between gap-2 px-3 py-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider hover:text-foreground transition-colors"
                >
                  <div className="flex items-center gap-2">
                    <Database className="h-4 w-4" />
                    <span>Справочники</span>
                  </div>
                  {databasesOpen ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                </button>

                {databasesOpen && (
                  <div className="mt-1 space-y-1 pl-2">
                    {databaseItems.map((item, index) => {
                      const isActive = location.pathname === item.path
                      const Icon = item.icon

                      return (
                        <Link
                          key={index}
                          to={item.path}
                          className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                            isActive
                              ? 'bg-primary text-primary-foreground'
                              : 'hover:bg-accent hover:text-accent-foreground'
                          }`}
                        >
                          <Icon className="h-4 w-4" />
                          <span className="text-sm font-medium">{item.label}</span>
                        </Link>
                      )
                    })}
                  </div>
                )}
              </div>
            )}
          </nav>

          <Separator />

          {/* User Info */}
          <div className="p-4">
            <div className="flex items-center gap-3 p-3 rounded-md bg-accent/50">
              <UserCircle className="h-8 w-8 text-primary" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium truncate">{user?.username}</p>
                <p className="text-xs text-muted-foreground truncate">
                  {user?.roles?.includes('ROLE_ADMIN') ? 'Администратор' : 
                   user?.roles?.includes('ROLE_MANAGER') ? 'Менеджер' : 'Пользователь'}
                </p>
              </div>
            </div>
            <Button 
              variant="ghost" 
              className="w-full mt-2 justify-start" 
              onClick={handleLogout}
            >
              <LogOut className="h-4 w-4 mr-2" />
              Выйти
            </Button>
          </div>
        </aside>

        {/* Main Content */}
        <div className="flex-1 flex flex-col overflow-hidden">
          {/* Header */}
          <header className="border-b border-border bg-card px-6 py-4 flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => setSidebarOpen(!sidebarOpen)}
            >
              {sidebarOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </Button>
            
            <div className="flex-1">
              <h1 className="text-xl font-semibold">
                {location.pathname === '/contracts' || location.pathname === '/' ? 'Договоры' : 
                 location.pathname === '/documents' ? 'Документы' :
                 location.pathname.includes('/databases/creditors') ? 'Кредиторы' :
                 location.pathname.includes('/databases/courts') ? 'Арбитражные суды' :
                 location.pathname.includes('/databases/bailiffs') ? 'Судебные приставы' :
                 location.pathname.includes('/databases/fns') ? 'ФНС' :
                 location.pathname.includes('/databases/mchs') ? 'ГИМС МЧС' :
                 location.pathname.includes('/databases/rosgvardia') ? 'Росгвардия' :
                 location.pathname.includes('/client/') ? 'Карточка договора' :
                 ''}
              </h1>
            </div>

            {/* Theme Toggle */}
            <Button
              variant="ghost"
              size="icon"
              onClick={toggleTheme}
              title={theme === 'dark' ? 'Светлая тема' : 'Темная тема'}
            >
              {theme === 'dark' ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
            </Button>
          </header>

          {/* Content */}
          <main className="flex-1 overflow-y-auto p-6">
            {children}
          </main>
        </div>
      </div>
    </div>
  )
}
