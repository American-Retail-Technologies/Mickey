<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel\Post\Grid;

use Aheadworks\Blog\Api\CommentsServiceInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Aheadworks\Blog\Model\ResourceModel\Post\Collection as PostCollection;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Store\Model\Store;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Collection for displaying grid of blog posts
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends PostCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var CommentsServiceInterface
     */
    private $commentsService;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param CommentsServiceInterface $commentsService
     * @param MetadataPool $metadataPool
     * @param string $mainTable
     * @param string $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        CommentsServiceInterface $commentsService,
        MetadataPool $metadataPool,
        $mainTable,
        $resourceModel,
        $model = Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $dateTime,
            $metadataPool,
            $connection,
            $resource
        );
        $this->commentsService = $commentsService;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = parent::getItems();
        $postIds = $this->getIds();
        $publishedComments = $this->commentsService->getPublishedCommNumBundle($postIds, Store::DEFAULT_STORE_ID);
        $newComments = $this->commentsService->getNewCommNumBundle($postIds, Store::DEFAULT_STORE_ID);
        foreach ($items as $item) {
            $postId = $item->getData('id');
            $item->setData('published_comments', $publishedComments[$postId]);
            $item->setData('new_comments', $newComments[$postId]);
        }
        return $items;
    }

    /**
     * Get items IDs
     *
     * @return array
     */
    private function getIds()
    {
        $ids = [];
        foreach ($this->_items as $item) {
            $ids[] = $this->_getItemId($item);
        }
        return $ids;
    }
}
