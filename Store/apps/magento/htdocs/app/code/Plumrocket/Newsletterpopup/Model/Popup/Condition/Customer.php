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

namespace Plumrocket\Newsletterpopup\Model\Popup\Condition;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Customer extends AbstractCondition
{
    protected $_sourceYesno;
    protected $_resourceCustomer;
    protected $_groupCollection;
    protected $_directoryCountry;
    protected $_directoryAllregion;

    public function __construct(
        Context $context,
        Yesno $sourceYesno,
        CustomerResource $resourceCustomer,
        Collection $groupCollection,
        Country $directoryCountry,
        Allregion $directoryAllregion,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_sourceYesno = $sourceYesno;
        $this->_resourceCustomer = $resourceCustomer;
        $this->_groupCollection = $groupCollection;
        $this->_directoryCountry = $directoryCountry;
        $this->_directoryAllregion = $directoryAllregion;
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'newsleter_subscribed' => __('Customer Subscribed'),
            'created_at' => __('Customer Created At'),
            // 'created_in' => __('Created In'),
            // 'default_billing' => __('Default Billing'),
            // 'default_shipping' => __('Default Shipping'),
            'dob' => __('Customer DOB'),
            'age' => __('Customer Age'),
            'gender' => __('Customer Gender'),
            'group_id' => __('Customer Group ID'),
            'firstname' => __('Customer Firstname'),
            'lastname' => __('Customer Lastname'),
            'middlename' => __('Customer Middlename'),

            'region' => __('Customer Region'),
            'region_id' => __('Customer State/Province'),
            'country_id' => __('Customer Country'),

            // 'store_id' => __('Store ID'),
            // 'taxvat' => __('Taxvat'),
            // 'website_id' => __('Website ID'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'age':
                return 'numeric';

            case 'country_id':
            case 'region_id':
                return 'select';

            case 'newsleter_subscribed':
            case 'gender':
            case 'group_id':
                return 'multiselect';

            case 'created_at':
            case 'dob':
                return 'date';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
                return 'select';

            case 'newsleter_subscribed':
            case 'gender':
            case 'group_id':
                return 'multiselect';

            case 'created_at':
            case 'dob':
                return 'date';
        }
        return 'text';
    }

    public function getExplicitApply()
    {
        switch ($this->getInputType()) {
            case 'date':
                return true;
        }
        return false;
    }

    /*public function getValueElement()
    {
        $params = [];

        if($this->getValueElementType() == 'date') {
            // !! Grid cal don't need image
            $params['image'] = $this->_assetRepo->getUrl('images/grid-cal.gif');
        }

        return parent::getValueElement()->addData($params);
    }*/

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'newsleter_subscribed':
                    $options = $this->_sourceYesno->toOptionArray();
                    break;

                case 'gender':
                    $options = $this->_resourceCustomer
                        ->getAttribute('gender')
                        ->getSource()
                        ->getAllOptions(false);
                    break;

                case 'group_id':
                    $options = $this->_groupCollection
                        ->load()
                        ->toOptionArray();
                    $options[0]['label'] = 'NO ACCOUNT';
                    break;

                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    unset($options[0]);
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    unset($options[0]);
                    break;

                default:
                    $options = [];
            }

            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if (is_array($value) && isset($value[0]) && is_string($value[0]) && count($value) === 1) {
                $value = $value[0];
            }
            if ($this->isArrayOperatorType() /*&& $value*/ && is_string($value)) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }
}
