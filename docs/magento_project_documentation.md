# Документация по проекту Magento Monitoring System

## 2. Разработка требований к программному продукту

### 2.1 Назначение и цели создания системы

Система мониторинга Magento 2 предназначена для обеспечения непрерывного наблюдения за работой интернет-магазина, построенного на платформе Magento 2, с целью своевременного выявления проблем, предотвращения отказов и оптимизации производительности.

**Основные цели создания системы:**
- Мониторинг ключевых компонентов Magento 2 (Cron, RabbitMQ, индексаторы, очереди)
- Раннее обнаружение проблем функционирования
- Оперативное уведомление администраторов о сбоях
- Сбор аналитической информации о работе системы
- Обеспечение более стабильной работы Magento 2

### 2.2 Функциональные требования

#### 2.2.1 Мониторинг Cron-задач
- Регистрация и отслеживание всех Cron-задач в системе
- Отображение информации о времени последнего запуска
- Определение "застрявших" задач
- Группировка задач по модулям и категориям
- Уведомление о неудачных выполнениях задач

#### 2.2.2 Мониторинг RabbitMQ
- Отслеживание статуса очередей и консьюмеров
- Мониторинг числа сообщений в каждой очереди
- Регистрация активности потребителей сообщений
- Определение "застрявших" консьюмеров
- Оповещение о проблемах в работе очередей

#### 2.2.3 Система оповещений
- Интеграция с Telegram для мгновенной доставки уведомлений
- Настраиваемые правила генерации оповещений
- Различные уровни приоритета сообщений (информационные, предупреждения, критические)
- Возможность фильтрации уведомлений по типам и источникам
- Настройка расписания отправки сообщений

#### 2.2.4 Административный интерфейс
- Панель мониторинга с общим состоянием системы
- Детальные страницы с информацией по каждому компоненту
- Настройка правил уведомлений
- Просмотр журналов событий и уведомлений
- Управление интеграциями (Telegram)

### 2.3 Нефункциональные требования

#### 2.3.1 Производительность
- Максимальное использование системных ресурсов: не более 10% CPU
- Дополнительное потребление памяти: не более 256 MB
- Время ответа административного интерфейса: не более 2 секунд
- Минимальное влияние на основные бизнес-процессы Magento

#### 2.3.2 Надежность
- Время бесперебойной работы (uptime): 99.9%
- Сохранение работоспособности при сбоях основной системы
- Отказоустойчивость компонентов мониторинга
- Корректное восстановление после сбоев

#### 2.3.3 Безопасность
- Аутентификация и авторизация доступа к административному интерфейсу
- Шифрование данных при передаче через внешние API
- Безопасное хранение ключей доступа и конфигурации
- Аудит действий пользователей в системе

#### 2.3.4 Масштабируемость
- Поддержка работы в высоконагруженных инсталляциях Magento
- Возможность подключения дополнительных источников мониторинга
- Добавление новых каналов оповещения
- Расширение функционала без существенной переработки архитектуры

#### 2.3.5 Совместимость
- Поддержка Magento 2 Open Source и Commerce (версии 2.3.x - 2.4.x)
- Совместимость с PHP 7.4 - 8.2
- Работа в различных окружениях (Linux, macOS для разработки)
- Поддержка различных баз данных (MySQL 5.7+, MariaDB 10.2+)

## 3. Техническое задание по ГОСТ 7.32-2017

### 3.1 Общие сведения

#### 3.1.1 Наименование системы
Полное наименование: Система мониторинга и оповещения для Magento 2
Условное обозначение: Magento Monitoring System

#### 3.1.2 Основание для разработки
Необходимость обеспечения стабильной работы e-commerce платформы Magento 2 и оперативного выявления проблем в ее функционировании.

#### 3.1.3 Назначение разработки
Система предназначена для мониторинга работоспособности ключевых компонентов Magento 2, сбора метрик производительности и оповещения администраторов о нештатных ситуациях.

