# Система мониторинга и управления задачами Magento 2

**Дипломная работа**

## О проекте

Данный проект представляет собой дипломную работу, посвященную разработке системы мониторинга и управления задачами для платформы Magento 2. Система позволяет отслеживать выполнение cron-задач, управлять конфигурацией Magento, получать уведомления о проблемах и мониторить состояние очередей RabbitMQ.

Подробная информация о структуре базы данных доступна в [database_structure.md](database_structure.md).

## Технологии

- PHP 8.1
- Magento 2.4.6
- MySQL 8.0
- RabbitMQ 3.9
- Nginx
- Composer 2
- Docker (опционально)

## Структура модулей

### Модули системы

- **Lachestry_Configuration** - Базовый модуль для управления конфигурацией
- **Lachestry_Cron** - Базовый модуль для управления cron-задачами
- **Lachestry_CronMonitoring** - Модуль мониторинга cron-задач
- **Lachestry_Telegram** - Модуль для отправки уведомлений в Telegram
- **Lachestry_Notifier** - Модуль для управления уведомлениями
- **Lachestry_RabbitMQMonitor** - Модуль для мониторинга очередей RabbitMQ

### Функциональность модулей

#### Lachestry_Notifier
Модуль обеспечивает отправку уведомлений о различных событиях в системе:
- Отслеживание зависших cron-задач
- Уведомления о проблемах в индексации
- Уведомления о проблемах с очередями сообщений
- Мониторинг API запросов

#### Lachestry_CronMonitoring
Модуль обеспечивает мониторинг выполнения cron-задач:
- Отслеживание времени выполнения задач
- Определение зависших задач
- Сбор статистики выполнения

#### Lachestry_Telegram
Модуль обеспечивает отправку уведомлений через Telegram:
- Настройка Telegram-бота
- Форматирование сообщений
- Отправка уведомлений

## Инструкция по установке и настройке

### Требования

- PHP 8.1 или выше
- MySQL 8.0 или выше
- Nginx
- RabbitMQ 3.9 или выше
- Composer 2

### Установка

1. Клонировать репозиторий:
   ```bash
   git clone <repository-url>
   cd magento-thesis
   ```

2. Установить зависимости с помощью Composer:
   ```bash
   composer install
   ```

3. Создать базу данных MySQL для проекта

4. Настроить файл app/etc/env.php:
   ```php
   <?php
   return [
       'backend' => [
           'frontName' => 'admin'
       ],
       'cache' => [
           'graphql' => [
               'id_salt' => '...'
           ],
           'frontend' => [
               'default' => [
                   'id_prefix' => '...'
               ],
               'page_cache' => [
                   'id_prefix' => '...'
               ]
           ],
           'allow_parallel_generation' => false
       ],
       'remote_storage' => [
           'driver' => 'file'
       ],
       'queue' => [
           'amqp' => [
               'host' => 'rabbitmq',
               'port' => '5672',
               'user' => 'guest',
               'password' => '',
               'virtualhost' => '/'
           ],
           'consumers_wait_for_messages' => 1
       ],
       'crypt' => [
           'key' => '...'
       ],
       'db' => [
           'table_prefix' => '',
           'connection' => [
               'default' => [
                   'host' => 'db',
                   'dbname' => 'magento',
                   'username' => 'magento',
                   'password' => '',
                   'model' => 'mysql4',
                   'engine' => 'innodb',
                   'initStatements' => 'SET NAMES utf8;',
                   'active' => '1',
                   'driver_options' => [
                       1014 => false
                   ]
               ]
           ]
       ],
       'resource' => [
           'default_setup' => [
               'connection' => 'default'
           ]
       ],
       'x-frame-options' => 'SAMEORIGIN',
       'MAGE_MODE' => 'developer',
       'session' => [
           'save' => 'files'
       ],
       'lock' => [
           'provider' => 'db'
       ],
       'directories' => [
           'document_root_is_pub' => true
       ],
       'cache_types' => [
           'config' => 1,
           'layout' => 1,
           'block_html' => 1,
           'collections' => 1,
           'reflection' => 1,
           'db_ddl' => 1,
           'compiled_config' => 1,
           'eav' => 1,
           'customer_notification' => 1,
           'config_integration' => 1,
           'config_integration_api' => 1,
           'full_page' => 1,
           'config_webservice' => 1,
           'translate' => 1
       ],
       'downloadable_domains' => [
           'localhost'
       ],
       'install' => [
           'date' => 'Tue, 01 Jan 2023 00:00:00 +0000'
       ],
       'system' => [
           'default' => [
               'lachestry_notifier' => [
                   'general' => [
                       'enabled' => 1
                   ],
                   'events' => [
                       'notify_indexer' => 1,
                       'notify_cron' => 1,
                       'notify_queue' => 1,
                       'notify_api' => 1,
                       'notify_stuck_cron' => 1
                   ]
               ]
           ]
       ]
   ];
   ```

