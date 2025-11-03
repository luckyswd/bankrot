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
  
  const config = {
    ...API_CONFIG,
    ...options,
    headers: {
      ...API_CONFIG.headers,
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, config);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
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

