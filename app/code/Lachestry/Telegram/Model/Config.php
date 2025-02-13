<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    protected const TOKEN      = 'token';
    protected const TEXT       = 'text';
    protected const BASE_PATH  = 'lachestry_telegram/general/';
    protected const SEND_MSG   = 'send_method';
    protected const GET_MSG    = 'get_method';
    protected const BASE_URL   = 'base_url';
    protected const IS_ENABLED = 'is_enabled';

    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::BASE_PATH . self::IS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getToken(): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_PATH . self::TOKEN, ScopeInterface::SCOPE_STORE);
    }

    public function getSendMsgMethod(): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_PATH . self::SEND_MSG, ScopeInterface::SCOPE_STORE);
    }

    public function getGetMsgMethod(): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_PATH . self::GET_MSG, ScopeInterface::SCOPE_STORE);
    }

    public function getBaseUrl(): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_PATH . self::BASE_URL, ScopeInterface::SCOPE_STORE);
    }
}
