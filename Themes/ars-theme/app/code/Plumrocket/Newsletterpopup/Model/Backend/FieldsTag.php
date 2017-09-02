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

namespace Plumrocket\Newsletterpopup\Model\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Plumrocket\Newsletterpopup\Helper\Data;

class FieldsTag extends Value
{
    protected $_fields = [
        'email'         => 'EMAIL',
        'firstname'     => 'FNAME',
        'middlename'    => 'MNAME',
        'lastname'      => 'LNAME',
        'suffix'        => 'SUFFIX',
        'dob'           => 'DOB',
        'gender'        => 'GENDER',
        'taxvat'        => 'TAXVAT',
        'prefix'        => 'PRENAME',
        'telephone'     => 'TELEPHONE',
        'fax'           => 'FAX',
        'company'       => 'COMPANY',
        'street'        => 'STREET',
        'city'          => 'CITY',
        'country_id'    => 'COUNTRY',
        'region '       => 'STATE',
        'postcode'      => 'ZIPCODE',
        'coupon'        => 'COUPON',
    ];

    protected $_dataHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Data $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function parseValue($value)
    {
        $result = $this->_getFields();
        $values = json_decode($value);
        if ($values) {
            foreach ($values as $name => $value) {
                $result[$name]['label'] = (!empty($value))? (string)$value: $result[$name]['label'];
            }
        }

        return $result;
    }

    public function afterLoad()
    {
        $value = $this->parseValue($this->getValue());
        $this->setValue($value);
        parent::afterLoad();
    }

    public function beforeSave()
    {
        $toSave = [];
        $values = $this->getValue();
        $result = $this->_getFields();

        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if (array_key_exists($name, $result)) {
                    $toSave[$name] = isset($value['label'])? (string)$value['label']: '';
                }
            }
        }

        $this->setValue(json_encode($toSave));
        parent::beforeSave();
    }

    protected function _getFields()
    {
        $systemItems = $this->_dataHelper->getPopupFormFields(0, false);

        $result = [];
        foreach ($this->_fields as $key => $value) {
            $result[$key] = [
                'name'      => $key,
                'orig_label'=> isset($systemItems[$key])? $systemItems[$key]->getData('label') : ucfirst($key),
                'label'     => $value,
            ];
        }

        return $result;
    }
}
