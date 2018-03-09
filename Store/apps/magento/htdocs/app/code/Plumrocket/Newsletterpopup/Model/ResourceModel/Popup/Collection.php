<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Template;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected $_templateResource;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Template $templateResource,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_templateResource = $templateResource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Plumrocket\Newsletterpopup\Model\Popup', 'Plumrocket\Newsletterpopup\Model\ResourceModel\Popup');
    }

    public function addStoreFilter($store)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }

        if (is_array($store)) {
            $store = $store[0];
        }

        $this->getSelect()->where("FIND_IN_SET('{$store}', `store_id`) OR FIND_IN_SET('0', `store_id`)");

        return $this;
    }

    public function addTemplateData()
    {
        $this->join(['t' => $this->_templateResource->getMainTable()], 't.entity_id = main_table.template_id', ['name as template_name', 'base_template_id', 'code', 'style']);
        $this->getSelect()
            ->joinLeft(['t2' => $this->_templateResource->getMainTable()], 't2.entity_id = t.base_template_id', ['base_template_name' => 'name']);

        return $this;
    }
}
