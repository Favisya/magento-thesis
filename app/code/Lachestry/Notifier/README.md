# Notifier Module for Magento 2

The Notifier module is designed to monitor and notify about errors in various Magento 2 system processes, including:

- Logger errors
- Indexer errors
- Cron job errors
- Message queue errors
- REST API errors

## Functionality

This module integrates with the Telegram module to send notifications about errors to Telegram chats. When an error occurs, the module:

1. Logs the error in Magento's standard logs
2. Formats the error message for sending to Telegram
3. Sends a notification to all active Telegram chats through the Lachestry_Telegram module

## Architecture

The module uses Magento 2's Plugin pattern to intercept errors in various system components. The main plugins are:

- `NotifyAllErrors` - intercepts all logger errors
- `NotifyIndexerErrors` - tracks errors in indexing processes
- `NotifyCronErrors` - intercepts cron job errors
- `NotifyQueueErrors` - monitors errors in message queues
- `NotifyApiErrors` - tracks REST API errors

Centralized error handling is performed through the `ErrorHandler` class, which formats messages and sends them to Telegram.

## Dependencies

The module has the following dependencies:
- Lachestry_Telegram - for sending notifications

## Message Format

Telegram messages include the following information:
- Error type and source
- Timestamp
- Error message
- File and line where the error occurred
- Contextual information depending on the error type
- Abbreviated stack trace for diagnostics 