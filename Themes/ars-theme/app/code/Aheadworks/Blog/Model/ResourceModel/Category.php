<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Category resource model
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class Category extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_blog_category', 'id');
    }

    /**
     * Load category by url key
     *
     * @param \Aheadworks\Blog\Model\Category $category
     * @param string $urlKey
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByUrlKey(\Aheadworks\Blog\Model\Category $category, $urlKey)
    {
        $connection = $this->getConnection();
        $bind = ['url_key' => $urlKey];
        $select = $connection->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where('url_key = :url_key');

        $categoryId = $connection->fetchOne($select, $bind);
        if ($categoryId) {
            $this->load($category, $categoryId);
        } else {
            $category->setData([]);
        }

        return $this;
    }
}
