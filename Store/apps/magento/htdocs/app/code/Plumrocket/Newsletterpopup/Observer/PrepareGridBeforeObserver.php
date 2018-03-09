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

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Block\Adminhtml\Subscriber\Grid;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Plumrocket\Newsletterpopup\Helper\Data;

class PrepareGridBeforeObserver implements ObserverInterface
{
    /**
     * @var string[]
     */
    protected $_columnsOrder = [
        'subscriber_prefix' => 'type',
        'subscriber_middlename' => 'firstname',
        'subscriber_suffix' => 'lastname',

        'subscriber_postcode' => 'status',
        'subscriber_street' => 'status',
        'subscriber_city' => 'status',
        'subscriber_region' => 'status',
        'subscriber_country_id' => 'status',
        'subscriber_company' => 'status',
        'subscriber_fax' => 'status',
        'subscriber_telephone' => 'status',
        'subscriber_taxvat' => 'status',
        'subscriber_gender' => 'status',
        'subscriber_dob' => 'status',
    ];

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var CustomerResource
     */
    protected $_resourceCustomer;

    /**
     * @var Country
     */
    protected $_directoryCountry;

    /**
     * @var SubscriberCollectionFactory
     */
    protected $_subscriberCollectionFactory;

    /**
     * @param Data                        $dataHelper
     * @param CustomerResource            $resourceCustomer
     * @param Country                     $directoryCountry
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     */
    public function __construct(
        Data $dataHelper,
        CustomerResource $resourceCustomer,
        Country $directoryCountry,
        SubscriberCollectionFactory $subscriberCollectionFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_resourceCustomer = $resourceCustomer;
        $this->_directoryCountry = $directoryCountry;
        $this->_subscriberCollectionFactory = $subscriberCollectionFactory;
    }