#### 3.1.4 Плановые сроки начала и окончания работ
- Начало работ: январь 2023 г.
- Окончание работ: июнь 2023 г.

### 3.2 Характеристика объекта автоматизации

Объектом автоматизации является интернет-магазин на базе Magento 2 - платформы электронной коммерции с открытым исходным кодом. Система Magento 2 включает следующие ключевые компоненты, требующие мониторинга:

- Cron-менеджер (планировщик задач)
- Система очередей (RabbitMQ)
- Индексаторы каталога и других данных
- API-интерфейсы
- Кеширование данных

### 3.3 Требования к системе

#### 3.3.1 Требования к системе в целом
Система должна обеспечивать непрерывный мониторинг ключевых компонентов Magento 2, сбор метрик производительности, выявление проблемных ситуаций и отправку уведомлений через настраиваемые каналы связи.

#### 3.3.2 Требования к структуре и функционированию системы
Система должна состоять из следующих подсистем:
- Модуль сбора данных о работе Cron-задач
- Модуль мониторинга RabbitMQ
- Система оповещений с интеграцией Telegram
- Административный интерфейс для настройки и мониторинга

#### 3.3.3 Требования к надежности
- Система должна сохранять работоспособность при сбоях основного приложения Magento
- Время восстановления после аварийных ситуаций не должно превышать 5 минут
- Система должна корректно обрабатывать ошибочные ситуации, исключая возможность "каскадных отказов"
- Регулярное резервное копирование конфигурации и данных системы

#### 3.3.4 Требования к безопасности
- Соблюдение принципов безопасности Magento 2
- Шифрование конфиденциальных данных при хранении и передаче
- Обеспечение аутентификации и авторизации для доступа к административным функциям
- Защита от несанкционированного доступа к данным мониторинга

#### 3.3.5 Требования к эргономике и технической эстетике
- Интуитивно понятный интерфейс администрирования
- Наглядное представление информации с использованием графиков и диаграмм
- Единый стиль оформления, согласованный с административным интерфейсом Magento 2
- Адаптивный дизайн для использования на различных устройствах

### 3.4 Стадии и этапы разработки

1. Подготовительный этап:
   - Анализ требований
   - Изучение архитектуры Magento 2
   - Выбор технологий и инструментов

2. Проектирование:
   - Разработка архитектуры системы
   - Проектирование базы данных
   - Проектирование интерфейса пользователя

3. Разработка:
   - Разработка модулей мониторинга
   - Разработка системы уведомлений
   - Разработка административного интерфейса

4. Тестирование:
   - Функциональное тестирование
   - Нагрузочное тестирование
   - Тестирование безопасности

5. Внедрение:
   - Установка и настройка
   - Обучение персонала
   - Опытная эксплуатация

### 3.5 Порядок контроля и приемки

1. Проверка соответствия разработанной системы требованиям технического задания
2. Тестирование работоспособности в различных сценариях
3. Проверка документации
4. Приемка результатов разработки

## 4. Проектирование архитектуры системы

### 4.1 Общая архитектура

Система мониторинга Magento 2 построена по модульному принципу, типичному для расширений Magento, и включает следующие основные компоненты:

1. **Базовый модуль (Lachestry_Base)**
   - Основная функциональность
   - Общие классы и интерфейсы
   - Управление зависимостями

2. **Модуль конфигурации (Lachestry_Configuration)**
   - Управление настройками
   - Хранение конфигурационных параметров
   - Административный интерфейс для конфигурации

3. **Модуль мониторинга Cron (Lachestry_Cron)**
   - Отслеживание Cron-задач
   - Хранение информации о выполнении задач
   - Определение проблемных задач

4. **Модуль отображения Cron (Lachestry_CronMonitoring)**
   - Административный интерфейс для Cron-задач
   - Визуализация статусов и метрик
   - Управление задачами

5. **Модуль Telegram (Lachestry_Telegram)**
   - Интеграция с Telegram API
   - Отправка уведомлений
   - Управление чатами и пользователями

