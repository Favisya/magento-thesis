<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Plugin;

use Magento\Framework\MessageQueue\CallbackInvoker;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivityFactory;
use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity as ConsumerActivityResource;
use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity\CollectionFactory as ConsumerActivityCollectionFactory;

class ConsumerActivityUpdate
{
    protected ConsumerActivityFactory $consumerActivityFactory;
    protected ConsumerActivityResource $consumerActivityResource;
    protected ConsumerActivityCollectionFactory $collectionFactory;

    public function __construct(
        ConsumerActivityFactory $consumerActivityFactory,
        ConsumerActivityResource $consumerActivityResource,
        ConsumerActivityCollectionFactory $collectionFactory
    ) {
        $this->consumerActivityFactory = $consumerActivityFactory;
        $this->consumerActivityResource = $consumerActivityResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function aroundExecute(
        CallbackInvoker $subject,
        callable $proceed,
        callable $callback,
        array $arguments
    ) {
        $consumerName = $this->getConsumerNameFromStackTrace();
        
        if ($consumerName) {
            $this->updateConsumerActivity($consumerName);
        }
        
        $result = $proceed($callback, $arguments);
        
        return $result;
    }
    
    protected function getConsumerNameFromStackTrace(): ?string
    {
        $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        foreach ($stackTrace as $trace) {
            if (isset($trace['class']) && 
                $trace['class'] === 'Magento\Framework\MessageQueue\Consumer\ConfigInterface' && 
                isset($trace['object']) && 
                method_exists($trace['object'], 'getName')
            ) {
                return $trace['object']->getName();
            }
            
            if (isset($trace['args']) && !empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_object($arg) && method_exists($arg, 'getName')) {
                        $name = $arg->getName();
                        if (strpos($name, ':') !== false) {
                            return $name;
                        }
                    }
                }
            }
        }
        
        $pid = getmypid();
        if ($pid) {
            return $this->getConsumerNameByPid($pid);
        }
        
        return null;
    }
    
    protected function getConsumerNameByPid(int $pid): ?string
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('pid', $pid);
        
        $consumer = $collection->getFirstItem();
        if ($consumer->getId()) {
            return $consumer->getConsumerName();
        }
        
        return null;
    }
    
    protected function updateConsumerActivity(string $consumerName): void
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('consumer_name', $consumerName);
            
            $consumer = $collection->getFirstItem();
            if (!$consumer->getId()) {
                $consumer = $this->consumerActivityFactory->create();
                $consumer->setConsumerName($consumerName);
                $consumer->setStatus('Running');
                $consumer->setPid(getmypid());
            }
            
            $consumer->setLastActivity(date('Y-m-d H:i:s'));
            $this->consumerActivityResource->save($consumer);
        } catch (\Exception $e) {
        }
    }
}