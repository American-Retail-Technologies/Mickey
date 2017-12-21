<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel\Post;

use Aheadworks\Blog\Model\Post;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Collection
 * @package Aheadworks\Blog\Model\ResourceModel\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Aheadworks\Blog\Model\ResourceModel\AbstractCollection
{
    /**
     * @var string
     */
    const IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS = 'is_need_to_attach_related_product_ids';

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EventManager $eventManager
     * @param DateTime $dateTime
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManager $eventManager,
        DateTime $dateTime,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->dateTime = $dateTime;
        $this->metadataPool = $metadataPool;

        $this->setFlag(self::IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Post::class, ResourcePost::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachStores('aw_blog_post_store', 'id', 'post_id');
        $this->attachCategories();
        $this->attachTagNames();
        if ($this->getFlag(self::IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS)) {
            $this->attachRelatedProductIds();
        }
        return parent::_afterLoad();
    }

    /**
     *  Add category filter
     *
     * @param int|array $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if (!is_array($category)) {
            $category = [$category];
        }
        $this->addFilter('category_id', ['in' => $category], 'public');
        return $this;
    }

    /**
     * Add tag filter
     *
     * @param int|array $tag
     * @return $this
     */
    public function addTagFilter($tag)
    {
        if (!is_array($tag)) {
            $tag = [$tag];
        }
        $this->addFilter('tag_id', ['in' => $tag], 'public');
        return $this;
    }

    /**
     * Add related product filter
     *
     * @param int|array $product
     * @return $this
     */
    public function addRelatedProductFilter($product)
    {
        if (!is_array($product)) {
            $product = [$product];
        }
        $this->addFilter('product_id', ['in' => $product], 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreLinkageTable('aw_blog_post_store', 'id', 'post_id');
        $this->joinCategoryLinkageTable();
        $this->joinTagLinkageTable();
        $this->joinRelatedProductLinkageTable();
        parent::_renderFiltersBefore();
    }

    /**
     * Join to category linkage table if category filter is applied
     *
     * @return void
     */
    private function joinCategoryLinkageTable()
    {
        if ($this->getFilter('category_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['category_linkage_table' => $this->getTable('aw_blog_post_category')],
                'main_table.id = category_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
        }
    }

    /**
     * Join to tag linkage table if tag filter is applied
     *
     * @return void
     */
    private function joinTagLinkageTable()
    {
        if ($this->getFilter('tag_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['tag_linkage_table' => $this->getTable('aw_blog_post_tag')],
                'main_table.id = tag_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
        }
    }

    /**
     * Join to product index linkage table if product filter is applied
     *
     * @return void
     */
    private function joinRelatedProductLinkageTable()
    {
        if ($this->getFilter('product_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['product_post_linkage_table' => $this->getTable('aw_blog_product_post')],
                'main_table.id = product_post_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
            if ($storeFilter = $this->getFilter('store_linkage_table.store_id')) {
                $select->where(
                    $this->_getConditionSql('product_post_linkage_table.store_id', $storeFilter->getValue()),
                    null,
                    Select::TYPE_CONDITION
                );
            }
        }
    }

    /**
     * Attach categories data to collection items
     *
     * @return void
     */
    private function attachCategories()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['category_linkage_table' => $this->getTable('aw_blog_post_category')])
                ->where('category_linkage_table.post_id IN (?)', $postIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $categoryIds = [];
                $postId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['post_id'] == $postId) {
                        $categoryIds[] = $data['category_id'];
                    }
                }
                $item->setData('category_ids', $categoryIds);
            }
        }
    }

    /**
     * Attach tag names to collection items
     *
     * @return void
     */
    private function attachTagNames()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['tags_table' => $this->getTable('aw_blog_tag')])
                ->joinLeft(
                    ['tag_post_linkage_table' => $this->getTable('aw_blog_post_tag')],
                    'tags_table.id = tag_post_linkage_table.tag_id',
                    ['post_id' => 'tag_post_linkage_table.post_id']
                )
                ->where('tag_post_linkage_table.post_id IN (?)', $postIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $tagNames = [];
                $postId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['post_id'] == $postId) {
                        $tagNames[] = $data['name'];
                    }
                }
                $item->setData('tag_names', $tagNames);
            }
        }
    }

    /**
     * Attach product ids data to collection items
     *
     * @return void
     */
    private function attachRelatedProductIds()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $productLinkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['product_post_linkage_table' => $this->getTable('aw_blog_product_post')])
                ->joinRight(
                    ['product_entity' => $this->getTable('catalog_product_entity')],
                    'product_post_linkage_table.product_id = product_entity.' . $productLinkField,
                    []
                )->where('product_post_linkage_table.post_id IN (?)', $postIds);
            if ($storeFilter = $this->getFilter('store_linkage_table.store_id')) {
                $select->where(
                    $this->_getConditionSql('product_post_linkage_table.store_id', $storeFilter->getValue()),
                    null,
                    Select::TYPE_CONDITION
                );
            }
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $productIds = [];
                $postId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['post_id'] == $postId) {
                        $productIds[] = $data['product_id'];
                    }
                }
                $item->setData('related_product_ids', $productIds);
            }
        }
    }
}