6. **Модуль мониторинга RabbitMQ (Lachestry_RabbitMQMonitor)**
   - Отслеживание очередей и консьюмеров
   - Сбор статистики работы
   - Выявление проблемных ситуаций

7. **Модуль уведомлений (Lachestry_Notifier)**
   - Формирование уведомлений
   - Управление каналами отправки
   - Настройка правил уведомлений

### 4.2 Структура базы данных

#### 4.2.1 Таблица lachestry_job_codes_info
Предназначена для хранения информации о Cron-задачах.

```
Структура:
- id (smallint, unsigned, auto_increment) - Уникальный идентификатор
- job_code_name (varchar(100)) - Название Cron-задачи
- schedule (varchar(50)) - Расписание выполнения (cron-синтаксис)
- module (varchar(150)) - Модуль, к которому относится задача
- config_path (varchar(200)) - Путь конфигурации в системе
- group (varchar(100)) - Группа Cron-задач
```

#### 4.2.2 Таблица lachestry_telegram_chats
Хранит информацию о Telegram-чатах для отправки уведомлений.

```
Структура:
- id (smallint, unsigned, auto_increment) - Уникальный идентификатор
- chat_id (bigint) - Идентификатор чата в Telegram
- chat_name (varchar(60)) - Название чата
- user_name (varchar(40)) - Имя пользователя в Telegram
- created_at (timestamp) - Дата создания записи
- telegram_updated_at (timestamp) - Дата обновления данных из Telegram
- is_active (smallint) - Статус активности чата
```

#### 4.2.3 Таблица rabbitmq_consumer_activity
Содержит информацию об активности консьюмеров RabbitMQ.

```
Структура:
- entity_id (int, unsigned, auto_increment) - Уникальный идентификатор
- consumer_name (varchar(255)) - Название консьюмера
- pid (int, unsigned) - Идентификатор процесса
- last_activity (timestamp) - Время последней активности
- status (varchar(50)) - Статус консьюмера
```

### 4.3 Интерфейсы взаимодействия

#### 4.3.1 Административный интерфейс
Реализован в рамках административной панели Magento 2 и предоставляет следующие функции:
- Просмотр статистики и метрик
- Настройка системы мониторинга
- Управление уведомлениями
- Просмотр журналов событий

#### 4.3.2 API для внешних систем
Система предоставляет REST API для интеграции с внешними системами:
- Получение статистики мониторинга
- Управление уведомлениями
- Получение статуса компонентов

#### 4.3.3 Система уведомлений
Обеспечивает отправку уведомлений через различные каналы:
- Telegram
- Email (через стандартный механизм Magento)
- Журнал событий в системе

### 4.4 Механизмы безопасности

- Аутентификация и авторизация на основе стандартных механизмов Magento 2
- Шифрование ключей API и токенов доступа
- Валидация входных данных
- Защита от XSS и CSRF атак
- Логирование действий пользователей

## 5. Развертывание базы данных MySQL

### 5.1 Конфигурация MySQL

В проекте используется MySQL 5.7, развернутый через Docker. Основные параметры конфигурации:

```yaml
mysql_57:
  image: mysql/mysql-server:5.7
  command: --explicit_defaults_for_timestamp --bind-address=0.0.0.0
  restart: unless-stopped
  container_name: mysql_57
  environment:
  - MYSQL_ROOT_PASSWORD:password
  - MYSQL_USER=magento
  - MYSQL_PASSWORD=password
  ports:
    - "3307:3306"
  env_file: ./db/db.env
  deploy:
    resources:
      limits:
        memory: 3072M
  volumes:
    - './db/dbfiles:/var/lib/mysql'
    - './db:/myfiles'
    - './db/my.cnf:/etc/my.cnf'
    - './db/logs:/var/log/mysql'
```

### 5.2 Настройки производительности

Для обеспечения оптимальной работы MySQL с Magento 2 рекомендуется следующая конфигурация (в файле my.cnf):

