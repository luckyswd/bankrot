import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AppProvider, useApp } from './context/AppContext'
import { ThemeProvider } from './context/ThemeContext'
import Layout from './components/Layout'
import Login from './components/Login'
import Dashboard from './components/Dashboard'
import ClientCard from './components/ClientCard'
import DocumentView from './components/DocumentView'
import DocumentsPage from './components/DocumentsPage'
import CreditorsDatabase from './components/databases/CreditorsDatabase'
import CourtsDatabase from './components/databases/CourtsDatabase'
import BailiffsDatabase from './components/databases/BailiffsDatabase'
import { FnsDatabase, MchsDatabase, RosgvardiaDatabase } from './components/databases/OtherDatabases'

function ProtectedRoute({ children }) {
  const { currentUser } = useApp()
  return currentUser ? children : <Navigate to="/login" />
}

function AppRoutes() {
  const { currentUser } = useApp()

  return (
    <Routes>
      <Route 
        path="/login" 
        element={currentUser ? <Navigate to="/contracts" /> : <Login />} 
      />
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
    <ThemeProvider>
      <AppProvider>
        <Router>
          <AppRoutes />
        </Router>
      </AppProvider>
    </ThemeProvider>
  )
}

export default App
