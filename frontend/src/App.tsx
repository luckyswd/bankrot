import type { ReactNode } from "react"
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom"

import { AppProvider } from "./context/AppContext"
import { ThemeProvider } from "./context/ThemeContext"
import { AuthProvider, useAuth } from "./context/AuthContext"
import Layout from "./components/Layout"
import Login from "./components/Login"
import Dashboard from "./components/Dashboard"
import DocumentView from "./components/DocumentView"
import DocumentsPage from "./components/DocumentsPage"
import CreditorsDatabase from "./components/databases/CreditorsDatabase"
import CourtsDatabase from "./components/databases/CourtsDatabase"
import BailiffsDatabase from "./components/databases/BailiffsDatabase"
import { FnsDatabase, MchsDatabase, RosgvardiaDatabase } from "./components/databases/OtherDatabases"
import ClientCard from "./components/ClientCard"
import TestApi from "./pages/TestApi"

function ProtectedRoute({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth()
  
  if (loading) {
    return <div>Loading...</div>
  }
  
  return user ? children : <Navigate to="/login" />
}
function AppRoutes() {
  return (
    <Routes>
      <Route path="/test" element={<TestApi />} />
      <Route
        path="/*"
        element={
          <ProtectedRoute>
            <Layout>
              <Routes>
                <Route path="/" element={<Navigate to="/contracts" />} />
                <Route path="/contracts" element={<Dashboard />} />
                <Route path="/documents" element={<DocumentsPage />} />
                <Route path="/client/:id" element={<ClientCard />} />
                <Route path="/document/:clientId/:docType" element={<DocumentView />} />
                <Route path="/databases/creditors" element={<CreditorsDatabase />} />
                <Route path="/databases/courts" element={<CourtsDatabase />} />
                <Route path="/databases/bailiffs" element={<BailiffsDatabase />} />
                <Route path="/databases/fns" element={<FnsDatabase />} />
                <Route path="/databases/mchs" element={<MchsDatabase />} />
                <Route path="/databases/rosgvardia" element={<RosgvardiaDatabase />} />
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
