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

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Newsletter\Helper\Data as NewsletterHelper;
use Magento\Newsletter\Model\Subscriber as NewsletterSubscriber;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\SubscriberEncoded;

class Subscriber extends NewsletterSubscriber
{
    protected $_subscriberEncoded;
    protected $_dataHelper;
    protected $_messageManager;
    protected $_attributeMetadataDataProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        NewsletterHelper $newsletterData,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        StateInterface $inlineTranslation,
        SubscriberEncoded $subscriberEncoded,
        Data $dataHelper,
        ManagerInterface $messageManager,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_subscriberEncoded = $subscriberEncoded;
        $this->_dataHelper = $dataHelper;
        $this->_messageManager = $messageManager;
        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        parent::__construct(
            $context,
            $registry,
            $newsletterData,
            $scopeConfig,
            $transportBuilder,
            $storeManager,
            $customerSession,
            $customerRepository,
            $customerAccountManagement,
            $inlineTranslation,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function customSubscribe($email, $controller, $data = [])
    {
        $customer = $this->_subscriberEncoded->validateCustomer($data);
        if ($customer === false) {
            return false;
        }

        $address = $this->_subscriberEncoded->validateAddress($data);
        if ($address === false) {
            return false;
        }

        if ($customerId = $this->_subscriberEncoded->tryRegisterCustomer($customer, $controller)) {
            $saveAddress = true;
            $systemItems = $this->_dataHelper->getPopupFormFields(0, false);
            foreach ($systemItems as $name => $value) {
                if (!$address->getData($name) && $this->_attributeMetadataDataProvider->getAttribute('customer_address', $name)->getIsRequired()) {
                    $saveAddress = false;
                    break;
                }
            }

            if ($saveAddress) {
                // save address
                $address->setCustomerId($customerId)
                    ->setIsDefaultBilling(false)
                    ->setIsDefaultShipping(false);

                $address->save();
            }
        }

        $this->addData(
            $this->_subscriberEncoded->getAdditionalData($data)
        );

        $status = $this->subscribe($email);

        if ($status == self::STATUS_NOT_ACTIVE) {
            $this->_subscriberEncoded->holdSubscribe($email, $data);
            $this->_messageManager->addSuccess(
                __('Thank you for subscribing to our newsletter! Confirmation request has been sent.')
            );
        } else {
            $this->_subscriberEncoded->subscribe($email, $data);
            if ($successText = $this->_subscriberEncoded->getPopup()->getTextSuccess()) {
                $this->_messageManager->addSuccess($successText);
            }
            /*$this->_messageManager->addSuccess(
                __('Thank you for subscribing to our newsletter! Your subscription request has been confirmed and you will be receiving more information by email shortly!')
            );*/
        }
        return $status;
    }

    public function cancel()
    {
        return $this->_dataHelper->moduleEnabled() && !$this->_dataHelper->isAdmin()
            ? $this->_subscriberEncoded->cancel()
            : false;
    }

    public function confirm($code)
    {
        $result = parent::confirm($code);
        if ($result && $this->_dataHelper->moduleEnabled()) {
            $this->_subscriberEncoded->releaseSubscribe($this);
        }
        return $result;
    }

    public function sendConfirmationSuccessEmail()
    {
        if ($this->_subscriberEncoded->getPopup()->getSendEmail()) {
            if ($this->_dataHelper->moduleEnabled()
                && $this->_subscriberEncoded->getPopup()->getId() > 0
            ) {
                return $this;
            }
            return parent::sendConfirmationSuccessEmail();
        }

        return $this;
    }
}
