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

namespace Plumrocket\Newsletterpopup\Block;

use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Redirectto;
use Plumrocket\Newsletterpopup\Model\Popup\Space;

class Template extends ViewTemplate
{
    protected $_dataHelper;
    protected $_space;

    protected $_layoutBased = false;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Space $space,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_space = $space;
        parent::__construct($context, $data);

        $this->_cacheInit();
    }

    protected function _isEnabled()
    {
        return $this->_dataHelper->moduleEnabled();
    }

    protected function _cacheInit()
    {
        if ($this->_isEnabled() && $this->getPopup() && !$this->getPopup()->getIsTemplate()) {
            $id = $this->getPopup()->getId();
            if ($id > 0) {
                $this->addData([
                    'cache_lifetime'    => 86400, // (seconds) data lifetime in the cache
                    'cache_tags'         => ['prnewsletterpopup_' . $id],
                    'cache_key'            => $id
                ]);
            }
        }
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_isEnabled() && !$this->_layoutBased) {
            $this->setTemplate('popup.phtml')
                ->setChild(
                    'popup.body',
                    $this->getLayout()->createBlock(
                        'Plumrocket\Newsletterpopup\Block\Popup'
                    )
                );
        }
    }

    protected function _toHtml()
    {
        if (!$this->_isEnabled()
            // if not found or in account pages or popup included in array of locked popups
            || ($this->getPopup()->getId() == 0)
        ) {
            $this->setTemplate('empty.phtml');
        }
        return parent::_toHtml();
    }

    public function getPopup()
    {
        return $this->_dataHelper->getCurrentPopup();
    }

    public function getSuccessPage()
    {
        $page = '';
        if ($this->_dataHelper->moduleEnabled()) {
            switch ($this->getPopup()->getData('success_page')) {
                case '':
                case Redirectto::STAY_ON_PAGE:
                    $page = '';
                    break;
                case Redirectto::CUSTOM_URL:
                    $page = $this->getPopup()->getData('custom_success_page');
                    break;
                case Redirectto::ACCOUNT_PAGE:
                    $page = $this->getUrl('customer/account');
                    break;
                case Redirectto::LOGIN_PAGE:
                    $page = $this->getUrl('customer/account/login');
                    break;
                default:
                    $page = $this->getUrl($this->getPopup()->getData('success_page'));
                    break;
            }
        }
        return $page;
    }

    public function getJsonConfig()
    {
        $popup = $this->getPopup();
        $config =
        [
            'display_popup' => $popup->getData('display_popup'),
            'delay_time' => (int)$popup->getData('delay_time'),
            'page_scroll' => (int)$popup->getData('page_scroll'),
            'css_selector' => $popup->getData('css_selector'),
            'success_url' => $this->getSuccessPage(),
            'cookie_time_frame' => (int)$popup->getData('cookie_time_frame'),
            'id' => (int)$popup->getId(),
            'current_device' => $this->_space->getDevice(),
        ];

        return json_encode($config);
    }
}
