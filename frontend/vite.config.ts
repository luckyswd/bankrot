import path from "node:path"

import react from "@vitejs/plugin-react"
import { defineConfig, loadEnv } from "vite"

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "")
  const proxyTarget = env.VITE_API_PROXY_TARGET || env.VITE_API_URL || "http://localhost:8000"

  return {
    plugins: [react()],
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "./src"),
        "@components": path.resolve(__dirname, "./src/components"),
        "@assets": path.resolve(__dirname, "./src/assets"),
        "@utils": path.resolve(__dirname, "./src/utils"),
        "@hooks": path.resolve(__dirname, "./src/hooks"),
        "@store": path.resolve(__dirname, "./src/store"),
        "@pages": path.resolve(__dirname, "./src/pages"),
        "@api": path.resolve(__dirname, "./src/api"),
        "@ui": path.resolve(__dirname, "./src/components/ui"),
        "@shared": path.resolve(__dirname, "./src/components/shared"),
      },
    },
    server: {
      port: 5173,
      host: "0.0.0.0", // Слушать на всех интерфейсах для Docker
      strictPort: true, // Не менять порт если занят
      watch: {
        usePolling: true, // Для работы в Docker
      },
      proxy: {
        "/api": {
          target: proxyTarget,
          changeOrigin: true,
          secure: false,
        },
      },
    },
  }
})
