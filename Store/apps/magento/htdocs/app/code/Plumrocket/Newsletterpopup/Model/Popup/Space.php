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

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\SubscriberFactory;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Devices;

class Space extends AbstractModel
{
    protected $_request;
    protected $_session;
    protected $_customerFactory;
    protected $_subscriberFactory;
    protected $_cart;
    protected $_cartHelper;
    protected $_productFactory;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        Session $session,
        CustomerFactory $customerFactory,
        SubscriberFactory $subscriberFactory,
        Cart $cart,
        CartHelper $cartHelper,
        ProductFactory $productFactory,
        Data $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_request = $request;
        $this->_session = $session;
        $this->_customerFactory = $customerFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_cart = $cart;
        $this->_cartHelper = $cartHelper;
        $this->_productFactory = $productFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /*protected function _construct()
    {
        parent::_construct();
        $this->_init('Plumrocket\Newsletterpopup\Model\ResourceModel\Popup');
    }*/

    public function getSpace()
    {
        // General.
        $this->setData('current_device', $this->getDevice());
        $this->setData('current_page_type', $this->_request->getParam('area'));
        $this->setData('current_cms_page', $this->_request->getParam('cmsPage'));
        $this->setData('current_page_url', $this->_request->getParam('referer'));
        $this->setData('category_ids', $this->_request->getParam('categoryId'));
        if (!$prevPopups = $this->_session->getData(Data::SECTION_ID . '_prev_popups')) {
            $prevPopups = [];
        }
        $this->setData('prev_popups_count', count($prevPopups));

        // Customer.
        $_customer = null;
        if ($this->_session->isLoggedIn()) {
            $_customer = $this->_session->getCustomer();
        } elseif ($visitorId = $this->_dataHelper->visitorId()) {
            $_customer = $this->_customerFactory->create()->load($visitorId);
        }

        if ($_customer) {
            $data = $_customer->getData();
            if (!empty($data['dob'])) {
                $data['age'] = floor((time() - strtotime($data['dob'])) / 31556926);
            }
            $data['newsleter_subscribed'] = $this->isSubscribed($_customer)? 1 : 0;
            $data['customer'] = $_customer;
            $this->addData($data);
        } else {
            $this->setData('newsleter_subscribed', 0);
            $this->setData('group_id', 0);
        }

        // Cart.
        $quote = $this->_cart->getQuote();
        $cart = [
            'quote'             => $quote,
            'cart_base_subtotal'=> round($quote->getGrandTotal(), 2),
            'cart_total_qty'    => (int)$this->_cartHelper->getSummaryCount(),
            'cart_total_items'  => (int)$quote->getItemsCount()
        ];
        $this->addData($cart);

        // Product.
        if ($this->_request->getParam('productId') && $_product = $this->_productFactory->create()->load($this->_request->getParam('productId'))) {
            $this->setData('product', $_product);
            $this->setData('category_ids', 0);
        }

        return $this;
    }

    public function getDevice()
    {
        $width = (int)$this->_request->getParam('w');
        $result = Devices::ALL;

        if ($width) {
            $_conf = $this->_dataHelper->getConfig(Data::SECTION_ID . '/size');
            $conf = [];

            foreach ($_conf as $device => $hash) {
                $a = explode(',', $hash);
                if (isset($a[1])) {
                    $sum = $width - (int)$a[1];
                    $condition = $a[0];

                    if (($sum === 0 && ($condition == 'el' || $condition == 'eg'))
                        || ($sum > 0 && ($condition == 'eg' || $condition == 'g'))
                        || ($sum < 0 && ($condition == 'el' || $condition == 'l'))
                    ) {
                        $conf[ abs($sum) ] = $device;
                    }
                }
            }
            ksort($conf);
            if ($conf) {
                $result = reset($conf);
            }
        }

        return $result;
    }

    public function isSubscribed($customer = null)
    {
        $status = false;
        if (null === $customer) {
            if ($this->_session->isLoggedIn()) {
                $customer = $this->_session->getCustomer();
            }
        }

        if ($customer instanceof DataObject && $email = $customer->getEmail()) {
            $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
            $status = $subscriber->isSubscribed();
        }

        return $status;
    }
}