```
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 300
innodb_file_per_table = 1
innodb_flush_method = O_DIRECT
innodb_thread_concurrency = 8
thread_cache_size = 8
query_cache_size = 64M
query_cache_limit = 2M
```

### 5.3 Создание базы данных и пользователя

1. Подключение к MySQL:
```bash
docker exec -it mysql_57 mysql -uroot -p
```

2. Создание базы данных:
```sql
CREATE DATABASE magento;
```

3. Создание пользователя (дополнительно к указанному в docker-compose):
```sql
CREATE USER 'magento'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON magento.* TO 'magento'@'%';
FLUSH PRIVILEGES;
```

### 5.4 Оптимизация для Magento

Для Magento 2 рекомендуется дополнительно настроить следующие параметры MySQL:

```
innodb_buffer_pool_instances = 8
tmp_table_size = 64M
max_heap_table_size = 64M
max_allowed_packet = 64M
```

### 5.5 Резервное копирование

Настройка регулярного резервного копирования базы данных:

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
docker exec mysql_57 mysqldump -uroot -ppassword magento > ./backups/magento_$DATE.sql
```

## 6. Развертывание веб-сервера

### 6.1 Конфигурация Nginx

В проекте используется Nginx в качестве веб-сервера. Конфигурация для Magento 2:

```nginx
upstream fastcgi_backend {
    server 127.0.0.1:9081;
}

