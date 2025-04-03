<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\LogMonitor\Model\ResourceModel\LogError\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ChartDataProvider
{
    private CollectionFactory $collectionFactory;
    private DateTime $dateTime;
    private TimezoneInterface $timezone;

    public function __construct(
        CollectionFactory $collectionFactory,
        DateTime          $dateTime,
        TimezoneInterface $timezone,
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime          = $dateTime;
        $this->timezone          = $timezone;
    }

    public function getChartData(string $from = null, string $to = null): array
    {
        $collection = $this->collectionFactory->create();

        if ($from) {
            $collection->addFieldToFilter('date', ['from' => $from]);
        }

        if ($to) {
            $collection->addFieldToFilter('date', ['to' => $to]);
        }

        $collection->setOrder('date', 'ASC');

        $errors = $collection->getItems();

        $dateLabels     = [];
        $severityCounts = [
            'debug'     => [],
            'info'      => [],
            'notice'    => [],
            'warning'   => [],
            'error'     => [],
            'critical'  => [],
            'alert'     => [],
            'emergency' => [],
        ];

        $dateErrors = [];

        foreach ($errors as $error) {
            $date = $this->dateTime->date('Y-m-d', strtotime($error->getDate()));

            if (!isset($dateErrors[$date])) {
                $dateErrors[$date] = [
                    'debug'     => 0,
                    'info'      => 0,
                    'notice'    => 0,
                    'warning'   => 0,
                    'error'     => 0,
                    'critical'  => 0,
                    'alert'     => 0,
                    'emergency' => 0,
                ];
            }

            $severity = strtolower($error->getSeverity());
            if (isset($dateErrors[$date][$severity])) {
                $dateErrors[$date][$severity]++;
            }
        }

        ksort($dateErrors);

        foreach ($dateErrors as $date => $counts) {
            $dateLabels[] = $date;

            foreach ($counts as $severity => $count) {
                $severityCounts[$severity][] = $count;
            }
        }

        return [
            'labels'   => $dateLabels,
            'datasets' => $severityCounts,
        ];
    }
}
