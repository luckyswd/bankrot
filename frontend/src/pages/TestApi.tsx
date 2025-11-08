import { useEffect } from 'react'
import { useQuery } from '@tanstack/react-query'
import { apiRequest } from '../config/api'

const TestApi = () => {
  const apiUrl = import.meta.env.VITE_API_URL

  useEffect(() => {
    console.log('🔍 API URL:', apiUrl)
    console.log('🚀 Отправляю запросы к backend...')
  }, [apiUrl])

  const testQuery = useQuery({
    queryKey: ['test-api', 'test'],
    queryFn: () => apiRequest('/api/v1/test'),
  })

  const healthQuery = useQuery({
    queryKey: ['test-api', 'health'],
    queryFn: () => apiRequest('/api/v1/health'),
  })

  useEffect(() => {
    if (testQuery.data) {
      console.log('✅ Данные от /api/v1/test:', testQuery.data)
    }
  }, [testQuery.data])

  useEffect(() => {
    if (healthQuery.data) {
      console.log('✅ Данные от /api/v1/health:', healthQuery.data)
    }
  }, [healthQuery.data])

  const loading = testQuery.isPending || healthQuery.isPending
  const error = testQuery.error || healthQuery.error
  const apiResponse = testQuery.data
  const healthResponse = healthQuery.data

  const resolveErrorMessage = (err: unknown) => {
    if (!err) return ''
    if (err instanceof Error) {
      return err.message
    }
    if (typeof err === 'object' && err !== null) {
      if ('message' in (err as Record<string, unknown>) && typeof (err as { message?: string }).message === 'string') {
        return (err as { message: string }).message
      }
      if ('body' in (err as Record<string, unknown>)) {
        const body = (err as { body?: { message?: string } }).body
        if (body?.message) {
          return body.message
        }
      }
    }
    return typeof err === 'string' ? err : 'Неизвестная ошибка'
  }

  const errorMessage = resolveErrorMessage(error)

  const statusIcon = loading ? '⏳' : error ? '❌' : '✅'
  const statusText = loading
    ? 'Загрузка...'
    : error
    ? 'Ошибка подключения'
    : 'Успешное подключение!'

  return (
    <div
      style={{
        maxWidth: '900px',
        margin: '0 auto',
        padding: '40px 20px',
        fontFamily:
          '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        backgroundColor: '#0f0f0f',
        minHeight: '100vh',
        color: '#e0e0e0',
      }}
    >
      <div
        style={{
          textAlign: 'center',
          marginBottom: '40px',
        }}
      >
        <h1
          style={{
            fontSize: '48px',
            margin: '0 0 10px 0',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            WebkitBackgroundClip: 'text',
            WebkitTextFillColor: 'transparent',
          }}
        >
          🧪 Тест API
        </h1>
        <p style={{ fontSize: '18px', color: '#888' }}>
          Проверка соединения Frontend ↔️ Backend
        </p>
      </div>

      <div
        style={{
          padding: '30px',
          border: '2px solid #333',
          borderRadius: '12px',
          backgroundColor: '#1a1a1a',
          marginBottom: '20px',
        }}
      >
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '15px',
            marginBottom: '20px',
          }}
        >
          <span style={{ fontSize: '32px' }}>{statusIcon}</span>
          <div>
            <h2 style={{ margin: '0 0 5px 0', fontSize: '24px' }}>{statusText}</h2>
            <p style={{ margin: 0, color: '#888', fontSize: '14px' }}>
              API URL:{' '}
              <code
                style={{
                  backgroundColor: '#2a2a2a',
                  padding: '2px 8px',
                  borderRadius: '4px',
                  color: '#4ade80',
                }}
              >
                {apiUrl}
              </code>
            </p>
          </div>
        </div>

        {error && (
          <div
            style={{
              padding: '20px',
              backgroundColor: '#2d1515',
              color: '#ff6b6b',
              borderRadius: '8px',
              border: '1px solid #ff6b6b33',
            }}
          >
            <strong>❌ Ошибка:</strong> {errorMessage}
          </div>
        )}

        {apiResponse && (
          <div style={{ marginTop: '20px' }}>
            <h3
              style={{
                fontSize: '18px',
                marginBottom: '15px',
                color: '#4ade80',
                display: 'flex',
                alignItems: 'center',
                gap: '10px',
              }}
            >
              <span>🎯</span> GET /api/v1/test
            </h3>
            <pre
              style={{
                backgroundColor: '#0a0a0a',
                padding: '20px',
                borderRadius: '8px',
                overflow: 'auto',
                fontSize: '14px',
                border: '1px solid #333',
                margin: 0,
              }}
            >
              {JSON.stringify(apiResponse, null, 2)}
            </pre>
          </div>
        )}

        {healthResponse && (
          <div style={{ marginTop: '20px' }}>
            <h3
              style={{
                fontSize: '18px',
                marginBottom: '15px',
                color: '#4ade80',
                display: 'flex',
                alignItems: 'center',
                gap: '10px',
              }}
            >
              <span>💚</span> GET /api/v1/health
            </h3>
            <pre
              style={{
                backgroundColor: '#0a0a0a',
                padding: '20px',
                borderRadius: '8px',
                overflow: 'auto',
                fontSize: '14px',
                border: '1px solid #333',
                margin: 0,
              }}
            >
              {JSON.stringify(healthResponse, null, 2)}
            </pre>
          </div>
        )}
      </div>

      <div
        style={{
          padding: '20px',
          backgroundColor: '#1a1a1a',
          borderRadius: '8px',
          border: '1px solid #333',
        }}
      >
        <h3 style={{ fontSize: '16px', marginTop: 0 }}>📋 Информация для дебага:</h3>
        <ul
          style={{
            listStyle: 'none',
            padding: 0,
            margin: 0,
            fontSize: '14px',
            color: '#888',
          }}
        >
          <li style={{ padding: '8px 0', borderBottom: '1px solid #222' }}>
            <strong style={{ color: '#e0e0e0' }}>Frontend:</strong> React + Vite
          </li>
          <li style={{ padding: '8px 0', borderBottom: '1px solid #222' }}>
            <strong style={{ color: '#e0e0e0' }}>Backend:</strong>{' '}
            {apiResponse?.data?.backend || 'N/A'}
          </li>
          <li style={{ padding: '8px 0', borderBottom: '1px solid #222' }}>
            <strong style={{ color: '#e0e0e0' }}>PHP Version:</strong>{' '}
            {apiResponse?.data?.php_version || 'N/A'}
          </li>
          <li style={{ padding: '8px 0' }}>
            <strong style={{ color: '#e0e0e0' }}>Timestamp:</strong>{' '}
            {apiResponse?.timestamp
              ? new Date(apiResponse.timestamp * 1000).toLocaleString()
              : 'N/A'}
          </li>
        </ul>
      </div>

      <div
        style={{
          marginTop: '30px',
          padding: '20px',
          backgroundColor: '#1a3a1a',
          borderRadius: '8px',
          border: '1px solid #4ade8044',
        }}
      >
        <p style={{ margin: '0 0 10px 0', fontSize: '14px' }}>
          <strong>✅ Откройте консоль браузера (F12)</strong> для просмотра детальных логов
          запросов
        </p>
        <p style={{ margin: 0, fontSize: '12px', color: '#888' }}>
          Все запросы и ответы логируются в консоль с эмодзи-маркерами для удобного поиска
        </p>
      </div>
    </div>
  )
}

export default TestApi
