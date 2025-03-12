<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Api;

use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * Plugin to catch and notify about API errors
 */
class NotifyApiErrors
{
    /**
     * @param ErrorHandler $errorHandler
     * @param Config $config
     */
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config $config
    ) {
    }

    /**
     * Intercept REST API error processing
     *
     * @param ErrorProcessor $subject
     * @param \Throwable $exception
     * @return \Throwable
     */
    public function beforeMaskException(
        ErrorProcessor $subject,
        \Throwable $exception
    ): \Throwable {
        if (!$this->config->isApiNotificationEnabled()) {
            return $exception;
        }

        try {
            $additionalData = [];
            
            if ($exception instanceof WebapiException) {
                $additionalData['http_code'] = $exception->getHttpCode();
            }
            
            $isClientError = $exception instanceof WebapiException && 
                            ($exception->getHttpCode() >= 400 && $exception->getHttpCode() < 500);
                            
            if (!$isClientError) {
                $this->errorHandler->handleError($exception, 'rest_api', $additionalData);
            }
        } catch (\Throwable $e) {
            // Do nothing to avoid interrupting the main process
        }
        
        return $exception;
    }
} 