5. Установить Magento:
   ```bash
   bin/magento setup:install --base-url=http://localhost/ \
      --db-host=db --db-name=magento --db-user=magento --db-password=XXXXX \
      --admin-firstname=Admin --admin-lastname=User \
      --admin-email=admin@example.com --admin-user=admin --admin-password=admin123 \
      --language=ru_RU --currency=RUB --timezone=Europe/Moscow \
      --use-rewrites=1 \
      --amqp-host=rabbitmq --amqp-port=5672 \
      --amqp-user=guest --amqp-password=guest
   ```

6. Установить модули:
   ```bash
   bin/magento module:enable Lachestry_Configuration Lachestry_Cron Lachestry_CronMonitoring Lachestry_Telegram Lachestry_Notifier Lachestry_RabbitMQMonitor
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento setup:static-content:deploy -f ru_RU en_US
   ```

7. Установить права на файлы:
   ```bash
   find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
   find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
   ```

### Настройка Nginx

1. Скопировать файл nginx.conf.sample в конфигурацию Nginx:
   ```bash
   cp nginx.conf.sample /etc/nginx/conf.d/magento.conf
   ```

2. Отредактировать файл конфигурации, указав пути к проекту и настройки PHP-FPM:
   ```nginx
   upstream fastcgi_backend {
      server unix:/var/run/php/php8.1-fpm.sock;
   }
   
   server {
      listen 80;
      server_name localhost;
      set $MAGE_ROOT /path/to/magento;
      include /path/to/magento/nginx.conf.sample;
   }
   ```

3. Перезапустить Nginx:
   ```bash
   systemctl restart nginx
   ```

### Настройка RabbitMQ

1. Установить RabbitMQ:
   ```bash
   apt-get install rabbitmq-server
   ```

2. Включить плагин управления:
   ```bash
   rabbitmq-plugins enable rabbitmq_management
   ```

3. Создать пользователя для Magento:
   ```bash
   rabbitmqctl add_user magento password
   rabbitmqctl set_user_tags magento administrator
   rabbitmqctl set_permissions -p / magento ".*" ".*" ".*"
   ```

### Настройка модулей

#### Настройка Lachestry_Notifier

1. Перейти в Админ-панель > Stores > Configuration > Lachestry Extensions > Notifier
2. Включить модуль
3. Настроить уведомления для различных событий:
   - Мониторинг индексеров
   - Мониторинг cron-задач
   - Мониторинг очередей
   - Мониторинг API

#### Настройка Lachestry_Telegram

1. Перейти в Админ-панель > Stores > Configuration > Lachestry Extensions > Telegram
2. Указать токен Telegram-бота
3. Указать ID чата для уведомлений

## Настройка cron-задач

Добавить в crontab следующую строку:
```bash
* * * * * php /path/to/magento/bin/magento cron:run | grep -v "Ran jobs by schedule" >> /path/to/magento/var/log/magento.cron.log
```