server {
    listen 80;
    server_name magento-thesis.local;

    set $MAGE_ROOT /Users/favis/Projects/magento-thesis;
    set $MAGE_MODE developer;

    root $MAGE_ROOT/pub;
    index index.php;
    autoindex off;
    charset UTF-8;
    error_page 404 403 = /errors/404.php;

    # Защита чувствительных файлов
    location /.user.ini {
        deny all;
    }

    # Обработка статических файлов
    location /static/ {
        location ~ ^/static/version\d*/ {
            rewrite ^/static/version\d*/(.*)$ /static/$1 last;
        }

        location ~* \.(ico|jpg|jpeg|png|gif|svg|js|css|eot|ttf|woff|woff2)$ {
            add_header Cache-Control "public";
            add_header X-Frame-Options "SAMEORIGIN";
            expires +1y;

            if (!-f $request_filename) {
                rewrite ^/static/(version\d*/)?(.*)$ /static.php?resource=$2 last;
            }
        }
    }

    # PHP обработчик
    location ~ ^/(index|get|static|errors/report|errors/404|errors/503)\.php$ {
        try_files $uri =404;
        fastcgi_pass fastcgi_backend;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param PHP_FLAG "session.auto_start=off \n suhosin.session.cryptua=off";
        fastcgi_param PHP_VALUE "memory_limit=756M \n max_execution_time=18000";
        fastcgi_read_timeout 600s;
        fastcgi_connect_timeout 600s;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6.2 Настройка PHP-FPM

Для Magento 2 рекомендуется следующая конфигурация PHP-FPM:

```
[www]
user = www-data
group = www-data
listen = 127.0.0.1:9081
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.process_idle_timeout = 10s
pm.max_requests = 500

php_admin_value[memory_limit] = 756M
php_admin_value[max_execution_time] = 600
```

### 6.3 Настройка HTTPS

Для настройки HTTPS с SSL-сертификатом:

```nginx
server {
    listen 443 ssl;
    server_name magento-thesis.local;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # остальная конфигурация как для HTTP
}
```

### 6.4 Оптимизация производительности

Дополнительные настройки Nginx для оптимизации производительности:

```nginx
# Gzip compression
gzip on;
gzip_disable "msie6";
gzip_comp_level 6;
gzip_min_length 1100;
gzip_buffers 16 8k;
gzip_proxied any;
gzip_types
    text/plain
    text/css
    text/js
    text/xml
    text/javascript
    application/javascript
    application/x-javascript
    application/json
    application/xml
    application/xml+rss
    image/svg+xml;
gzip_vary on;

# Browser caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 365d;
    add_header Pragma public;
    add_header Cache-Control "public";
}
```

## 7. Установка и настройка Magento 2

### 7.1 Системные требования

- PHP 7.4 - 8.2
- MySQL 5.7 или выше
- Elasticsearch 7.x (для поиска)
- Nginx или Apache
- Composer 2.x

### 7.2 Установка через Composer

```bash
# Создание проекта
composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition magento-thesis

# Переход в директорию проекта
cd magento-thesis

# Установка Magento
bin/magento setup:install \
--base-url=http://magento-thesis.local/ \
--db-host=localhost:3307 \
--db-name=magento \
--db-user=magento \
--db-password=password \
--admin-firstname=Admin \
--admin-lastname=User \
--admin-email=admin@example.com \
--admin-user=admin \
--admin-password=admin123 \
--language=ru_RU \
--currency=RUB \
--timezone=Europe/Moscow \
--use-rewrites=1 \
--elasticsearch-host=localhost \
--elasticsearch-port=9202
```

### 7.3 Конфигурация Magento

#### 7.3.1 Настройка режима разработки

```bash
bin/magento deploy:mode:set developer
```

#### 7.3.2 Настройка кэширования

```bash
# Включение кэширования
bin/magento cache:enable

# Очистка кэша
bin/magento cache:clean
bin/magento cache:flush
```

#### 7.3.3 Установка модулей мониторинга

```bash
# Создание модулей
mkdir -p app/code/Lachestry

# Копирование файлов модулей
cp -r /path/to/modules/* app/code/Lachestry/

# Включение модулей
bin/magento module:enable Lachestry_Base Lachestry_Configuration Lachestry_Cron Lachestry_CronMonitoring Lachestry_Telegram Lachestry_RabbitMQMonitor Lachestry_Notifier

# Установка модулей
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
```

### 7.4 Настройка Cron

```bash
# Добавление Cron-задач Magento в crontab
bin/magento cron:install

# Проверка работы Cron
bin/magento cron:run
```

### 7.5 Настройка RabbitMQ

#### 7.5.1 Конфигурация RabbitMQ в Magento

Файл `app/etc/env.php`:

```php
'queue' => [
    'amqp' => [
        'host' => 'localhost',
        'port' => '5672',
        'user' => 'guest',
        'password' => 'guest',
        'virtualhost' => '/',
        'ssl' => false
    ],
],
```

#### 7.5.2 Запуск консьюмеров

```bash
# Запуск всех потребителей
bin/magento queue:consumers:start --all

# Запуск конкретного потребителя
bin/magento queue:consumers:start consumer_name
```

### 7.6 Настройка Elasticsearch

Конфигурация Elasticsearch в Magento (файл `app/etc/env.php`):

```php
'system' => [
    'default' => [
        'catalog' => [
            'search' => [
                'engine' => 'elasticsearch7',
                'elasticsearch7_server_hostname' => 'localhost',
                'elasticsearch7_server_port' => '9202',
                'elasticsearch7_index_prefix' => 'magento2',
                'elasticsearch7_enable_auth' => '0',
                'elasticsearch7_server_timeout' => '15'
            ]
        ]
    ]
],
```

### 7.7 Настройка безопасности

```bash
# Установка прав доступа на файлы
find var generated vendor pub/static pub/media app/etc -type f -exec chmod u+w {} +
find var generated vendor pub/static pub/media app/etc -type d -exec chmod u+w {} +
chmod u+x bin/magento

# Настройка двухфакторной аутентификации (только для Commerce)
bin/magento security:tfa:google:set-secret admin
```

### 7.8 Интеграция мониторинга с Telegram

1. Создание бота в Telegram через BotFather
2. Получение API токена
3. Настройка токена в административной панели Magento:
   Stores → Configuration → Lachestry → Telegram → Bot Token

### 7.9 Проверка установки

1. Открытие административной панели: http://magento-thesis.local/admin
2. Проверка работы магазина: http://magento-thesis.local/
3. Проверка модулей мониторинга: System → Monitoring Dashboard 