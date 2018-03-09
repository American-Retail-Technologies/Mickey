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

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\History;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected $_attributeMetadataDataProvider;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Plumrocket\Newsletterpopup\Model\History', 'Plumrocket\Newsletterpopup\Model\ResourceModel\History');
    }

    public function addCustomerNameToSelect()
    {
        $firstnameId = $this->_attributeMetadataDataProvider->getAttribute('customer', 'firstname')->getId();
        $lastnameId = $this->_attributeMetadataDataProvider->getAttribute('customer', 'lastname')->getId();

        $this->getSelect()
            ->joinLeft(
                ['ce1' => $this->getTable('customer_entity_varchar')],
                '`ce1`.`entity_id` = `main_table`.`customer_id` AND `ce1`.`attribute_id` = ' . $firstnameId,
                ['firstname' => 'value']
            )
            ->joinLeft(
                ['ce2' => $this->getTable('customer_entity_varchar')],
                '`ce2`.`entity_id` = `main_table`.`customer_id` AND `ce2`.`attribute_id` = ' . $lastnameId,
                ['lastname' => 'value']
            )
            // check if customer exists, because firstname and lastname will not every exists
            ->joinLeft(
                ['ce' => $this->getTable('customer_entity')],
                '`ce`.`entity_id` = `main_table`.`customer_id`',
                ['cid' => 'entity_id']
            )
            ->columns(new \Zend_Db_Expr('CONCAT(`ce1`.`value`, " ", `ce2`.`value`) AS customer_name'));

        return $this;
    }

    public function addNameFilter($value)
    {
        if (is_numeric($value)) {
            $this->getSelect()->where("`customer_id` = {$value}");
        } else {
            $inputKeywords = explode(' ', $value);
            $select = [];
            foreach ($inputKeywords as $keyword) {
                $select[] = "CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) LIKE '%{$keyword}%'";
            }
            $this->getSelect()->where(implode(' AND ', $select));
        }
        return $this;
    }

    public function addActionTextToResult()
    {
        $this->getSelect()
            ->joinLeft(
                ['ha' => $this->getResource()->getActionMainTable()],
                '`ha`.`id` = `main_table`.`action_id`',
                new \Zend_Db_Expr('IFNULL(`ha`.`text`, `main_table`.`action`) AS "action"')
            );

        return $this;
    }
}
