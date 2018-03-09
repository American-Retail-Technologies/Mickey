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

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Action;

class History extends AbstractModel
{
    protected $_checkEnabled = true;
    protected $_checkIpSkip = true;

    protected $_dataHelper;
    protected $_remoteAddress;
    protected $_session;
    protected $_request;
    protected $_storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        RemoteAddress $remoteAddress,
        Session $session,
        RequestInterface $request,
        StoreManager $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_remoteAddress = $remoteAddress;
        $this->_session = $session;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Plumrocket\Newsletterpopup\Model\ResourceModel\History');
    }

    public function beforeSave()
    {
        if (!$this->_checkEnabled || $this->_dataHelper->getConfig(Data::SECTION_ID . '/general/enable_history')) {
            $customerIp = $this->_remoteAddress->getRemoteAddress();

            if ($this->_checkIpSkip) {
                $ipSkip = explode("\n", $this->_dataHelper->getConfig(Data::SECTION_ID . '/general/ip_skip'));
                foreach ($ipSkip as &$ip) {
                    $ip = trim($ip);
                }
                if ($this->getAction() != Action::SUBSCRIBE && in_array($customerIp, $ipSkip)) {
                    $this->_dataSaveAllowed = false;
                    return $this;
                }
            }

            $customer = $this->_session->isLoggedIn()? $this->_session->getCustomer() : false;

            $path = str_replace(['http://', 'https://'], '', $this->_request->getParam('referer'));
            $path = substr($path, strpos($path, '/'));

            $data = array_merge([
                'customer_id'        => $customer? $customer->getId(): 0,
                'customer_group'    => $customer? $customer->getGroupId(): 0,
                'customer_ip'        => $customerIp,
                'landing_page'        => $path,
                // 'store_id'            => $this->_storeManager->getStore()->getStoreId(),
                'store_id'            => $this->_storeManager->getStore()->getId(),
                'date_created'        => strftime('%F %T', time()),
            ], $this->getData());

            $this->setData($data);
        } else {
            $this->_dataSaveAllowed = false;
        }

        return $this;
    }

    public function save()
    {
        if ($this->getAction() == Action::OTHER && $this->getActionText()) {
            if (!$id = $this->_getResource()->insertOnDuplicate($this->_getResource()->getActionMainTable(), ['text' => trim($this->getActionText())], ['text'])) {
                $connection = $this->_getResource()->getConnection('read');
                $id = $connection->fetchOne('SELECT `id` FROM ' . $this->_getResource()->getActionMainTable() . ' WHERE `text` = ? LIMIT 1', trim($this->getActionText()));
            }

            if (!empty($id)) {
                $this->setActionId($id);
                $this->unsActionText();
            }
        }

        $this->setAction(ucfirst($this->getAction()));

        return parent::save();
    }

    public function checkEnabled($flag = null)
    {
        if (null === $flag) {
            $thus->_checkEnabled = (bool)$flag;
        }
        return $thus->_checkEnabled;
    }

    public function checkIpSkip($flag = null)
    {
        if (null === $flag) {
            $thus->_checkIpSkip = (bool)$flag;
        }
        return $thus->_checkIpSkip;
    }
}
