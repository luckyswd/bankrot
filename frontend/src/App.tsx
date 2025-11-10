import type { ReactNode } from "react"
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom"

import { AppProvider } from "./context/AppContext"
import { ThemeProvider } from "./context/ThemeContext"
import { AuthProvider, useAuth } from "./context/AuthContext"
import Layout from "./components/Layout"
import Login from "./components/Login"
import Dashboard from "./components/Dashboard"
import Loading from "./components/shared/Loading"
import DocumentView from "./components/DocumentView"
import DocumentsPage from "./components/DocumentsPage"
import CreditorsDatabase from "./components/databases/CreditorsDatabase"
import CourtsDatabase from "./components/databases/CourtsDatabase"
import BailiffsDatabase from "./components/databases/BailiffsDatabase"
import ClientCard from "./components/ClientCard"
import { RosgvardiaDatabase } from "./components/databases/RosgvardiaDatabase"
import { MchsDatabase } from "./components/databases/MchsDatabase"
import { FnsDatabase } from "./components/databases/FnsDatabase"
import GostekhnadzorDatabase from "./components/databases/GostekhnadzorDatabase"
import { ToastViewport } from "./components/ui/toast"

function ProtectedRoute({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth()
  
  if (loading) {
    return <Loading fullScreen text="Загрузка..." />
  }
  
  return user ? children : <Navigate to="/login" />
}
function AppRoutes() {
  return (
    <Routes>
      <Route
        path="/*"
        element={
          <ProtectedRoute>
            <Layout>
              <Routes>
                <Route path="/" element={<Navigate to="/contracts" />} />
                <Route path="/contracts" element={<Dashboard />} />
                <Route path="/documents" element={<DocumentsPage />} />
                <Route path="/contract/:id" element={<ClientCard />} />
                <Route path="/document/:contractId/:docType" element={<DocumentView />} />
                <Route path="/databases/creditors" element={<CreditorsDatabase />} />
                <Route path="/databases/courts" element={<CourtsDatabase />} />
                <Route path="/databases/bailiffs" element={<BailiffsDatabase />} />
                <Route path="/databases/fns" element={<FnsDatabase />} />
                <Route path="/databases/mchs" element={<MchsDatabase />} />
                <Route path="/databases/rosgvardia" element={<RosgvardiaDatabase />} />
                <Route path="/databases/gostekhnadzor" element={<GostekhnadzorDatabase />} />
              </Routes>
            </Layout>
          </ProtectedRoute>
        }
      />
    </Routes>
  )
}

function App() {
  return (
    <AuthProvider>
      <AppProvider>
        <Router>
          <Routes>
            {/* Login БЕЗ ThemeProvider - всегда светлая */}
            <Route path="/login" element={<LoginWrapper />} />
            
            {/* Остальные роуты С ThemeProvider */}
            <Route path="/*" element={
              <ThemeProvider>
                  <ToastViewport />
                  <AppRoutes />
              </ThemeProvider>
            } />
          </Routes>
        </Router>
      </AppProvider>
    </AuthProvider>
  )
}

function LoginWrapper() {
  const { user } = useAuth()
  return user ? <Navigate to="/contracts" /> : <Login />
}

export default App
