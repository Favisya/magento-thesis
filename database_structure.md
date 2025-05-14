# Документация по структуре базы данных проекта

## Кастомные схемы базы данных

В проекте обнаружены следующие кастомные таблицы:

### 1. Таблица `lachestry_job_codes_info` (модуль Cron)
```sql
CREATE TABLE `lachestry_job_codes_info` (
    `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `job_code_name` varchar(100) COMMENT 'Job code',
    `schedule` varchar(50) COMMENT 'job schedule',
    `module` varchar(150) COMMENT 'module',
    `config_path` varchar(200) COMMENT 'config path',
    `group` varchar(100) COMMENT 'cron group',
    PRIMARY KEY (`id`),
    UNIQUE KEY `GA_JOB_CODE_NAME_UNIQUE` (`job_code_name`)
) ENGINE=InnoDB COMMENT='Информация о задачах Cron';
```

### 2. Таблица `rabbitmq_consumer_activity` (модуль RabbitMQMonitor)
```sql
CREATE TABLE `rabbitmq_consumer_activity` (
    `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
    `consumer_name` varchar(255) NOT NULL COMMENT 'Consumer Name',
    `pid` int(10) UNSIGNED COMMENT 'Process ID',
    `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last Activity Time',
    `status` varchar(50) NOT NULL DEFAULT 'Stopped' COMMENT 'Consumer Status',
    PRIMARY KEY (`entity_id`),
    UNIQUE KEY `RABBITMQ_CONSUMER_ACTIVITY_CONSUMER_NAME` (`consumer_name`),
    KEY `RABBITMQ_CONSUMER_ACTIVITY_STATUS` (`status`)
) ENGINE=InnoDB COMMENT='RabbitMQ Consumer Activity';
```

### 3. Таблица `lachestry_telegram_chats` (модуль Telegram)
```sql
CREATE TABLE `lachestry_telegram_chats` (
    `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `chat_id` bigint(20) NOT NULL COMMENT 'TG chat id',
    `chat_name` varchar(60) COMMENT 'chat name',
    `user_name` varchar(40) COMMENT 'TG user name',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Time',
    `telegram_updated_at` timestamp NULL COMMENT 'Telegram Updated At',
    `is_active` smallint(5) COMMENT 'Is Active',
    PRIMARY KEY (`id`),
    UNIQUE KEY `LACHESTRY_TELEGRAM_CHATS_CHAT_ID_UNIQUE` (`chat_id`)
) ENGINE=InnoDB COMMENT='Telegram Chats';
```

### 4. Таблица `lachestry_log_errors` (модуль LogMonitor)
```sql
CREATE TABLE `lachestry_log_errors` (
    `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
    `log_file` varchar(255) NOT NULL COMMENT 'Log File',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Error Date',
    `severity` varchar(50) NOT NULL COMMENT 'Error Severity',
    `message` text NOT NULL COMMENT 'Error Message',
    `context` varchar(255) COMMENT 'Error Context',
    PRIMARY KEY (`entity_id`),
    KEY `LACHESTRY_LOG_ERRORS_SEVERITY` (`severity`),
    KEY `LACHESTRY_LOG_ERRORS_DATE` (`date`)
) ENGINE=InnoDB COMMENT='Log Errors';
```

### 5. Модуль ProcessMonitor
Модуль ProcessMonitor не имеет своей таблицы в базе данных. Он работает с процессами системы напрямую через команды оболочки, получая информацию о процессах через команду `ps aux`.

## Назначение кастомных таблиц

Эти таблицы являются частью системы мониторинга различных аспектов Magento:

1. **lachestry_job_codes_info** - хранит информацию о заданиях Cron, включая их расписание, модуль и группу. Позволяет отслеживать и управлять Cron-задачами.

2. **rabbitmq_consumer_activity** - отслеживает активность потребителей RabbitMQ, хранит информацию о статусе, идентификаторе процесса и времени последней активности. Помогает контролировать очереди сообщений.

3. **lachestry_telegram_chats** - хранит информацию о чатах Telegram, которые интегрированы с системой. Используется для отправки уведомлений через Telegram.

4. **lachestry_log_errors** - содержит информацию об ошибках из журналов системы, включая уровень серьезности, сообщение и контекст. Полезно для централизованного мониторинга ошибок.

## Применение в проекте

Данные таблицы составляют комплексную систему мониторинга, которая позволяет:

1. **Отслеживать выполнение задач** - мониторинг Cron-задач и процессов системы
2. **Контролировать обмен сообщениями** - отслеживание активности потребителей RabbitMQ
3. **Обрабатывать ошибки** - централизованный сбор и анализ ошибок из журналов
4. **Настраивать уведомления** - отправка уведомлений через Telegram

## Практическая польза

Данная структура БД обеспечивает:
- Оперативное обнаружение проблем в работе системы
- Предотвращение простоев через мониторинг критических компонентов
- Быстрое реагирование на ошибки благодаря централизованному логированию
- Оптимизацию использования ресурсов через мониторинг процессов

Такая архитектура является расширяемой и может быть дополнена для мониторинга других аспектов работы Magento. 

---

# Project Database Structure Documentation

## Custom Database Schemas

The following custom tables were found in the project:

### 1. Table `lachestry_job_codes_info` (Cron module)
```sql
CREATE TABLE `lachestry_job_codes_info` (
    `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `job_code_name` varchar(100) COMMENT 'Job code',
    `schedule` varchar(50) COMMENT 'job schedule',
    `module` varchar(150) COMMENT 'module',
    `config_path` varchar(200) COMMENT 'config path',
    `group` varchar(100) COMMENT 'cron group',
    PRIMARY KEY (`id`),
    UNIQUE KEY `GA_JOB_CODE_NAME_UNIQUE` (`job_code_name`)
) ENGINE=InnoDB COMMENT='Cron jobs information';
```

### 2. Table `rabbitmq_consumer_activity` (RabbitMQMonitor module)
```sql
CREATE TABLE `rabbitmq_consumer_activity` (
    `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
    `consumer_name` varchar(255) NOT NULL COMMENT 'Consumer Name',
    `pid` int(10) UNSIGNED COMMENT 'Process ID',
    `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last Activity Time',
    `status` varchar(50) NOT NULL DEFAULT 'Stopped' COMMENT 'Consumer Status',
    PRIMARY KEY (`entity_id`),
    UNIQUE KEY `RABBITMQ_CONSUMER_ACTIVITY_CONSUMER_NAME` (`consumer_name`),
    KEY `RABBITMQ_CONSUMER_ACTIVITY_STATUS` (`status`)
) ENGINE=InnoDB COMMENT='RabbitMQ Consumer Activity';
```

### 3. Table `lachestry_telegram_chats` (Telegram module)
```sql
CREATE TABLE `lachestry_telegram_chats` (
    `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `chat_id` bigint(20) NOT NULL COMMENT 'TG chat id',
    `chat_name` varchar(60) COMMENT 'chat name',
    `user_name` varchar(40) COMMENT 'TG user name',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Time',
    `telegram_updated_at` timestamp NULL COMMENT 'Telegram Updated At',
    `is_active` smallint(5) COMMENT 'Is Active',
    PRIMARY KEY (`id`),
    UNIQUE KEY `LACHESTRY_TELEGRAM_CHATS_CHAT_ID_UNIQUE` (`chat_id`)
) ENGINE=InnoDB COMMENT='Telegram Chats';
```

### 4. Table `lachestry_log_errors` (LogMonitor module)
```sql
CREATE TABLE `lachestry_log_errors` (
    `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
    `log_file` varchar(255) NOT NULL COMMENT 'Log File',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Error Date',
    `severity` varchar(50) NOT NULL COMMENT 'Error Severity',
    `message` text NOT NULL COMMENT 'Error Message',
    `context` varchar(255) COMMENT 'Error Context',
    PRIMARY KEY (`entity_id`),
    KEY `LACHESTRY_LOG_ERRORS_SEVERITY` (`severity`),
    KEY `LACHESTRY_LOG_ERRORS_DATE` (`date`)
) ENGINE=InnoDB COMMENT='Log Errors';
```

### 5. ProcessMonitor Module
The ProcessMonitor module does not have its own database table. It works with system processes directly through shell commands, obtaining process information via the `ps aux` command.

## Purpose of Custom Tables

These tables are part of a monitoring system for various aspects of Magento:

1. **lachestry_job_codes_info** - stores information about Cron jobs, including their schedule, module, and group. Allows tracking and managing Cron tasks.

2. **rabbitmq_consumer_activity** - tracks RabbitMQ consumer activity, stores information about status, process ID, and last activity time. Helps control message queues.

3. **lachestry_telegram_chats** - stores information about Telegram chats that are integrated with the system. Used for sending notifications via Telegram.

4. **lachestry_log_errors** - contains information about errors from system logs, including severity level, message, and context. Useful for centralized error monitoring.

## Application in the Project

These tables constitute a comprehensive monitoring system that allows:

1. **Tracking task execution** - monitoring Cron tasks and system processes
2. **Controlling message exchange** - tracking RabbitMQ consumer activity
3. **Processing errors** - centralized collection and analysis of errors from logs
4. **Configuring notifications** - sending notifications via Telegram

## Practical Benefits

This database structure provides:
- Prompt detection of problems in system operation
- Prevention of downtime through monitoring of critical components
- Quick response to errors thanks to centralized logging
- Optimization of resource usage through process monitoring

This architecture is extensible and can be supplemented to monitor other aspects of Magento's operation. 
