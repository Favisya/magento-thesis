<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\ViewModel;

use Lachestry\CronMonitoring\Api\CronGroupRepositoryInterface;
use Lachestry\CronMonitoring\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

class CronCard implements ArgumentInterface
{
    protected const ROUTE_PATH = 'cron_monitoring/cron/view/';

    protected readonly UrlInterface $urlBuilder;
    protected readonly Config $config;
    protected array $cronGroupData;

    public function __construct(
        UrlInterface $urlBuilder,
        Config       $config,
        CronGroupRepositoryInterface $cronGroupRepository
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->cronGroupData = $cronGroupRepository->getGroupsData();
    }

    public function getGroupsSettings(string $groupName): array
    {
        return $this->cronGroupData[$groupName]['settings'] ?? [];
    }

    public function getGroupCodes(string $groupName): array
    {
        $codes = $this->cronGroupData[$groupName]['codes'] ?? [];
        $rowsInCard = $this->getRowsInCard();

        if (count($codes) < $rowsInCard) {
            $emptyElementsToAdd = $rowsInCard - count($codes);
            for ($i = 0; $i < $emptyElementsToAdd; $i++) {
                $codes[] = ['name' => ''];
            }
        }
        return $codes;
    }

    public function getUrl(string $groupName): string
    {
        return $this->urlBuilder->getUrl(self::ROUTE_PATH, ['group' => $groupName]);
    }

    public function getRowsInCard(): int
    {
        return $this->config->getRowsInCard();
    }

    public function getListHeigth(): float
    {
        return $this->config->getRowHeight() * $this->getRowsInCard();
    }
}
