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

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address as AddressResource;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Validator\Factory;
use Plumrocket\Newsletterpopup\Helper\Data;

class Address extends AddressResource
{
    protected $_request;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Factory $validatorFactory,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        Data $dataHelper,
        $data = []
    ) {
        $this->_request = $request;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $validatorFactory, $customerRepository, $data);
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Framework\DataObject $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    protected function _validate($address)
    {
        if ($this->_dataHelper->moduleEnabled()
            && $this->_request->getModuleName() === Data::SECTION_ID
            && $this->_request->getActionName() === 'subscribe'
        ) {
            // Do not nothing
        } else {
            return parent::_validate($address);
        }
    }
}
