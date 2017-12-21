<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Observer;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Sitemap;
use Aheadworks\Blog\Model\Sitemap\ItemsProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddSitemapItemsObserver
 * @package Aheadworks\Blog\Observer
 */
class AddSitemapItemsObserver implements ObserverInterface
{
    /**
     * @var ItemsProvider
     */
    private $itemsProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ItemsProvider $itemsProvider
     * @param Config $config
     */
    public function __construct(
        ItemsProvider $itemsProvider,
        Config $config
    ) {
        $this->itemsProvider = $itemsProvider;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Sitemap $sitemap */
        $sitemap = $event->getObject();
        $storeId = $sitemap->getStoreId();
        if ($this->config->isBlogEnabled($storeId)) {
            $sitemap
                ->appendSitemapItem($this->itemsProvider->getBlogItem($storeId))
                ->appendSitemapItem($this->itemsProvider->getCategoryItems($storeId))
                ->appendSitemapItem($this->itemsProvider->getPostItems($storeId));
        }
    }
}
