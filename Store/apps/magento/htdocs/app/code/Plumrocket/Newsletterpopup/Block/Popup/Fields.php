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

namespace Plumrocket\Newsletterpopup\Block\Popup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Helper\Data;

class Fields extends AbstractWidget
{
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Data $dataHelper,
        array $data = []
    ) {
        $this->setTemplate('popup/fields.phtml');

        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
    }

    public function getFields()
    {
        if ($data = $this->_getPopup()->getData('custom_signup_fields')) {
            return $data;
        }
        $popupId = $this->_getPopup()->getId();
        if ($this->_getPopup()->getIsTemplate()) {
            $popupId = 0;
        }
        return $this->_dataHelper->getPopupFormFields($popupId, true);
    }

    public function createBlock($field)
    {
        $blockName = 'field';
        if (in_array($field->getName(), ['dob', 'gender', 'prefix', 'suffix'])) {
            $blockName = $field->getName();
        }
        return $this->getLayout()
            ->createBlock('Plumrocket\Newsletterpopup\Block\Popup\Fields\\' . ucfirst($blockName))
            ->setField($field)
            ->setPopup($this->_getPopup());
    }

    protected function _getPopup()
    {
        return $this->getParentBlock()->getPopup();
    }
}