## Использование Docker (опционально)

Для упрощения разработки можно использовать Docker:

1. Создать файл docker-compose.yml:
   ```yaml
   version: '3'
   services:
     web:
       image: nginx:1.21
       ports:
         - "80:80"
       volumes:
         - ./:/var/www/html
         - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
       depends_on:
         - php
       networks:
         - magento-network

     php:
       build: ./docker/php
       volumes:
         - ./:/var/www/html
       depends_on:
         - db
         - rabbitmq
       networks:
         - magento-network

     db:
       image: mysql:8.0
       ports:
         - "3306:3306"
       environment:
         - MYSQL_ROOT_PASSWORD=root
         - MYSQL_DATABASE=magento
         - MYSQL_USER=magento
         - MYSQL_PASSWORD=magento
       volumes:
         - db-data:/var/lib/mysql
       networks:
         - magento-network

     rabbitmq:
       image: rabbitmq:3.9-management
       ports:
         - "5672:5672"
         - "15672:15672"
       environment:
         - RABBITMQ_DEFAULT_USER=guest
         - RABBITMQ_DEFAULT_PASS=guest
       networks:
         - magento-network

   networks:
     magento-network:

   volumes:
     db-data:
   ```

2. Запустить контейнеры:
   ```bash
   docker-compose up -d
   ```

## Разработка

### Структура модулей

Каждый модуль имеет следующую структуру:
```
app/code/Lachestry/ModuleName/
├── Api/
├── Block/
├── Controller/
├── Cron/
├── etc/
│   ├── adminhtml/
│   ├── config.xml
│   ├── di.xml
│   ├── module.xml
│   └── crontab.xml (если есть cron-задачи)
├── Helper/
├── i18n/
├── Model/
├── Plugin/
├── Test/
└── registration.php
```

### Добавление новых уведомлений

Для добавления новых типов уведомлений необходимо:

1. Добавить константу и метод в классе `Lachestry\Notifier\Model\Config`
2. Создать обработчик в `Lachestry\Notifier\Model\ErrorHandler`
3. Добавить конфигурацию в `app/code/Lachestry/Notifier/etc/config.xml`
4. Добавить поле настройки в админ-панели в `app/code/Lachestry/Notifier/etc/adminhtml/system.xml`

## Тестирование

Для запуска тестов выполните:

```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Lachestry
```

## Лицензия

© 2025 Favis, Все права защищены

---

# Magento 2 Task Monitoring and Management System

**Thesis Project**

## About the Project

This project is a thesis work dedicated to developing a task monitoring and management system for the Magento 2 platform. The system allows tracking cron job execution, managing Magento configuration, receiving problem notifications, and monitoring RabbitMQ queue status.

Detailed information about the database structure is available in [database_structure.md](database_structure.md).

## Technologies

- PHP 8.1
- Magento 2.4.6
- MySQL 8.0
- RabbitMQ 3.9
- Nginx
- Composer 2
- Docker (optional)

## Module Structure

### System Modules

- **Lachestry_Configuration** - Base module for configuration management
- **Lachestry_Cron** - Base module for cron job management
- **Lachestry_CronMonitoring** - Cron job monitoring module
- **Lachestry_Telegram** - Module for sending Telegram notifications
- **Lachestry_Notifier** - Notification management module
- **Lachestry_RabbitMQMonitor** - RabbitMQ queue monitoring module

### Module Functionality

#### Lachestry_Notifier
The module provides notifications about various system events:
- Tracking stuck cron jobs
- Indexer problem notifications
- Message queue problem notifications
- API request monitoring

#### Lachestry_CronMonitoring
The module provides cron job execution monitoring:
- Tracking task execution time
- Identifying stuck tasks
- Collecting execution statistics

#### Lachestry_Telegram
The module provides notification sending via Telegram:
- Telegram bot configuration
- Message formatting
- Notification sending

## Installation and Configuration Guide

### Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Nginx
- RabbitMQ 3.9 or higher
- Composer 2

### Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd magento-thesis
   ```

2. Install dependencies using Composer:
   ```bash
   composer install
   ```

3. Create a MySQL database for the project

4. Configure the app/etc/env.php file:
   ```php
   <?php
   return [
       'backend' => [
           'frontName' => 'admin'
       ],
       'cache' => [
           'graphql' => [
               'id_salt' => '...'
           ],
           'frontend' => [
               'default' => [
                   'id_prefix' => '...'
               ],
               'page_cache' => [
                   'id_prefix' => '...'
               ]
           ],
           'allow_parallel_generation' => false
       ],
       'remote_storage' => [
           'driver' => 'file'
       ],
       'queue' => [
           'amqp' => [
               'host' => 'rabbitmq',
               'port' => '5672',
               'user' => 'guest',
               'password' => '',
               'virtualhost' => '/'
           ],
           'consumers_wait_for_messages' => 1
       ],
       'crypt' => [
           'key' => '...'
       ],
       'db' => [
           'table_prefix' => '',
           'connection' => [
               'default' => [
                   'host' => 'db',
                   'dbname' => 'magento',
                   'username' => 'magento',
                   'password' => '',
                   'model' => 'mysql4',
                   'engine' => 'innodb',
                   'initStatements' => 'SET NAMES utf8;',
                   'active' => '1',
                   'driver_options' => [
                       1014 => false
                   ]
               ]
           ]
       ],
       'resource' => [
           'default_setup' => [
               'connection' => 'default'
           ]
       ],
       'x-frame-options' => 'SAMEORIGIN',
       'MAGE_MODE' => 'developer',
       'session' => [
           'save' => 'files'
       ],
       'lock' => [
           'provider' => 'db'
       ],
       'directories' => [
           'document_root_is_pub' => true
       ],
       'cache_types' => [
           'config' => 1,
           'layout' => 1,
           'block_html' => 1,
           'collections' => 1,
           'reflection' => 1,
           'db_ddl' => 1,
           'compiled_config' => 1,
           'eav' => 1,
           'customer_notification' => 1,
           'config_integration' => 1,
           'config_integration_api' => 1,
           'full_page' => 1,
           'config_webservice' => 1,
           'translate' => 1
       ],
       'downloadable_domains' => [
           'localhost'
       ],
       'install' => [
           'date' => 'Tue, 01 Jan 2023 00:00:00 +0000'
       ],
       'system' => [
           'default' => [
               'lachestry_notifier' => [
                   'general' => [
                       'enabled' => 1
                   ],
                   'events' => [
                       'notify_indexer' => 1,
                       'notify_cron' => 1,
                       'notify_queue' => 1,
                       'notify_api' => 1,
                       'notify_stuck_cron' => 1
                   ]
               ]
           ]
       ]
   ];
   ```

5. Install Magento:
   ```bash
   bin/magento setup:install --base-url=http://localhost/ \
      --db-host=db --db-name=magento --db-user=magento --db-password=XXXXX \
      --admin-firstname=Admin --admin-lastname=User \
      --admin-email=admin@example.com --admin-user=admin --admin-password=admin123 \
      --language=en_US --currency=USD --timezone=UTC \
      --use-rewrites=1 \
      --amqp-host=rabbitmq --amqp-port=5672 \
      --amqp-user=guest --amqp-password=guest
   ```

6. Install modules:
   ```bash
   bin/magento module:enable Lachestry_Configuration Lachestry_Cron Lachestry_CronMonitoring Lachestry_Telegram Lachestry_Notifier Lachestry_RabbitMQMonitor
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento setup:static-content:deploy -f en_US
   ```

7. Set file permissions:
   ```bash
   find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
   find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
   ```

### Nginx Configuration

1. Copy the nginx.conf.sample file to Nginx configuration:
   ```bash
   cp nginx.conf.sample /etc/nginx/conf.d/magento.conf
   ```

2. Edit the configuration file, specifying paths to the project and PHP-FPM settings:
   ```nginx
   upstream fastcgi_backend {
      server unix:/var/run/php/php8.1-fpm.sock;
   }
   
   server {
      listen 80;
      server_name localhost;
      set $MAGE_ROOT /path/to/magento;
      include /path/to/magento/nginx.conf.sample;
   }
   ```

3. Restart Nginx:
   ```bash
   systemctl restart nginx
   ```

### RabbitMQ Configuration

1. Install RabbitMQ:
   ```bash
   apt-get install rabbitmq-server
   ```

2. Enable management plugin:
   ```bash
   rabbitmq-plugins enable rabbitmq_management
   ```

3. Create a user for Magento:
   ```bash
   rabbitmqctl add_user magento password
   rabbitmqctl set_user_tags magento administrator
   rabbitmqctl set_permissions -p / magento ".*" ".*" ".*"
   ```

### Module Configuration

#### Lachestry_Notifier Configuration

1. Go to Admin Panel > Stores > Configuration > Lachestry Extensions > Notifier
2. Enable the module
3. Configure notifications for various events:
   - Indexer monitoring
   - Cron job monitoring
   - Queue monitoring
   - API monitoring

#### Lachestry_Telegram Configuration

1. Go to Admin Panel > Stores > Configuration > Lachestry Extensions > Telegram
2. Specify the Telegram bot token
3. Specify the chat ID for notifications

## Cron Job Setup

Add the following line to crontab:
```bash
* * * * * php /path/to/magento/bin/magento cron:run | grep -v "Ran jobs by schedule" >> /path/to/magento/var/log/magento.cron.log
```

## Using Docker (optional)

For simplified development, you can use Docker:

1. Create a docker-compose.yml file:
   ```yaml
   version: '3'
   services:
     web:
       image: nginx:1.21
       ports:
         - "80:80"
       volumes:
         - ./:/var/www/html
         - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
       depends_on:
         - php
       networks:
         - magento-network

     php:
       build: ./docker/php
       volumes:
         - ./:/var/www/html
       depends_on:
         - db
         - rabbitmq
       networks:
         - magento-network

     db:
       image: mysql:8.0
       ports:
         - "3306:3306"
       environment:
         - MYSQL_ROOT_PASSWORD=root
         - MYSQL_DATABASE=magento
         - MYSQL_USER=magento
         - MYSQL_PASSWORD=magento
       volumes:
         - db-data:/var/lib/mysql
       networks:
         - magento-network

     rabbitmq:
       image: rabbitmq:3.9-management
       ports:
         - "5672:5672"
         - "15672:15672"
       environment:
         - RABBITMQ_DEFAULT_USER=guest
         - RABBITMQ_DEFAULT_PASS=guest
       networks:
         - magento-network

   networks:
     magento-network:

   volumes:
     db-data:
   ```

2. Start the containers:
   ```bash
   docker-compose up -d
   ```

## Development

### Module Structure

Each module has the following structure:
```
app/code/Lachestry/ModuleName/
├── Api/
├── Block/
├── Controller/
├── Cron/
├── etc/
│   ├── adminhtml/
│   ├── config.xml
│   ├── di.xml
│   ├── module.xml
│   └── crontab.xml (if cron jobs are present)
├── Helper/
├── i18n/
├── Model/
├── Plugin/
├── Test/
└── registration.php
```

### Adding New Notifications

To add new notification types, you need to:

1. Add a constant and method in the `Lachestry\Notifier\Model\Config` class
2. Create a handler in `Lachestry\Notifier\Model\ErrorHandler`
3. Add configuration in `app/code/Lachestry/Notifier/etc/config.xml`
4. Add a settings field in the admin panel in `app/code/Lachestry/Notifier/etc/adminhtml/system.xml`

## Testing

To run tests, execute:

```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Lachestry
```

## License

© 2025 Favis, All rights reserved 
