/**
 * Конфигурация API
 * 
 * VITE_API_URL берется из .env файлов:
 * - .env.development (для dev режима)
 * - .env.production (для production режима)
 * - .env.local (для локальных переопределений, не коммитится в git)
 */

const isProd = import.meta.env.PROD
const envApiUrl = import.meta.env.VITE_API_URL?.replace(/\/$/, '') ?? ''
const ensurePathSuffix = (base, suffix) => (base.endsWith(suffix) ? base : `${base}${suffix}`)
const prodBase = envApiUrl || ''
const devBase = envApiUrl || ''

export const API_URL = isProd
  ? ensurePathSuffix(prodBase, '/api/v1')
  : ensurePathSuffix(devBase, '/api/v1')

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
  const { auth = true, headers: customHeaders = {}, responseType, ...restOptions } = options;
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

    if (responseType === 'blob') {
      responseBody = await response.blob();
    } else if (isJson) {
      try {
        responseBody = await response.json();
      } catch {
        responseBody = null;
      }
    } else {
      responseBody = await response.text();
    }

    if (!response.ok) {
      // При 401 ошибке (неавторизован) очищаем токен и редиректим на логин
      if (response.status === 401 && auth) {
        localStorage.removeItem('token');
        // Редирект на логин только если мы не уже на странице логина
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
      }
      
      let errorData = responseBody;

      if (responseType === 'blob' && responseBody instanceof Blob) {
        try {
          errorData = await responseBody.json();
        } catch {
          errorData = { error: 'Ошибка при выполнении запроса' };
        }
      }
      
      const errorMessage =
        (errorData && typeof errorData === 'object' && errorData.error) ||
        (errorData && typeof errorData === 'object' && errorData.message) ||
        `HTTP error! status: ${response.status}`;
      const error = new Error(errorMessage);

      error.status = response.status;
      error.body = errorData;

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
//
// // Скачивание файла (blob)
// const blob = await apiRequest('/document-templates/1/generate', {
//   method: 'POST',
//   body: JSON.stringify({ contractId: 1 }),
//   responseType: 'blob'
// });
