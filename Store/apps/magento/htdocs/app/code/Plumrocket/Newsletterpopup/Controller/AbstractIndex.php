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

namespace Plumrocket\Newsletterpopup\Controller;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
// use Magento\Framework\View\Result\PageFactory;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Subscriber;
use Plumrocket\Newsletterpopup\Model\SubscriberEncoded;

abstract class AbstractIndex extends Action
{
    protected $_subscriber;
    protected $_subscriberEncoded;
    protected $_dataHelper;
    // protected $_resultPageFactory;

    public function __construct(
        Context $context,
        Subscriber $subscriber,
        SubscriberEncoded $subscriberEncoded,
        Data $dataHelper
        // PageFactory $resultPageFactory
    ) {
        $this->_subscriber = $subscriber;
        $this->_subscriberEncoded = $subscriberEncoded;
        $this->_dataHelper = $dataHelper;
        // $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
}
