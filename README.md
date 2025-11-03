# Organizations Catalog API

API каталога организаций, зданий и видов деятельности.

## Технологический стек

- **PHP**: ^8.4
- **Framework**: Laravel ^11.31
- **База данных**: PostgreSQL 16
- **Web-сервер**: Nginx
- **Контейнеризация**: Docker & Docker Compose
- **Документация API**: Swagger UI / Redoc (OpenAPI 3.0)

## Разворачивание

### 1. Клонирование репозитория

```bash
git clone https://github.com/filippi4/organizations-catalog-api
cd organizations-catalog-api
```

### 2. Настройка окружения

Скопируйте файл с примером переменных окружения:

```bash
cp .env.example .env
```

Файл `.env` уже содержит настройки для работы с Docker:

- **База данных**: `organizations_catalog`
- **Пользователь БД**: `laravel`
- **Пароль БД**: `secret`
- **API ключ**: `1234`

### 3. Запуск Docker контейнеров

Соберите и запустите контейнеры:

```bash
docker-compose up -d --build
```

Это создаст и запустит три контейнера:
- **organizations_nginx** - веб-сервер (порт 8080)
- **organizations_php** - PHP-FPM с Laravel
- **organizations_postgres** - база данных PostgreSQL (порт 5432)

### 4. Зайдите в контейнер PHP

```bash
docker exec -it organizations_php sh
```

### 5. Установка зависимостей

Установите PHP зависимости через Composer:

```bash
composer install
```

### 6. Генерация ключа приложения

```bash
php artisan key:generate
```

### 7. Запуск миграций и сидеров

Выполните миграции для создания структуры БД:

```bash
php artisan migrate
```

Заполните базу данных тестовыми данными:

```bash
php artisan db:seed
```

Сидеры создадут:
- Виды деятельности (иерархическая структура)
- Здания с адресами и координатами
- Организации с привязкой к зданиям и видам деятельности

## Документация API

- **Swagger UI**: http://localhost:8080/api/documentation
  - Интерактивный интерфейс для тестирования API
  - Возможность выполнять запросы прямо из браузера

- **Redoc**: http://localhost:8080/api/redoc
  - Альтернативный интерфейс документации
  - Более читаемый формат

- **OpenAPI JSON**: http://localhost:8080/docs/api-docs.json
  - Спецификация для импорта в Postman/Insomnia

## Основные API эндпоинты

### Организации

```
GET /api/organizations              - Список организаций с фильтрацией
GET /api/organizations/{id}         - Получить организацию по ID
GET /api/organizations/geo/radius   - Поиск в радиусе от точки
GET /api/organizations/geo/bounds   - Поиск в прямоугольной области
```

### Здания

```
GET /api/buildings                  - Список зданий
GET /api/buildings/{id}             - Получить здание по ID
```

### Виды деятельности

```
GET /api/activities                 - Список видов деятельности
GET /api/activities/tree            - Древовидная структура
GET /api/activities/{id}            - Получить вид деятельности по ID
```

## Аутентификация

API использует простую аутентификацию через query параметр `key`:

```
http://localhost:8080/api/organizations?key=1234
```

API ключ можно изменить в файле `.env`:

```env
API_KEY=ваш_секретный_ключ
```

## Структура проекта

```
organizations-catalog-api/
├── app/                    # Код приложения
│   ├── Http/
│   │   └── Controllers/    # API контроллеры
│   └── Models/             # Eloquent модели
├── database/
│   ├── migrations/         # Миграции БД
│   └── seeders/            # Сидеры для тестовых данных
├── docker/
│   ├── nginx/             # Конфигурация Nginx
│   └── php/               # Dockerfile для PHP
├── routes/
│   └── api.php            # API маршруты
├── docker-compose.yml     # Конфигурация Docker Compose
├── .env.example           # Пример переменных окружения
└── README.md              # Этот файл
```

## Лицензия

MIT License
