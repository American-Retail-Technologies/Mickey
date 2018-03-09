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

// use Magento\Store\Model\Store;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Helper\Data as BackendHelper;
use Plumrocket\Newsletterpopup\Helper\Image;
use Plumrocket\Newsletterpopup\Model\Config\Source\Devices;
use Plumrocket\Newsletterpopup\Model\Mcapi;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Template\Collection as TemplateCollection;

class Adminhtml extends Main
{
    const THUMBNAIL_WIDTH = 512;

    private $_mailchimp = false;
    private $_checkIfHtmlToImageInstalledResult = null;

    protected $_encryptor;
    protected $_cache;
    protected $_messageManager;
    protected $_storeManager;
    // protected $_store;
    protected $_viewRepository;
    protected $_salesRule;
    protected $_templateCollection;
    protected $_imageHelper;
    protected $backendHelper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        Encryptor $encryptor,
        Cache $cache,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        // Store $store,
        Repository $viewRepository,
        Rule $salesRule,
        TemplateCollection $templateCollection,
        Image $imageHelper,
        BackendHelper $backendHelper
    ) {
        $this->_encryptor = $encryptor;
        $this->_cache = $cache;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        // $this->_store = $store;
        $this->_viewRepository = $viewRepository;
        $this->_salesRule = $salesRule;
        $this->_templateCollection = $templateCollection;
        $this->_imageHelper = $imageHelper;
        $this->backendHelper = $backendHelper;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
    }

    public function isMaichimpEnabled()
    {
        return (bool)$this->getConfig($this->_configSectionId . '/mailchimp/enable');
    }

    public function getMcapi()
    {
        if (!$this->_mailchimp) {
            if ($this->isMaichimpEnabled()) {
                $this->_mailchimp = new Mcapi(
                    trim($this->_encryptor->decrypt($this->getConfig($this->_configSectionId . '/mailchimp/key'))),
                    true
                );
            }
        }
        return $this->_mailchimp;
    }

    public function getTemplates()
    {
        $collection = $this->_templateCollection
            ->addExpressionFieldToSelect('template_type', 'IF(main_table.base_template_id >= 0, 1, -1)', [])
            ->addExpressionFieldToSelect('is_template', new \Zend_Db_Expr(1), []);

        $collection->getSelect()
            ->joinLeft(
                ['t' => $this->_templateCollection->getResource()->getMainTable()],
                't.entity_id = main_table.base_template_id',
                ['base_template_name' => 'name']
            );

        return $collection;
    }

    public function checkIfHtmlToImageInstalled()
    {
        $disabled = explode(',', ini_get('disable_functions'));
        if (in_array('shell_exec', $disabled)) {
            return false;
        }

        if (null === $this->_checkIfHtmlToImageInstalledResult) {
            $cacheKeyName = $this->getHtmlToImageCacheKeyName();

            if ($which = shell_exec('which wkhtmltoimage')) {
                $this->_checkIfHtmlToImageInstalledResult = trim($which);
            } else {
                $path = $this->_cache->load($cacheKeyName);
                if ($path) {
                    $this->_checkIfHtmlToImageInstalledResult = shell_exec("find $path -name \"wkhtmltoimage\"");
                    if ($this->_checkIfHtmlToImageInstalledResult) {
                        $this->_checkIfHtmlToImageInstalledResult = 'wkhtmltoimage';
                    }

                    if (!$this->_checkIfHtmlToImageInstalledResult) {
                        // moved or deleted
                        $this->_cache->remove($cacheKeyName);
                        $this->_messageManager->addWarning('The wkhtmltoimage thumbnail generation tool is missing.
                            Newsletter popup thumbnail generation is now disabled.
                            Please contact your webserver admin to install wkhtmltoimage command line tool.');
                    }
                }
            }
        }

        return $this->_checkIfHtmlToImageInstalledResult;
    }

    public function getHtmlToImageCacheKeyName()
    {
        return 'prnewsletter_popup_htmltoimage';
    }

    public function getFrontendUrl($url, $params = [], $checkDomain = false)
    {
        $result = null;
        $websites = $this->_storeManager->getWebsites(true);
        foreach ($websites as $website) {
            $storeId = $website
                ->getDefaultGroup()
                ->getDefaultStoreId();

            $params = array_merge(['key' => null, '_nosid' => true], $params);
            $result = $this->_storeManager->getStore($storeId)->getUrl($url, $params);

            if (!$checkDomain || ($checkDomain && parse_url($this->_storeManager->getStore()->getBaseUrl(), PHP_URL_HOST) == parse_url($result, PHP_URL_HOST))) {
                break;
            }
        }

        if ($result) {
            $result = str_replace(
                $this->backendHelper->getAreaFrontName() . '/',
                '',
                $result
            );

            if (false !== ($length = stripos($result, '?'))) {
                $result = substr($result, 0, $length);
            }

            if ($this->getConfig('web/seo/use_rewrites')) {
                $result = str_replace('index.php/', '', $result);
            }
        }

        return $result;
    }

    public function getBaseScreenUrl($obj, $useParentBase = false)
    {
        if ($useParentBase) {
            if ($obj->getBaseTemplateId() == 0) {
                return false;
            }

            if (!$name = $obj->getBaseTemplateName()) {
                return false;
            }
        } else {
            if ($obj->getBaseTemplateId() != -1) {
                return false;
            }

            if (!$name = $obj->getTemplateName()) {
                $name = $obj->getName();
            }
        }

        $name = str_replace(['.', ' '], ['', '_'], $name);
        return $this->_viewRepository->getUrl('Plumrocket_Newsletterpopup::images/screens/' . strtolower($name) . '.jpg');
    }

    public function getScreenUrl($item)
    {
        if ($screenUrl = $this->getBaseScreenUrl($item)) {
            return $screenUrl;
        }

        $filePath = $item->getThumbnailFilePath();
        $previewPath = $item->getThumbnailCacheFilePath(true);

        if (!file_exists($filePath)) {
            $item->generateThumbnail();
            $previewPath = false;
        }

        if (file_exists($filePath)) {
            $cachedFilePath = $item->getThumbnailCacheFilePath();
            if (!$previewPath || !file_exists($cachedFilePath)) {
                $previewPath = $this->_imageHelper->resize(
                    'prnewsletterpopup/' . ($item->getIsTemplate()? 'popup_template_' : 'popup_') . $item->getId() . '.png',
                    self::THUMBNAIL_WIDTH
                );
            }
        } else {
            $previewPath = false;
        }

        if (!$previewPath) {
            $previewPath = $this->getBaseScreenUrl($item, true);
        }

        if (!$previewPath) {
            $previewPath = $this->_viewRepository->getUrl('Plumrocket_Newsletterpopup::images/none.jpg');
        }

        return $previewPath;
    }

    public function getCoupons()
    {
        $items = $this->_salesRule->getCollection()
            ->addFieldToFilter('coupon_type', ['gt' => 1]);

        return $items;
    }

    public function getDefaultRule($serialised = false)
    {
        $rule = [
            'type' => 'Plumrocket\Newsletterpopup\Model\Popup\Condition\Combine',
            'attribute' => null,
            'operator' => null,
            'value' => '1',
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [],
        ];

        $rule['conditions'][] = [
            'type' => 'Plumrocket\Newsletterpopup\Model\Popup\Condition\General',
            'attribute' => 'current_device',
            'operator' => '()',
            'value' => [
                Devices::DESKTOP,
                Devices::TABLET
            ],
            'is_value_processed' => false,
        ];

        return $serialised? serialize($rule) : $rule;
    }
}
