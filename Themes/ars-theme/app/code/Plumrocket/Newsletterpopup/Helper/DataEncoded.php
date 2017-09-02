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

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Model\Config\Source\Cookies;
use Plumrocket\Newsletterpopup\Model\Config\Source\Devices;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\Popup\Space;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

class DataEncoded extends Main
{
    protected $_popup = null;

    protected $_storeManager;
    protected $_cookieManager;
    protected $_popupFactory;
    protected $_space;
    protected $_dataHelper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        PopupFactory $popupFactory,
        Space $space,
        Data $dataHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_cookieManager = $cookieManager;
        $this->_popupFactory = $popupFactory;
        $this->_space = $space;
        $this->_dataHelper = $dataHelper;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
    }

    public function getCurrentPopup()
    {
        if (null === $this->_popup) {
            // if (!$this->_dataHelper->moduleEnabled()) {
            if (!$this->_dataHelper->moduleEnabled()) {
                $item = $this->_popupFactory->create();
            } elseif ($id = (int)$this->_getRequest()->getParam('id')) {
                $item = $this->_popupFactory->create()->load($id);
            /*} elseif ($this->isSubscribed()) {
                // If customer was subscribed.
                $item = $this->_popupFactory->create();*/
            } else {
                $area = $this->_getRequest()->getParam('area');
                // we disable popups on customer account pages
                if ($area == Show::ON_ACCOUNT_PAGES) {
                    $item = $this->_popupFactory->create();
                } else {
                    $lockedIds = $this->_dataHelper->getLockedPopupIds();

                    // if cookie is global and already locked any popup(s)
                    if ($this->getConfig($this->_configSectionId . '/general/cookies_usage') == Cookies::_GLOBAL && $lockedIds) {
                        $item = $this->_popupFactory->create();
                    } else {
                        $now = strftime('%F %T', time());
                        $orderBy = ['display_popup ASC'];

                        $popups = $this->_popupFactory->create()
                            ->getCollection()
                            ->addTemplateData()
                            // `status` int(1) NOT NULL DEFAULT '0',
                            ->addFieldToFilter('status', Status::STATUS_ENABLED)
                            ->addFieldToFilter('display_popup', ['neq' => Method::MANUALLY])
                            // `store_id` varchar(32) NOT NULL DEFAULT '0',
                            ->addStoreFilter($this->_storeManager->getStore());
                            // `cookie_time_frame` int(11) NOT NULL DEFAULT '7',
                            // Filtered by COOKIE and check out in _toHTML method of block.

                        // `start_date` datetime DEFAULT NULL,
                        $popups->getSelect()->where("(`start_date` <= '$now') OR (`start_date` IS NULL)");
                        // `end_date` datetime DEFAULT NULL,
                        $popups->getSelect()->where("(`end_date` >= '$now') OR (`end_date` IS NULL)");

                        // check cookies id
                        if ($this->getConfig($this->_configSectionId . '/general/cookies_usage') == Cookies::_SEPARATE && $lockedIds) {
                            $popups->getSelect()->where('`main_table`.`entity_id` NOT IN (?)', $lockedIds);
                        }

                        if ($orderBy) {
                            $popups->getSelect()->order($orderBy);
                        }

                        $space = $this->_space->getSpace();

                        $item = $this->_popupFactory->create();
                        foreach ($popups as $key => $_popup) {
                            // If customer is subscribed and have not special rule, use default logic: don't show popup.
                            if (false === strpos($_popup->getData('conditions_serialized'), 'newsleter_subscribed') && $this->_space->isSubscribed($space->getCustomer())) {
                                continue;
                            }

                            if ($_popup->validate($space)) {
                                $item = $_popup;
                                break;
                            }
                        }
                    }
                }
            }
            // load coupon code
            $item = $this->_dataHelper->assignCoupon($item);
            $this->_popup = $item;
        }

        return $this->_popup;
    }

    public function getLockedPopupIds()
    {
        $ids = [];
        foreach (range(0, 100) as $n) {
            if ($this->_cookieManager->getCookie("prnewsletterpopup_disable_popup_$n")) {
                $ids[] = $n;
            }
        }
        return $ids;
    }
}
