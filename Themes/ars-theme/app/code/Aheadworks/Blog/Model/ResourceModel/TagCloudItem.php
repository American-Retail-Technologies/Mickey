<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Magento\Store\Model\Store;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class TagCloudItem
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class TagCloudItem extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_blog_tag', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(TagInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Retrieves number of posts corresponding to given tag ID for particular store view
     *
     * @param int $tagId
     * @param int $storeId
     * @return int
     */
    public function getPostCount($tagId, $storeId)
    {
        $select = $this->getConnection()->select()
            ->from(['post_tag_table' => $this->_resources->getTableName('aw_blog_post_tag')], [])
            ->joinLeft(
                ['post_store_table' => $this->_resources->getTableName('aw_blog_post_store')],
                'post_tag_table.post_id = post_store_table.post_id',
                []
            )
            ->where('post_tag_table.tag_id = ?', $tagId)
            ->where('post_store_table.store_id IN (?)', [$storeId, Store::DEFAULT_STORE_ID])
            ->columns(
                [TagCloudItemInterface::POST_COUNT => new \Zend_Db_Expr('COUNT(post_tag_table.post_id)')]
            );
        return (int)$this->getConnection()->fetchOne($select);
    }
}
