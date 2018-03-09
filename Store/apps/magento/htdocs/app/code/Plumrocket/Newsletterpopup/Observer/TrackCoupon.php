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
 
 
 References: 
 https://magento.stackexchange.com/questions/139749/magento-2-capturing-coupon-code-application-cancelling
 https://stackoverflow.com/questions/26140361/how-to-get-controller-method-in-observer-in-magento
 https://cyrillschumacher.com/magento-2.2-list-of-all-dispatched-events/
 https://github.com/magento/magento2/blob/2.2.0/lib/internal/Magento/Framework/View/Element/Messages.php#L261
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\SalesRule\Model\CouponFactory;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\HistoryFactory;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

class TrackCoupon implements ObserverInterface
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

        $eventStuff = $observer->getData();
		\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode("EventStuff: ".$eventStuff));
	}
}