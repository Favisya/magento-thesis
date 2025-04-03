<?php
declare(strict_types=1);

namespace Lachestry\ProcessMonitor\Model\ResourceModel\Process\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;
use Lachestry\ProcessMonitor\Model\Process;

class Collection extends SearchResult
{
    protected $process;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        Process $process,
        $mainTable = 'process_listing',
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->process = $process;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('pid', 'main_table.pid');
        $this->addFilterToMap('user', 'main_table.user');
        $this->addFilterToMap('command', 'main_table.command');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('cpu', 'main_table.cpu');
        $this->addFilterToMap('memory', 'main_table.memory');
        $this->addFilterToMap('started', 'main_table.started');

        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $processes = $this->process->getProcessList();
            foreach ($processes as $process) {
                $this->_addItem($process);
            }
            $this->_setIsLoaded();
        }
        return $this;
    }
} 