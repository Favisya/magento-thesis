<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Block\Adminhtml;

use Lachestry\CronMonitoring\Api\CronGroupRepositoryInterface;
use Magento\Framework\View\Element\Template;

class CronCardDesk extends Template
{
    protected CronGroupRepositoryInterface $cronGroupRepository;

    public function __construct(
        Template\Context $context,
        CronGroupRepositoryInterface $cronGroupRepository,
        array $data = []
    ) {
        $this->cronGroupRepository = $cronGroupRepository;
        parent::__construct($context, $data);
    }

    public function getGroupNames(): array
    {
        return $this->cronGroupRepository->getGroupNames();
    }
}
