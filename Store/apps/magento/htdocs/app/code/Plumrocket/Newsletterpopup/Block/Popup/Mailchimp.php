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

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode;

class Mailchimp extends Template
{
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        $mode = $this->getPopup()->getSubscriptionMode();
        switch ($mode) {
            case SubscriptionMode::ONE_LIST_RADIO:
                $this->setTemplate('popup/mailchimp/radio.phtml');
                break;
            case SubscriptionMode::ONE_LIST_SELECT:
                $this->setTemplate('popup/mailchimp/select.phtml');
                break;
            case SubscriptionMode::MUPTIPLE_LIST:
                $this->setTemplate('popup/mailchimp/checkbox.phtml');
                break;
            case SubscriptionMode::ALL_LIST:
            case SubscriptionMode::ALL_SELECTED_LIST:
            default:
                $this->setTemplate('empty.phtml');
                break;
        }

        return parent::_toHtml();
    }

    public function getLists()
    {
        // for preview: dynamic generated data
        if ($data = $this->getPopup()->getData('custom_mailchimp_list')) {
            return $data;
        }

        $popupId = $this->getPopup()->getId();
        if ($this->getPopup()->getIsTemplate()) {
            $popupId = 0;
        }
        return $this->_dataHelper->getPopupMailchimpList($popupId, true);
    }

    public function getPopup()
    {
        // de($this->getParentBlock()->getPopup()->getId());
        return $this->getParentBlock()->getPopup();
    }
}
