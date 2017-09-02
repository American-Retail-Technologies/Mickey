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

use Magento\Config\Model\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Plumrocket\Newsletterpopup\Model\PopupFactory;
use Plumrocket\Newsletterpopup\Model\TemplateFactory;

class Data extends Main
{
    const VISITOR_ID_PARAM_NAME = 'nsp_v';
    const SECTION_ID = 'prnewsletterpopup';

    /**
     * @var array
     */
    protected $_defaultValues = [
        'status' => 1,
        'display_popup' => 'after_time_delay',
        'delay_time' => 0,
        'text_title' => 'GET $10 OFF YOUR FIRST ORDER',
        'text_description' => '<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>',
        'text_success' => '<p>Thank you for your subscription.</p>',
        'text_submit' => 'Sign Up Now',
        'text_cancel' => 'Hide',
        'animation' => 'fadeInDownBig',
    ];

    /**
     * @var array
     */
    protected $_templatePlaceholders = [
        '{{text_cancel}}',
        '{{text_title}}',
        '{{text_description}}',
        '{{form_fields}}',
        '{{mailchimp_fields}}',
        '{{text_submit}}',
    ];

    /**
     * @var string
     */
    protected $_configSectionId = 'prnewsletterpopup';

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var Rule
     */
    protected $_salesRule;

    /**
     * @var Coupon
     */
    protected $_coupon;

    /**
     * @var Encryptor
     */
    protected $_encryptor;

    /**
     * @var PhpCookieManager
     */
    protected $_phpCookieManager;

    /**
     * @var PublicCookieMetadataFactory
     */
    protected $_publicCookieMetadataFactory;

    /**
     * @var DataEncodedFactory
     */
    protected $_dataEncodedHelperFactory;

    /**
     * @var PopupFactory
     */
    protected $_popupFactory;

    /**
     * @var TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Data constructor.
     *
     * @param ObjectManagerInterface      $objectManager
     * @param Context                     $context
     * @param Config                      $config
     * @param StoreManagerInterface       $storeManager
     * @param Store                       $store
     * @param Rule                        $salesRule
     * @param Coupon                      $coupon
     * @param Encryptor                   $encryptor
     * @param PhpCookieManager            $phpCookieManager
     * @param PublicCookieMetadataFactory $publicCookieMetadataFactory
     * @param DataEncodedFactory          $dataEncodedHelperFactory
     * @param PopupFactory                $popupFactory
     * @param TemplateFactory             $templateFactory
     * @param ResourceConnection          $resourceConnection
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        Config $config,
        StoreManagerInterface $storeManager,
        Store $store,
        Rule $salesRule,
        Coupon $coupon,
        Encryptor $encryptor,
        PhpCookieManager $phpCookieManager,
        PublicCookieMetadataFactory $publicCookieMetadataFactory,
        DataEncodedFactory $dataEncodedHelperFactory,
        PopupFactory $popupFactory,
        TemplateFactory $templateFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_store = $store;
        $this->_salesRule = $salesRule;
        $this->_coupon = $coupon;
        $this->_encryptor = $encryptor;
        $this->_phpCookieManager = $phpCookieManager;
        $this->_publicCookieMetadataFactory = $publicCookieMetadataFactory;
        $this->_dataEncodedHelperFactory = $dataEncodedHelperFactory;
        $this->_popupFactory = $popupFactory;
        $this->_templateFactory = $templateFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($objectManager, $context);
    }

    public function moduleEnabled()
    {
        return (bool)$this->getConfig($this->_configSectionId . '/general/enable');
    }

    public function getCurrentPopup()
    {
        return $this->moduleEnabled() && !$this->isAdmin() ?
            $this->_dataEncodedHelperFactory->create()->getCurrentPopup() :
            $this->_popupFactory->create();
    }

    public function getLockedPopupIds()
    {
        return $this->moduleEnabled() && !$this->isAdmin() ? $this->_dataEncodedHelperFactory->create()->getLockedPopupIds() : [];
    }

    public function isAdmin()
    {
        return $this->_storeManager->getStore()->getId() === Store::DEFAULT_STORE_ID;
    }

    public function validateUrl($url)
    {
        // !! I think need to use storeManager here.
        if (!$this->_store->isCurrentlySecure()) {
            $url = str_replace('https://', 'http://', $url);
        } else {
            $url = str_replace('http://', 'https://', $url);
        }
        return $url;
    }

    public function getPopupMailchimpList($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'MailchimpList');
    }

    public function getPopupMailchimpListKeys($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'MailchimpList', true);
    }

    public function getPopupFormFields($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'FormField');
    }

    public function getPopupFormFieldsKeys($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'FormField', true);
    }

    public function disableExtension()
    {
        $connection = $this->resourceConnection->getConnection('core_write');
        $connection->delete(
            $this->resourceConnection->getTableName('core_config_data'),
            [$connection->quoteInto('path = ?', $this->_configSectionId . '/general/enable')]
        );

        $this->_config->setDataByPath($this->_configSectionId . '/general/enable', 0);
        $this->_config->save();
    }

    private function _getCollectionData($popupId, $justActive, $model, $justKeys = false)
    {
        $collection = $this->_objectManager->get('Plumrocket\Newsletterpopup\Model\\' . $model)
            ->getCollection()
            ->addFieldToFilter('popup_id', $popupId);

        if ($justActive) {
            $collection = $collection->addFieldToFilter('enable', 1);
        }
        $collection->getSelect()->order(['sort_order', 'label']);

        $result = [];
        foreach ($collection as $item) {
            if ($justKeys) {
                $result[] = $item->getName();
            } else {
                $result[$item->getName()] = $item;
            }
        }
        return $result;
    }

    public function getPopupById($id)
    {
        $item = $this->_popupFactory->create()->load($id);
        // load coupon code
        return $this->assignCoupon($item);
    }

    public function assignCoupon($item)
    {
        $rule = $this->_salesRule->load((int)$item->getCouponCode());
        if (!$rule->getUseAutoGeneration()) {
            $rule->setCoupon(
                $this->_coupon->loadPrimaryByRule($rule)
            );
        }
        return $item->setCoupon($rule);
    }

    public function getPopupTemplateById($id)
    {
        if ($item = $this->_templateFactory->create()->load($id)) {
            if (!$defaultValues = @unserialize($item->getData('default_values'))) {
                $defaultValues = [];
            }
            $item->addData(array_merge($this->_defaultValues, $defaultValues));
            $this->_getRequest()->setParams($defaultValues);
        }

        return $item;
    }

    public function getTemplatePlaceholders($withAdditional = false)
    {
        $additional = [
            '{{media url="wysiwyg/image.png"}}',
            '{{view url="Plumrocket_Newsletterpopup::images/image.png"}}',
            '{{store direct_url="privacy-policy-cookie-restriction-mode"}}',
        ];

        if ($withAdditional) {
            return array_merge($this->_templatePlaceholders, $additional);
        }

        return $this->_templatePlaceholders;
    }

    public function getNString($str)
    {
        return str_replace("\r\n", "\n", $str);
    }

    public function visitorId($id = null)
    {
        // !! Check, where is set cookie and not casheable it
        if ($prevId = $this->_phpCookieManager->getCookie(self::VISITOR_ID_PARAM_NAME)) {
            $prevId = (int)$this->_encryptor->decrypt($prevId);
        }

        if ($id) {
            $this->_phpCookieManager->setPublicCookie(
                self::VISITOR_ID_PARAM_NAME,
                $this->_encryptor->encrypt($id),
                $this->_publicCookieMetadataFactory->create()->setDurationOneYear()
            );
        }

        return $prevId;
    }
}
