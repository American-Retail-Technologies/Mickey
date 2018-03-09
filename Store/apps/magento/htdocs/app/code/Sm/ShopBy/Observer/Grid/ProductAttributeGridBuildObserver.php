<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.0.2
 # Copyright (c) 2016 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ShopBy\Observer\Grid;

use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;

class ProductAttributeGridBuildObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Sm_ShopBy')) {
            return;
        }

        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $grid */
        $grid = $observer->getGrid();

        $grid->addColumnAfter(
            'is_filterable',
            [
                    'header' => __('Use in Layered Navigation'),
                    'sortable' => true,
                    'index' => 'is_filterable',
                    'type' => 'options',
                    'options' => [
                        '1' => __('Filterable (with results)'),
                        '2' => __('Filterable (no results)'),
                        '0' => __('No'),
                    ],
                    'align' => 'center',
            ],
            'is_searchable'
        );
    }
}
