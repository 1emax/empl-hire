# Employee Management API

API для управления сотрудниками компании, разработанное с использованием Symfony 7.2.

## Требования

- Docker
- Docker Compose

## Установка и запуск

1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd <repository-name>
```

2. Запустите контейнеры:
```bash
docker compose up -d
```

3. Установите зависимости:
```bash
docker compose exec php composer install
```

4. Создайте базу данных и выполните миграции:
```bash
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate
```

## Конфигурация окружения

В проекте настроено следующее:
- .env – базовые дефолтные настройки (коммитится в репозиторий).
- .env.dev и .env.test – настройки для разработки и тестирования соответственно.
- .env.local (и .env.<APP_ENV>.local) – для локальных переопределений и секретов, не коммитятся.

Таким образом, каждый разработчик может оставить свои настройки в .env.local, а базовая конфигурация будет доступна из репозитория.

## API Endpoints

API доступно по адресу: `http://localhost:8080/api`

Swagger документация доступна по адресу: `http://localhost:8080/api/doc`

### Доступные эндпоинты:

- `POST /api/employees` - Создание нового сотрудника
- `GET /api/employees` - Получение списка всех сотрудников
- `GET /api/employees/{id}` - Получение информации о конкретном сотруднике
- `PUT /api/employees/{id}` - Обновление информации о сотруднике
- `DELETE /api/employees/{id}` - Удаление сотрудника

## Валидация данных

При создании и обновлении сотрудника проверяются следующие условия:

- Обязательные поля: имя, фамилия, email, дата приема на работу, зарплата
- Дата приема на работу не может быть в прошлом
- Минимальная зарплата: 100

## Запуск тестов

```bash
docker compose exec php php bin/phpunit
```