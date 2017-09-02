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
 * @package     Plumrocket_SocialLoginFree
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
 
namespace Plumrocket\SocialLoginFree\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Stdlib\DateTime;

class Authentication extends \Magento\Customer\Model\Authentication
{
    protected $helper = null;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry,
        ConfigInterface $backendConfig,
        DateTime $dateTime,
        Encryptor $encryptor,
        \Plumrocket\SocialLoginFree\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($customerRepository, $customerRegistry, $backendConfig, $dateTime, $encryptor);
    }

    public function authenticate($customerId, $password)
    {
        if ($this->helper->moduleEnabled() && $this->helper->isFakeMail()) {
            return true;
        }
        parent::authenticate($customerId, $password);
    }
}