    /**
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->_dataHelper->moduleEnabled()) {
            return;
        }

        $grid = $observer->getEvent()->getGrid();

        if (! $grid instanceof Grid) {
            return;
        }

        foreach ($this->_columnsOrder as $columnId => $after) {
            if (! $grid->getColumn($columnId)) {
                $this->_processAdditionalField($grid, $columnId);
            }
        }

        // If standard firstname and lastname is empty then use our columns
        $grid->getCollection()
            ->addFilterToMap('firstname', new \Zend_Db_Expr('IFNULL(`customer`.`firstname`, `main_table`.`subscriber_firstname`)'))
            ->addFilterToMap('lastname', new \Zend_Db_Expr('IFNULL(`customer`.`lastname`, `main_table`.`subscriber_lastname`)'));

        $grid->getCollection()->getSelect()
            ->columns('IF(`main_table`.`customer_id` = 0, `main_table`.`subscriber_firstname`, `customer`.`firstname`) AS firstname')
            ->columns('IF(`main_table`.`customer_id` = 0, `main_table`.`subscriber_lastname`, `customer`.`lastname`) AS lastname');

        $this->_sortColumnsByOrder($grid);
    }

    /**
     * Process to add the field to grid
     *
     * @param  Grid $grid
     * @param  string $field
     * @return boolean
     */
    private function _processAdditionalField(Grid $grid, $field)
    {
        if (! $this->_showColumn($field)) {
            return false;
        }

        switch ($field) {
            case 'subscriber_middlename':
                $this->_addColumn($grid, 'subscriber_middlename', [
                    'header'    => __('Middle Name'),
                    'index'     => 'subscriber_middlename',
                    'default'   => '----'
                ]);
                break;
            case 'subscriber_suffix':
                $this->_addColumn($grid, 'subscriber_suffix', [
                    'header'    => __('Suffix'),
                    'index'     => 'subscriber_suffix',
                ]);
                break;
            case 'subscriber_dob':
                $this->_addColumn($grid, 'subscriber_dob', [
                    'header'    => __('Date of Birth'),
                    'index'     => 'subscriber_dob',
                    'type'      => 'date',
                ]);
                break;
            case 'subscriber_gender':
                $options = $this->_resourceCustomer
                        ->getAttribute('gender')
                        ->getSource()
                        ->getAllOptions(false);

                $this->_addColumn($grid, 'subscriber_gender', [
                    'header'    => __('Gender'),
                    'index'     => 'subscriber_gender',
                    'type'      => 'options',
                    'options'   => $this->_getOptions($options),
                ]);
                break;
            case 'subscriber_taxvat':
                $this->_addColumn($grid, 'subscriber_taxvat', [
                    'header'    => __('Tax/VAT Number'),
                    'index'     => 'subscriber_taxvat',
                ]);
                break;
            case 'subscriber_prefix':
                $this->_addColumn($grid, 'subscriber_prefix', [
                    'header'    => __('Prefix'),
                    'index'     => 'subscriber_prefix',
                ]);
                break;
            case 'subscriber_telephone':
                $this->_addColumn($grid, 'subscriber_telephone', [
                    'header'    => __('Telephone'),
                    'index'     => 'subscriber_telephone'
                ]);
                break;
            case 'subscriber_fax':
                $this->_addColumn($grid, 'subscriber_fax', [
                    'header'    => __('Fax'),
                    'index'     => 'subscriber_fax'
                ]);
                break;
            case 'subscriber_company':
                $this->_addColumn($grid, 'subscriber_company', [
                    'header'    => __('Company'),
                    'index'     => 'subscriber_company'
                ]);
                break;
            case 'subscriber_street':
                $this->_addColumn($grid, 'subscriber_street', [
                    'header'    => __('Street'),
                    'index'     => 'subscriber_street'
                ]);
                break;
            case 'subscriber_city':
                $this->_addColumn($grid, 'subscriber_city', [
                    'header'    => __('City'),
                    'index'     => 'subscriber_city'
                ]);
                break;
            case 'subscriber_country_id':
                $options = $this->_directoryCountry->toOptionArray();
                unset($options[0]);

                $this->_addColumn($grid, 'subscriber_country_id', [
                    'header'    => __('Country'),
                    'index'     => 'subscriber_country_id',
                    'type'      => 'options',
                    'options'   => $this->_getOptions($options),
                ]);
                break;
            case 'subscriber_region':
                $this->_addColumn($grid, 'subscriber_region', [
                    'header'    => __('Region'),
                    'index'     => 'subscriber_region'
                ]);
                break;
            case 'subscriber_postcode':
                $this->_addColumn($grid, 'subscriber_postcode', [
                    'header'    => __('Postcode'),
                    'index'     => 'subscriber_postcode'
                ]);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Add column to grid
     *
     * @param   Grid $grid
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @return  void
     * @throws  \Exception
     */
    protected function _addColumn(Grid $grid, $columnId, $column)
    {
        if (is_array($column)) {
            $grid->getColumnSet()->setChild(
                $columnId,
                $grid->getLayout()
                    ->createBlock('Magento\Backend\Block\Widget\Grid\Column\Extended')
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($grid)
            );
            $grid->getColumnSet()->getChildBlock($columnId)->setGrid($grid);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }
    }

    /**
     * Sort columns by predefined order
     *
     * @param  Grid $grid
     * @return void
     */
    protected function _sortColumnsByOrder($grid)
    {
        foreach ($this->_columnsOrder as $columnId => $after) {
            if (! $after || ! $grid->getColumn($columnId) || ! $grid->getColumn($after)) {
                continue;
            }

            $grid->getLayout()->reorderChild(
                $grid->getColumnSet()->getNameInLayout(),
                $grid->getColumn($columnId)->getNameInLayout(),
                $grid->getColumn($after)->getNameInLayout()
            );
        }
    }

    /**
     * Check if need to show column in the grid
     *
     * @param  string $field
     * @return boolean
     */
    protected function _showColumn($field)
    {
        $size = $this->_subscriberCollectionFactory->create()
            ->addFieldToFilter($field, ['neq' => ''])
            ->addFieldToFilter($field, ['neq' => '0000-00-00'])
            ->getSize();

        return $size > 0;
    }

    /**
     * Convert OptionsValue array to Options array
     *
     * @param array $optionsArray
     * @return array
     */
    protected function _getOptions($optionsArray)
    {
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
