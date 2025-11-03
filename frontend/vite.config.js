import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  server: {
    port: 5173,
    host: '0.0.0.0', // Слушать на всех интерфейсах для Docker
    strictPort: true, // Не менять порт если занят
    watch: {
      usePolling: true, // Для работы в Docker
    },
    hmr: {
      clientPort: 80,
    },
  }
})
