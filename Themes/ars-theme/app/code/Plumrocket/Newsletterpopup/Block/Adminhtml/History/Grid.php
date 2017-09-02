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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\History;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\System\Store as SystemStore;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\History;
use Plumrocket\Newsletterpopup\Model\Popup;

class Grid extends Extended
{
    protected $_dataHelper;
    protected $_popup;
    protected $_history;
    protected $_systemStore;
    protected $_groupCollection;

    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Data $dataHelper,
        Popup $popup,
        History $history,
        SystemStore $systemStore,
        GroupCollection $groupCollection,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_popup = $popup;
        $this->_history = $history;
        $this->_systemStore = $systemStore;
        $this->_groupCollection = $groupCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('manage_prnewsletterpopup_history_grid');
        $this->setDefaultSort('date_created');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_history
            ->getCollection()
            ->addCustomerNameToSelect()
            ->addActionTextToResult()
            ->addFilterToMap('action', new \Zend_Db_Expr("IFNULL(`ha`.`text`, `main_table`.`action`)"));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('popup_id', [
            'header'    => __('Popup ID'),
            'index'     => 'popup_id',
            'type'      => 'text',
            'width'     => '3%',
        ]);

        $items = $this->_popup->getCollection();
        $popups = [];
        foreach ($items as $item) {
            $popups[ $item->getId() ] = $item->getName();
        }
        $this->addColumn('popup_name', [
            'header'    => __('Popup Name'),
            'index'     => 'popup_id',
            'type'      =>  'options',
            'options'   =>  $popups,
            'width'     => '10%',
        ]);

        $this->addColumn('customer_name', [
            'header'    => __('Customer Name'),
            'index'     => 'customer_name',
            'type'      => 'text',
            'width'     => '10%',
            'filter_condition_callback' => [$this, '_customerNameCondition'],
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\History\Renderer\Name',
        ]);

        $this->addColumn('customer_email', [
            'header'    => __('Email'),
            'index'     => 'customer_email',
            'type'      => 'text',
            'width'     => '10%',
        ]);

        $this->addColumn('customer_group', [
            'header'    =>  __('Customer Group'),
            'width'     =>  '5%',
            'index'     =>  'customer_group',
            'type'      =>  'options',
            'options'   =>  $this->_groupCollection->load()->toOptionHash()
        ]);

        $this->addColumn('customer_ip', [
            'header'    => __('Customer IP'),
            'index'     => 'customer_ip',
            'type'      => 'text',
            'width'     => '6%',
        ]);

        $this->addColumn('landing_page', [
            'header'    => __('Landing Page'),
            'index'     => 'landing_page',
            'type'      => 'text',
            'width'     => '20%',
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'index'     => 'action',
            'type'      => 'text',
            // 'options'   => Mage::getSingleton('newsletterpopup/values_action')->toOptionHash(),
            'width'     => '5%',
        ]);

        $this->addColumn('coupon_code', [
            'header'    => __('Coupon Code'),
            'index'     => 'coupon_code',
            'type'      => 'text',
            'width'     => '6%',
        ]);

        $this->addColumn('order_id', [
            'header'    => __('Order #'),
            'index'     => 'order_id',
            'type'      => 'text',
            'width'     => '6%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\History\Renderer\Order',
        ]);

        $this->addColumn('grand_total', [
            'header'    => __('Order G.T.'),
            'index'     => 'grand_total',
            'type'      => 'price',
            'currency_code' => (string)$this->_dataHelper->getConfig(Currency::XML_PATH_CURRENCY_BASE),
            'width'     => '4%',
            'frame_callback' => [$this, 'decoratePrice'],
        ]);

        $this->addColumn('date_created', [
            'header'    => __('Datetime'),
            'index'     => 'date_created',
            'type'      => 'datetime',
            'width'     => '6%',
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header'    =>  __('Store View'),
                'width'     =>  '6%',
                'index'     =>  'store_id',
                'type'      =>  'options',
                'options'   =>  $this->_systemStore->getStoreOptionHash(),
                'filter_index'  => 'main_table.store_id',
            ]);
        }

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('Excel XML'));
        return parent::_prepareColumns();
    }

    public function decoratePrice($value, $row, $column, $isExport)
    {
        if ((int)$row->getGrandTotal() === 0) {
            return '';
        }
        return $value;
    }

    protected function _customerNameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addNameFilter($value);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('history_id');
        $this->getMassactionBlock()
            ->addItem('delete', [
                'label'     => __('Delete'),
                'url'       => $this->getUrl('*/*/mass', ['action' => 'delete'])
            ]);
        return $this;
    }
}
