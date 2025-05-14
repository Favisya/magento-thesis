<?php
declare(strict_types=1);

namespace Lachestry\Configuration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfigValue(string $path, string $scope = 'store', ?int $scopeId = null): string
    {
        return (string)$this->scopeConfig->getValue($path, $scope, $scopeId);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('lachestry/general/enabled');
    }
} 