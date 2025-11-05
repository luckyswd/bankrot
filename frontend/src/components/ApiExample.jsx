import { useMutation, useQuery } from '@tanstack/react-query'
import { apiRequest, API_URL } from '../config/api'

/**
 * Компонент-пример для демонстрации работы с API
 */
export function ApiExample() {
  const {
    data,
    isPending,
    isError,
    error,
    refetch,
  } = useQuery({
    queryKey: ['api', 'example', 'test'],
    queryFn: () => apiRequest('/api/test'),
  })

  const postExample = useMutation({
    mutationFn: () =>
      apiRequest('/api/endpoint', {
        method: 'POST',
        body: JSON.stringify({
          name: 'Example',
          value: 123,
        }),
      }),
  })

  if (isPending) return <div>Загрузка...</div>
  if (isError) return <div>Ошибка: {error?.message}</div>

  return (
    <div className="api-example">
      <h2>API Пример</h2>
      <p>
        API URL: <code>{API_URL}</code>
      </p>

      {data && (
        <div>
          <h3>Ответ от API:</h3>
          <pre>{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}

      <button onClick={() => refetch()}>Обновить данные</button>

      <button
        onClick={() => postExample.mutate()}
        disabled={postExample.isPending}
      >
        {postExample.isPending ? 'Отправка...' : 'Пример POST запроса'}
      </button>

      {postExample.isError && (
        <div>POST ошибка: {postExample.error?.message}</div>
      )}
    </div>
  )
}
