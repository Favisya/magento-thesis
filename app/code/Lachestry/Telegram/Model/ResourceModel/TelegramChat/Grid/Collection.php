<?php

declare(strict_types=1);

namespace Lachestry\Telegram\Model\ResourceModel\TelegramChat\Grid;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\Collection as TelegramChatCollection;

class Collection extends TelegramChatCollection implements SearchResultInterface
{
    protected AggregationInterface $aggregations;

    /**
     * Возвращает агрегации
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Устанавливает агрегации
     *
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * Получает критерии поиска (не используется)
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Устанавливает критерии поиска (не используется)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }

    /**
     * Возвращает общее количество элементов
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Устанавливает общее количество элементов (не используется)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Устанавливает элементы (не используется)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
