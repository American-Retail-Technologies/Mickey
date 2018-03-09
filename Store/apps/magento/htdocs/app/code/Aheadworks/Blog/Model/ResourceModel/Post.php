<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Post resource model
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class Post extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_blog_post', 'id');
    }

    /**
     * Load post by url key
     *
     * @param \Aheadworks\Blog\Model\Post $post
     * @param string $urlKey
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByUrlKey(\Aheadworks\Blog\Model\Post $post, $urlKey)
    {
        $connection = $this->getConnection();
        $bind = ['url_key' => $urlKey];
        $select = $connection->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where(
                'url_key = :url_key'
            );

        $postId = $connection->fetchOne($select, $bind);
        if ($postId) {
            $this->load($post, $postId);
        } else {
            $post->setData([]);
        }

        return $this;
    }
}
