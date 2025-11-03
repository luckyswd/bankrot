FROM node:20-alpine

WORKDIR /app

# Копируем package files
COPY package*.json ./

# Устанавливаем зависимости
RUN npm install

# Копируем остальные файлы
COPY . .

# Задаем аргумент для build time
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

# Expose порт Vite dev server
EXPOSE 5173

# Запускаем dev server с доступом извне контейнера
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

