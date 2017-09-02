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

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;

class Field extends AbstractWidget
{
    protected $_attributeMetadataDataProvider;
    protected $_customerOptions;
    protected $_customer;

    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        Options $customerOptions,
        Customer $customer,
        array $data = []
    ) {
        $this->setTemplate('popup/fields.phtml');

        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->_customerOptions = $customerOptions;
        $this->_customer = $customer;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
    }

    public function _toHtml()
    {
        $this->setTemplate('popup/fields/' . $this->getField()->getName() . '.phtml');
        return parent::_toHtml();
    }

    public function getLabel()
    {
        return $this->getField()->getLabel();
    }

    public function isRequired()
    {
        $name = $this->getField()->getName();
        if (in_array($name, ['confirm_password', 'confirm_email'])) {
            return true;
        }

        return $this->_attributeMetadataDataProvider->getAttribute('customer', $name)->getIsRequired()
            || $this->_attributeMetadataDataProvider->getAttribute('customer_address', $name)->getIsRequired();
    }

    public function getFieldName($name = null)
    {
        if (null === $name) {
            $name = $this->getField()->getName();
        }
        return parent::getFieldName($name);
    }

    public function getFieldId($name = null)
    {
        if (null === $name) {
            $name = $this->getField()->getName();
        }
        return 'nl_' . parent::getFieldId($name) . '_' . $this->getPopup()->getId();
    }

    public function getAttributeValidationClass($name = null)
    {
        if (null === $name) {
            $name = $this->getField()->getName();
        }
        return $this->_addressHelper->getAttributeValidationClass($name);
    }
}
