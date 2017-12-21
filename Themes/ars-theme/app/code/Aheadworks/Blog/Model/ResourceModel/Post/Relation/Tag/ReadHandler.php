<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel\Post\Relation\Tag;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Blog\Model\ResourceModel\Post\Relation\Tag
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(PostInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from(['tag' => $this->resourceConnection->getTableName('aw_blog_tag')], 'name')
                ->joinLeft(
                    ['tag_post' => $this->resourceConnection->getTableName('aw_blog_post_tag')],
                    'tag.id = tag_post.tag_id',
                    []
                )->where('tag_post.post_id = :id', $entityId);
            $tagNames = $connection->fetchCol($select, ['id' => $entityId]);
            $entity->setTagNames($tagNames);
        }
        return $entity;
    }
}
