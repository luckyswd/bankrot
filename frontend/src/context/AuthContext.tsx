import { createContext, useContext, useState, useMemo, useCallback, useEffect, type ReactNode } from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"

import { apiRequest } from "../config/api"

interface AuthUser {
  id: number
  username: string
  roles: string[]
  fullName?: string
}

interface LoginResponse {
  token: string
  user: AuthUser
}

interface LoginResult {
  success: boolean
  error?: string
}

interface AuthContextValue {
  user: AuthUser | null
  token: string | null
  loading: boolean
  login: (username: string, password: string) => Promise<LoginResult>
  logout: () => void
  hasRole: (role: string) => boolean
}

const AuthContext = createContext<AuthContextValue | null>(null)

interface AuthProviderProps {
  children: ReactNode
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [token, setToken] = useState<string | null>(() => localStorage.getItem("token"))
  const queryClient = useQueryClient()

  const logout = useCallback(() => {
    localStorage.removeItem("token")
    setToken(null)
    queryClient.removeQueries({ queryKey: ["auth"] })
  }, [queryClient])

  const {
    data: userData,
    error: userError,
    isPending,
    isFetching,
  } = useQuery<AuthUser | null>({
    queryKey: ["auth", "me"],
    queryFn: () => apiRequest("/api/v1/me"),
    enabled: Boolean(token),
    retry: false,
  })

  useEffect(() => {
    if (userError) {
      console.error("Error fetching user:", userError)
      logout()
    }
  }, [userError, logout])

  const loginMutation = useMutation<LoginResponse, Error, { username: string; password: string }>({
    mutationFn: ({ username, password }) =>
      apiRequest("/api/v1/login", {
        method: "POST",
        auth: false,
        body: JSON.stringify({ username, password }),
      }),
    onSuccess: (data) => {
      if (!data?.token) {
        return
      }

      localStorage.setItem("token", data.token)
      setToken(data.token)
      queryClient.setQueryData(["auth", "me"], data.user ?? null)
    },
  })

  const login = useCallback(
    async (username: string, password: string): Promise<LoginResult> => {
      try {
        const data = await loginMutation.mutateAsync({ username, password })

        if (!data?.token) {
          return { success: false, error: "Login failed" }
        }

        await queryClient.invalidateQueries({ queryKey: ["auth", "me"] })
        return { success: true }
      } catch (error: any) {
        return {
          success: false,
          error: error?.body?.message || error.message || "Network error",
        }
      }
    },
    [loginMutation, queryClient],
  )

  const loading = token ? isPending || isFetching : false
  const user = userData ?? null

  const hasRole = useCallback(
    (role: string) => user?.roles?.includes(role) || false,
    [user],
  )

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
    throw new Error("useAuth must be used within AuthProvider")
  }
  return context
}
