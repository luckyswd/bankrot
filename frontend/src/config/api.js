/**
 * Конфигурация API
 * 
 * VITE_API_URL берется из .env файлов:
 * - .env.development (для dev режима)
 * - .env.production (для production режима)
 * - .env.local (для локальных переопределений, не коммитится в git)
 */

export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

// Базовые настройки для fetch запросов
export const API_CONFIG = {
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  credentials: 'include', // Для отправки cookies
};

/**
 * Функция для выполнения API запросов
 * @param {string} endpoint - URL endpoint (например: '/api/users')
 * @param {object} options - Опции для fetch
 * @returns {Promise}
 */
export async function apiRequest(endpoint, options = {}) {
  const url = `${API_URL}${endpoint}`;
  const { auth = true, headers: customHeaders = {}, ...restOptions } = options;
  const token = auth ? localStorage.getItem('token') : null;
  const isFormData =
    typeof FormData !== 'undefined' && restOptions.body instanceof FormData;
  const baseHeaders = isFormData
    ? { Accept: API_CONFIG.headers.Accept }
    : API_CONFIG.headers;

  const config = {
    ...API_CONFIG,
    ...restOptions,
    headers: {
      ...baseHeaders,
      ...customHeaders,
      ...(auth && token ? { Authorization: `Bearer ${token}` } : {}),
    },
  };

  try {
    const response = await fetch(url, config);
    const contentType = response.headers.get('content-type') || '';
    const isJson = contentType.includes('application/json');
    let responseBody = null;

    if (isJson) {
      try {
        responseBody = await response.json();
      } catch {
        responseBody = null;
      }
    } else {
      responseBody = await response.text();
    }

    if (!response.ok) {
      const errorMessage =
        (responseBody && typeof responseBody === 'object' && responseBody.message) ||
        `HTTP error! status: ${response.status}`;
      const error = new Error(errorMessage);
      error.status = response.status;
      error.body = responseBody;
      throw error;
    }
    
    return responseBody;
  } catch (error) {
    console.error('API Request failed:', error);
    throw error;
  }
}

// Примеры использования:
// import { apiRequest, API_URL } from './config/api';
// 
// // GET запрос
// const users = await apiRequest('/api/users');
//
// // POST запрос
// const newUser = await apiRequest('/api/users', {
//   method: 'POST',
//   body: JSON.stringify({ name: 'John' })
// });
