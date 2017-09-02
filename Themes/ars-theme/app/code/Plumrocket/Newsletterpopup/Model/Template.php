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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
// use Plumrocket\Newsletterpopup\Model\Popup;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection as PopupCollection;

class Template extends AbstractModel
{
    protected $_adminhtmlHelper;
    // protected $_popup;
    protected $_popupCollection;
    protected $_dateTime;
    protected $_filesystem;
    protected $_store;
    protected $_messageManager;

    public function __construct(
        Context $context,
        Registry $registry,
        Adminhtml $adminhtmlHelper,
        // Popup $popup,
        PopupCollection $popupCollection,
        DateTime $dateTime,
        Filesystem $filesystem,
        Store $store,
        ManagerInterface $messageManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        // $this->_popup = $popup;
        $this->_popupCollection = $popupCollection;
        $this->_dateTime = $dateTime;
        $this->_filesystem = $filesystem;
        $this->_store = $store;
        $this->_messageManager = $messageManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Plumrocket\Newsletterpopup\Model\ResourceModel\Template');
    }

    public function beforeSave()
    {
        if (!$this->getCanSaveBaseTemplates()) {
            if ($this->getOrigData('base_template_id') == -1) {
                // It's base template, create new.
                $this->setBaseTemplateId($this->getOrigData('entity_id'));
                $this->setId(null);
            } elseif ($this->isObjectNew()) {
                $this->setBaseTemplateId($this->getOrigData('base_template_id'));
            }
        }

        // Set dates.
        $date = $this->_dateTime->gmtDate();
        if ($this->isObjectNew()) {
            $this->setData('created_at', $date);
            $this->setData('updated_at', null);
        } else {
            $this->setData('updated_at', $date);
        }

        return parent::beforeSave();
    }

    public function delete()
    {
        if (!$this->canDelete()) {
            return $this;
        }

        return parent::delete();
    }

    public function canDelete()
    {
        if ($this->isBase()) {
            return false;
        }

        // $hasPopups = $this->_popup
        $hasPopups = $this->_popupCollection
            // ->getCollection()
            ->addFieldToFilter('template_id', $this->getId())
            ->getSize();

        if ($hasPopups) {
            return false;
        }

        return true;
    }

    public function isBase()
    {
        if (!$this->hasData('base_template_id')) {
            $this->load($this->getEntityId());
        }

        if ($this->getOrigData('base_template_id') == -1) {
            return true;
        }
    }

    protected function _afterLoad()
    {
        $this->setIsTemplate(true);
        return parent::_afterLoad();
    }

    public function generateThumbnail()
    {
        if ($command = $this->_adminhtmlHelper->checkIfHtmlToImageInstalled()) {
            $dirPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('prnewsletterpopup');
            if (!file_exists($dirPath)) {
                if (!mkdir($dirPath)) {
                    $this->_messageManager->addError(__('Directory was not created. Access denied.'));
                    return false;
                }
            }

            $previewUrl = $this->_adminhtmlHelper->getFrontendUrl(
                'prnewsletterpopup/index/snapshot',
                ['id' => $this->getId(), 'is_template' => 1]
            );
            $filePath = $this->getThumbnailFilePath();
            $cacheFilePath = $this->getThumbnailCacheFilePath();

            if (file_exists($cacheFilePath)) {
                unlink($cacheFilePath);
            }

            exec("$command --crop-w 800 $previewUrl $filePath");
        }
        return true;
    }

    private function _webOrDirFormat($formatAsWeb = false, $path)
    {
        return ($formatAsWeb)?
            $this->_store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path:
            $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path);
    }

    public function getThumbnailFilePath($forWeb = false)
    {
        return $this->_webOrDirFormat($forWeb, DIRECTORY_SEPARATOR . 'prnewsletterpopup' . DIRECTORY_SEPARATOR . 'popup_template_' . $this->getId() . '.png');
    }

    public function getThumbnailCacheFilePath($forWeb = false)
    {
        return $this->_webOrDirFormat($forWeb, DIRECTORY_SEPARATOR . 'prnewsletterpopup' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . Adminhtml::THUMBNAIL_WIDTH . 'x' . Adminhtml::THUMBNAIL_WIDTH . DIRECTORY_SEPARATOR . 'popup_template_' . $this->getId() . '.png');
    }

    public function setIsObjectNew($flag = true)
    {
        $this->setCanSaveBaseTemplates($flag);
        $this->getResource()->useIsObjectNew($flag);
        $this->isObjectNew($flag);
        return $this;
    }
}
