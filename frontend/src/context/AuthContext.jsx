import { createContext, useContext, useState, useMemo, useCallback, useEffect } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiRequest } from '../config/api'

const AuthContext = createContext(null)

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useState(() => localStorage.getItem('token'))
  const queryClient = useQueryClient()

  const logout = useCallback(() => {
    localStorage.removeItem('token')
    setToken(null)
    queryClient.removeQueries({ queryKey: ['auth'] })
  }, [queryClient])

  const {
    data: userData,
    error: userError,
    isPending,
    isFetching,
  } = useQuery({
    queryKey: ['auth', 'me'],
    queryFn: () => apiRequest('/api/v1/me'),
    enabled: Boolean(token),
    retry: false,
  })

  useEffect(() => {
    if (userError) {
      console.error('Error fetching user:', userError)
      logout()
    }
  }, [userError, logout])

  const loginMutation = useMutation({
    mutationFn: ({ username, password }) =>
      apiRequest('/api/v1/login', {
        method: 'POST',
        auth: false,
        body: JSON.stringify({ username, password }),
      }),
    onSuccess: (data) => {
      if (!data?.token) {
        return
      }

      localStorage.setItem('token', data.token)
      setToken(data.token)
      queryClient.setQueryData(['auth', 'me'], data.user ?? null)
    },
  })

  const login = useCallback(
    async (username, password) => {
      try {
        const data = await loginMutation.mutateAsync({ username, password })

        if (!data?.token) {
          return { success: false, error: 'Login failed' }
        }

        await queryClient.invalidateQueries({ queryKey: ['auth', 'me'] })
        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error?.body?.message || error.message || 'Network error',
        }
      }
    },
    [loginMutation, queryClient],
  )

  const loading = token ? isPending || isFetching : false
  const user = userData ?? null

  const hasRole = useCallback((role) => user?.roles?.includes(role) || false, [user])

  const value = useMemo(
    () => ({
      user,
      token,
      loading,
      login,
      logout,
      hasRole,
    }),
    [user, token, loading, login, logout, hasRole],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider')
  }
  return context
}
