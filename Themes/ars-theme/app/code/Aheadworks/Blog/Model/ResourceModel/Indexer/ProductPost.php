<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel\Indexer;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Indexer\Model\ResourceModel\AbstractResource;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Catalog\Model\Product;
use Aheadworks\Blog\Model\Post;

/**
 * Class ProductPost
 * @package Aheadworks\Blog\Model\ResourceModel\Indexer
 */
class ProductPost extends AbstractResource implements IdentityInterface
{
    /**
     * @var int
     */
    const INSERT_PER_QUERY = 500;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @param Context $context
     * @param StrategyInterface $tableStrategy
     * @param PostRepositoryInterface $postRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param EventManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        PostRepositoryInterface $postRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        EventManagerInterface $eventManager,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $connectionName);
        $this->postRepository = $postRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Define main product post index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_blog_product_post', 'product_id');
    }

    /**
     * Reindex all product post data
     *
     * @return $this
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->clearTemporaryIndexTable();
        $this->beginTransaction();
        try {
            $toInsert = $this->prepareProductPostData();
            $this->prepareInsertToTable($toInsert);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        $this->syncData();
        $this->dispatchCleanCacheByTags($toInsert);
        return $this;
    }

    /**
     * Reindex product post data for defined ids
     *
     * @param array|int $ids
     * @return $this
     */
    public function reindexRows($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $toUpdate = $this->prepareProductPostData($ids);
        $this->beginTransaction();
        try {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['post_id IN (?)' => $ids]
            );
            $this->prepareInsertToTable($toUpdate, false);
            $this->commit();
            $this->dispatchCleanCacheByTags($toUpdate);
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Dispatch clean_cache_by_tags event
     *
     * @param array $entities
     * @return void
     */
    private function dispatchCleanCacheByTags($entities = [])
    {
        $this->entities = $entities;
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
    }

    /**
     * Prepare and return data for insert to index table
     *
     * @param array|null $entityIds
     * @return array
     */
    private function prepareProductPostData($entityIds = null)
    {
        if ($entityIds) {
            $this->searchCriteriaBuilder->addFilter(PostInterface::ID, ['in' => $entityIds]);
        }
        $postList = $this->postRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $data = [];
        foreach ($postList as $post) {
            // If conditions is set
            if ($post->getProductRule()->getConditions()->getConditions()) {
                foreach ($this->storeManager->getStores() as $store) {
                    if (in_array($store->getId(), $post->getStoreIds()) || in_array(0, $post->getStoreIds())) {
                        foreach ($post->getProductRule()->getMatchingProductIds($store->getId()) as $productId) {
                            $data[] = [
                                'product_id' => $productId,
                                'post_id'    => $post->getId(),
                                'store_id'   => $store->getId()
                            ];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Prepare data and partial insert to index or main table
     *
     * @param array $data
     * @param bool|true $intoIndexTable
     * @return $this
     */
    private function prepareInsertToTable($data, $intoIndexTable = true)
    {
        $counter = 0;
        $toInsert = [];
        foreach ($data as $row) {
            $counter++;
            $toInsert[] = $row;
            if ($counter % self::INSERT_PER_QUERY == 0) {
                $this->insertToTable($toInsert, $intoIndexTable);
                $toInsert = [];
            }
        }
        $this->insertToTable($toInsert, $intoIndexTable);
        return $this;
    }

    /**
     * Insert to index table
     *
     * @param array $toInsert
     * @param bool|true $intoIndexTable
     * @return $this
     */
    private function insertToTable($toInsert, $intoIndexTable = true)
    {
        $table = $intoIndexTable
            ? $this->getTable($this->getIdxTable())
            : $this->getMainTable();
        if (count($toInsert)) {
            $this->getConnection()->insertMultiple(
                $table,
                $toInsert
            );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [Product::CACHE_TAG];
        foreach ($this->entities as $entitie) {
            $postTag = Post::CACHE_TAG . '_' . $entitie['post_id'];
            if (false === array_search($postTag, $identities)) {
                $identities[] = $postTag;
            }
        }
        return $identities;
    }
}
