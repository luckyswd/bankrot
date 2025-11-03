// Пример использования API во фронтенде
// frontend/src/components/ApiExample.jsx

import { useState, useEffect } from 'react';
import { apiRequest, API_URL } from '../config/api';

/**
 * Компонент-пример для демонстрации работы с API
 */
export function ApiExample() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Пример GET запроса при монтировании компонента
  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await apiRequest('/api/test');
      setData(response);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Пример POST запроса
  const handlePostExample = async () => {
    try {
      const response = await apiRequest('/api/endpoint', {
        method: 'POST',
        body: JSON.stringify({
          name: 'Example',
          value: 123
        })
      });
      console.log('POST response:', response);
    } catch (err) {
      console.error('POST error:', err);
    }
  };

  if (loading) return <div>Загрузка...</div>;
  if (error) return <div>Ошибка: {error}</div>;

  return (
    <div className="api-example">
      <h2>API Пример</h2>
      <p>API URL: <code>{API_URL}</code></p>
      
      {data && (
        <div>
          <h3>Ответ от API:</h3>
          <pre>{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}

      <button onClick={fetchData}>
        Обновить данные
      </button>
      
      <button onClick={handlePostExample}>
        Пример POST запроса
      </button>
    </div>
  );
}

// Использование в App.jsx:
// import { ApiExample } from './components/ApiExample';
// 
// function App() {
//   return (
//     <div>
//       <ApiExample />
//     </div>
//   );
// }

