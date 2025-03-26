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
        DateTime $dateTime,
        TimezoneInterface $timezone
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
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
        
        $dateLabels = [];
        $severityCounts = [
            'debug' => [],
            'info' => [],
            'notice' => [],
            'warning' => [],
            'error' => [],
            'critical' => [],
            'alert' => [],
            'emergency' => []
        ];
        
        $dateErrors = [];
        
        foreach ($errors as $error) {
            $date = $this->dateTime->date('Y-m-d', strtotime($error->getDate()));
            
            if (!isset($dateErrors[$date])) {
                $dateErrors[$date] = [
                    'debug' => 0,
                    'info' => 0,
                    'notice' => 0,
                    'warning' => 0,
                    'error' => 0,
                    'critical' => 0,
                    'alert' => 0,
                    'emergency' => 0
                ];
            }
            
            $severity = $error->getSeverity();
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
            'labels' => $dateLabels,
            'datasets' => [
                [
                    'label' => __('Debug'),
                    'data' => $severityCounts['debug'],
                    'backgroundColor' => 'rgba(192, 192, 192, 0.2)',
                    'borderColor' => 'rgba(192, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Info'),
                    'data' => $severityCounts['info'],
                    'backgroundColor' => 'rgba(93, 173, 226, 0.2)',
                    'borderColor' => 'rgba(93, 173, 226, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Notice'),
                    'data' => $severityCounts['notice'],
                    'backgroundColor' => 'rgba(40, 116, 166, 0.2)',
                    'borderColor' => 'rgba(40, 116, 166, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Warning'),
                    'data' => $severityCounts['warning'],
                    'backgroundColor' => 'rgba(244, 208, 63, 0.2)',
                    'borderColor' => 'rgba(244, 208, 63, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Error'),
                    'data' => $severityCounts['error'],
                    'backgroundColor' => 'rgba(231, 76, 60, 0.2)',
                    'borderColor' => 'rgba(231, 76, 60, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Critical'),
                    'data' => $severityCounts['critical'],
                    'backgroundColor' => 'rgba(176, 58, 46, 0.2)',
                    'borderColor' => 'rgba(176, 58, 46, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Alert'),
                    'data' => $severityCounts['alert'],
                    'backgroundColor' => 'rgba(120, 40, 31, 0.2)',
                    'borderColor' => 'rgba(120, 40, 31, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('Emergency'),
                    'data' => $severityCounts['emergency'],
                    'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
} 