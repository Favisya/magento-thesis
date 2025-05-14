# RabbitMQMonitor Module

Модуль для мониторинга и управления очередями RabbitMQ в Magento 2.

## Функциональность

- Проверка состояния очередей (количество сообщений, потребители, память)
- Очистка очередей
- Мониторинг состояния очередей
- Интеграция с API RabbitMQ Management

## Тесты

Модуль включает в себя полный набор unit-тестов, которые проверяют:

1. Проверка состояния очереди
   - Корректный ответ API
   - Некорректный JSON
   - Ошибка соединения
   - Различные HTTP-коды ответа

2. Очистка очереди
   - Успешная очистка
   - Ошибка соединения
   - Обработка таймаутов

## Установка

```bash
composer require lachestry/rabbitmq-monitor
bin/magento module:enable Lachestry_RabbitMQMonitor
bin/magento setup:upgrade
```

## Конфигурация

В административной панели Magento 2:
1. Перейдите в Stores > Configuration > Lachestry > RabbitMQ Monitor
2. Настройте параметры подключения:
   - Host
   - Port
   - Username
   - Password
   - Virtual Host

## Использование

```php
$rabbitMQMonitor = $objectManager->get(\Lachestry\RabbitMQMonitor\Model\RabbitMQMonitor::class);

// Проверка состояния очереди
$status = $rabbitMQMonitor->checkQueueStatus('test_queue');

// Очистка очереди
$rabbitMQMonitor->purgeQueue('test_queue');
```

## API Endpoints

Модуль использует следующие эндпоинты RabbitMQ Management API:
- GET /api/queues/{vhost}/{name} - получение информации об очереди
- DELETE /api/queues/{vhost}/{name}/contents - очистка очереди 