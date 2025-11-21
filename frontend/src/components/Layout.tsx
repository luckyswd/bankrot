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
  Database,
  Sun,
  Moon,
  FileBarChart,
  Wrench
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from './ui/accordion'
import { useModalStore } from './Modals/ModalProvider'

interface LayoutProps {
  children: React.ReactNode
}

export default function Layout({ children }: LayoutProps) {
  const location = useLocation()
  const navigate = useNavigate()
  const { user, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [databasesOpen, setDatabasesOpen] = useState(false)
  const { openModal } = useModalStore()

  const databaseItems = [
    { icon: Users, label: 'Кредиторы', path: '/databases/creditors' },
    { icon: Scale, label: 'Арбитражные суды', path: '/databases/courts' },
    { icon: Shield, label: 'Судебные приставы', path: '/databases/bailiffs' },
    { icon: Building2, label: 'ФНС', path: '/databases/fns' },
    { icon: Flame, label: 'ГИМС МЧС', path: '/databases/mchs' },
    { icon: Shield, label: 'Росгвардия', path: '/databases/rosgvardia' },
    { icon: Wrench, label: 'Гостехнадзор', path: '/databases/gostekhnadzor' },
  ]

  const handleLogout = () => {
    openModal('confirm', {
      title: 'Подтверждение выхода',
      description: 'Вы уверены, что хотите выйти из аккаунта?',
      confirmLabel: 'Выйти',
      confirmVariant: 'destructive',
      onConfirm: () => {
        logout()
        navigate('/login')
      },
    })
  }

  return (
    <div className="min-h-screen bg-background text-foreground">
      <TooltipProvider delayDuration={200}>
      <div className="flex h-screen overflow-hidden">
        {/* Sidebar */}
        <aside 
          className={`transition-all duration-300 border-r border-border bg-card flex flex-col ${
            sidebarOpen ? 'w-64' : 'w-16'
          }`}
        >

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {/* Договоры */}
            <Tooltip>
              <TooltipTrigger asChild>
                <Link
                  to="/contracts"
                  className={`flex items-center ${sidebarOpen ? 'gap-3 justify-start px-3 py-2' : 'justify-center p-2'}  rounded-md transition-colors ${
                    location.pathname === '/contracts' || location.pathname === '/'
                      ? 'bg-primary text-primary-foreground'
                      : 'hover:bg-accent hover:text-accent-foreground'
                  }`}
                >
                  <FileText className="h-5 w-5" />
                  {sidebarOpen && <span className="text-sm font-medium">Договоры</span>}
                </Link>
              </TooltipTrigger>
              {!sidebarOpen && <TooltipContent side="right">Договоры</TooltipContent>}
            </Tooltip>

            {user?.roles?.includes('ROLE_ADMIN') && (
              <>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Link
                  to="/documents"
                  className={`flex items-center ${sidebarOpen ? 'gap-3 justify-start px-3 py-2' : 'justify-center p-2'} rounded-md transition-colors ${
                    location.pathname === '/documents'
                      ? 'bg-primary text-primary-foreground'
                      : 'hover:bg-accent hover:text-accent-foreground'
                  }`}
                >
                  <FileBarChart className="h-5 w-5" />
                  {sidebarOpen && <span className="text-sm font-medium whitespace-nowrap">Шаблоны документов</span>}
                    </Link>
                  </TooltipTrigger>
                  {!sidebarOpen && <TooltipContent side="right">Шаблоны документов</TooltipContent>}
                </Tooltip>

                <Separator className="my-2" />
              </>
            )}

            {user?.roles?.includes('ROLE_ADMIN') && (
              <Accordion type="single" collapsible value={sidebarOpen ? (databasesOpen ? "databases" : undefined) : undefined} onValueChange={(value) => setDatabasesOpen(value === "databases")}>
                <AccordionItem value="databases" className="border-none">
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <AccordionTrigger className={`w-full flex items-center ${sidebarOpen ? 'justify-between' : 'justify-center'} gap-2 px-3 py-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider hover:text-foreground transition-colors`}>
                        <div className={`flex items-center ${sidebarOpen ? 'gap-3' : 'justify-center'}`}>
                          <Database className="h-4 w-4" />
                          {sidebarOpen && <span>Справочники</span>}
                        </div>
                      </AccordionTrigger>
                    </TooltipTrigger>
                    {!sidebarOpen && <TooltipContent side="right">Справочники</TooltipContent>}
                  </Tooltip>
                  <AccordionContent className="mt-1 space-y-1 pl-0">
                    {databaseItems.map((item, index) => {
                      const isActive = location.pathname === item.path
                      const Icon = item.icon

                      return (
                        <Tooltip key={index}>
                          <TooltipTrigger asChild>
                            <Link
                              to={item.path}
                              className={`flex items-center ${sidebarOpen ? 'gap-3 justify-start px-3 py-2' : 'justify-center p-2'}  rounded-md transition-colors ${
                                isActive
                                  ? 'bg-primary text-primary-foreground'
                                  : 'hover:bg-accent hover:text-accent-foreground'
                              }`}
                            >
                              <Icon className="h-4 w-4" />
                              {sidebarOpen && <span className="text-sm font-medium whitespace-nowrap">{item.label}</span>}
                            </Link>
                          </TooltipTrigger>
                          {!sidebarOpen && <TooltipContent side="right">{item.label}</TooltipContent>}
                        </Tooltip>
                      )
                    })}
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            )}
          </nav>

          <Separator />

          {/* User Info */}
          <div className="p-4 space-y-2">
            <Tooltip>
              <TooltipTrigger asChild>
                <div className={cn("flex items-center gap-3 rounded-md bg-accent/50", sidebarOpen ? "p-3" : "p-2 justify-center")}>
                  <UserCircle className="h-8 w-8 text-primary" />
                  {sidebarOpen && (
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{user?.username}</p>
                      <p className="text-xs text-muted-foreground truncate">
                        {user?.roles?.includes('ROLE_ADMIN') ? 'Администратор' : 
                         user?.roles?.includes('ROLE_MANAGER') ? 'Менеджер' : 'Пользователь'}
                      </p>
                    </div>
                  )}
                </div>
              </TooltipTrigger>
              {!sidebarOpen && (
                <TooltipContent side="right">
                  <div className="text-center">
                    <p className="text-sm font-medium">{user?.username}</p>
                    <p className="text-xs text-muted-foreground">
                      {user?.roles?.includes('ROLE_ADMIN') ? 'Администратор' : 
                       user?.roles?.includes('ROLE_MANAGER') ? 'Менеджер' : 'Пользователь'}
                    </p>
                  </div>
                </TooltipContent>
              )}
            </Tooltip>
            <Tooltip>
              <TooltipTrigger asChild>
                <Button 
                  variant="ghost" 
                  className={`w-full mt-2 justify-start ${sidebarOpen ? '' : 'px-0 !justify-center'}`} 
                  onClick={handleLogout}
                >
                  <LogOut className="h-4 w-4 mr-2" />
                  {sidebarOpen && 'Выйти'}
                </Button>
              </TooltipTrigger>
              {!sidebarOpen && <TooltipContent side="right">Выйти</TooltipContent>}
            </Tooltip>
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
              <Menu className={cn("h-5 w-5", !sidebarOpen && 'rotate-90')} />
            </Button>
            
            <div className="flex-1">
              <h1 className="text-xl font-semibold">
                {location.pathname === '/contracts' || location.pathname === '/' ? 'Договоры' : 
                 location.pathname === '/documents' ? 'Шаблоны документов' :
                 location.pathname.includes('/databases/creditors') ? 'Кредиторы' :
                 location.pathname.includes('/databases/courts') ? 'Арбитражные суды' :
                 location.pathname.includes('/databases/bailiffs') ? 'Судебные приставы' :
                 location.pathname.includes('/databases/fns') ? 'ФНС' :
                 location.pathname.includes('/databases/mchs') ? 'ГИМС МЧС' :
                 location.pathname.includes('/databases/rosgvardia') ? 'Росгвардия' :
                 location.pathname.includes('/contract/') ? 'Карточка договора' :
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
          <main className="flex-1 overflow-y-auto" style={{ scrollbarGutter: 'stable' }}>
            {children}
          </main>
        </div>
      </div>
      </TooltipProvider>
    </div>
  )
}
