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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\HistoryFactory;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

class SaveOrderObserver implements ObserverInterface
{
    protected $_dataHelper;
    protected $_historyFactory;
    protected $_popupFactory;

    public function __construct(
        Data $dataHelper,
        HistoryFactory $historyFactory,
        PopupFactory $popupFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_historyFactory = $historyFactory;
        $this->_popupFactory = $popupFactory;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_dataHelper->moduleEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        if ($code = $order->getCouponCode()) {
            $email = $order->getCustomerEmail();
            $historyItem = null;
            $history = $this->_historyFactory->create();

            if ($email) {
                $historyItem = $history
                    ->getCollection()
                    ->addFieldToFilter('customer_email', $email)
                    ->addFieldToFilter('coupon_code', $code)
                    ->getFirstItem();
            }

            if (null === $historyItem || !$historyItem->getId()) {
                $historyItem = $history->load($code, 'coupon_code');
            }

            if ($historyItem->getId()) {
                if ($order->getState() == Order::STATE_CANCELED || $order->getState() == Order::STATE_HOLDED) {
                    $this->_save($historyItem, 0, 0, 0);
                } else {
                    $this->_save(
                        $historyItem,
                        $order->getIncrementId(),
                        $order->getId(),
                        $order->getGrandTotal()
                    );
                }
            }
        }
    }

    private function _save($historyItem, $incrementId, $orderId, $grandTotal)
    {
        $boolHistoryGT = $historyItem->getData('grand_total') > 0;
        $boolGT = $grandTotal > 0;

        if ($boolGT != $boolHistoryGT) {
            $popup = $this->_popupFactory->create()->load($historyItem->getPopupId());
            // check if linked popup exists
            if ($popup->getId()) {
                $tr = $grandTotal?
                    $popup->getData('total_revenue') + $grandTotal:
                    $popup->getData('total_revenue') - $historyItem->getData('grand_total');
                $addOrdersCount = $grandTotal? 1: -1;

                $popup
                    ->setData('orders_count', $popup->getData('orders_count') + $addOrdersCount)
                    ->setData('total_revenue', $tr)
                    ->save();
            }

            $historyItem
                ->setData('increment_id', $incrementId)
                ->setData('order_id', $orderId)
                ->setData('grand_total', $grandTotal)
                ->save();
        }
    }
}
