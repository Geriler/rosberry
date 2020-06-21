# Rosberry

## Задание
Разработать API и структуру БД для обеспечения работы мобильного
приложения для знакомств с указанным функционалом.

## Функционал приложения
1. Регистрация/авторизация по email/password

POST запросы для регистрации выполняются на адрес `/api/register`, а для авторизации на `/api/login`. В случае использования метода `GET` вернется ошибка:
```json
{
  "errors": [
    "This route doesn't support GET. Use POST"
  ]
}
```

Запрос
```json
{
  "email": "test@example.com",
  "password": "test"
}
```
Так же в запросе можно передать `lat` и `lon` для определения позиции пользователя
```json
{
  "email": "test@example.com",
  "password": "test",
  "lat": "54.994584",
  "lon": "73.363568"
}
```

Ответ
```json
{
  "token": "b14d1ff129a4b47a6cb4e681f1c672722996f5d353edeeb8703a24c34b7d6c4e24d9f57bada6f06eb742706f1751029d7fca7484124a56fda938e0ef2d5027a2"
}
```
В случае ошибок вернется массив `errors`, в которых будут перечисленны все ошибки. Пример:
```json
{
  "errors": [
    "Email is required",
    "Password is required"
  ]
}
```