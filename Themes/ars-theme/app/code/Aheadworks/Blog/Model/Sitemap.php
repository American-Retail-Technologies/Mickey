<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model;

use Magento\Framework\DataObject;

/**
 * Class Sitemap
 * @package Aheadworks\Blog\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * {@inheritdoc}
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();
        $this->_eventManager->dispatch('aw_sitemap_items_init', ['object' => $this]);
    }

    /**
     * Add sitemap item
     *
     * @param DataObject $item
     * @return $this
     */
    public function appendSitemapItem(DataObject $item)
    {
        $this->_sitemapItems[] = $item;
        return $this;
    }
}
