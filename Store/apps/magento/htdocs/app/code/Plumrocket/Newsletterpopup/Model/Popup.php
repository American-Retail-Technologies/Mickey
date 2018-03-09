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

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\CollectionFactory;
use Magento\Store\Model\Store;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\CombineFactory;
use Plumrocket\Newsletterpopup\Model\TemplateFactory;

class Popup extends AbstractModel
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_templateFactory;
    protected $_combineFactory;
    protected $_actionCollectionFactory;
    protected $_filterProvider;
    protected $_filesystem;
    protected $_store;
    protected $_messageManager;
    protected $_dateTime;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        TemplateFactory $templateFactory,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        FilterProvider $filterProvider,
        Filesystem $filesystem,
        Store $store,
        ManagerInterface $messageManager,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_templateFactory = $templateFactory;
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_filterProvider = $filterProvider;
        $this->_filesystem = $filesystem;
        $this->_store = $store;
        $this->_messageManager = $messageManager;
        $this->_dateTime = $dateTime;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Plumrocket\Newsletterpopup\Model\ResourceModel\Popup');

        $defaultTemplate = $this->_templateFactory->create()->load(20);

        $defaults =
        [
            'success_page'      =>'__stay__',
            'display_popup'     => 'after_time_delay',
            'template_id'       => $defaultTemplate->getId(),
            'code'              => $defaultTemplate->getCode(),
            'style'             => $defaultTemplate->getStyle(),
            'send_email'        => 1,
            'delay_time'        => '5',
            'page_scroll'       => '50',
            'css_selector'      => '',
            'cookie_time_frame' => '30',
            'store_id'          => '0',
            'text_title'        => __('Join our email list and SAVE'),
            'text_submit'       => __('Submit'),
            'text_cancel'       => __('No Thanks'),
            'code_length'       => 12,
            'code_format'       => 'alphanum',
            'code_prefix'       => '',
            'code_suffix'       => '',
            'code_dash'         => 0,
            'email_template'    => 'prnewsletterpopup_general_email_template',
            'animation'         => 'fadeInDownBig',
            'signup_method'     => 'signup_only',
            'subscription_mode' => 'all_selected',
            'conditions_serialized' => $this->_adminhtmlHelper->getDefaultRule(true),
        ];
        $data = $this->getData();

        foreach ($defaults as $key => $val) {
            if (!array_key_exists($key, $data)) {
                $this->setData($key, $val);
            }
        }
    }

    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function getData($key = '', $index = null)
    {
        if (!$this->_dataHelper->isAdmin()) {
            if (in_array($key, ['text_description', 'text_success'])) {
                $process = $this->_filterProvider->getPageFilter();
                return $process->filter(parent::getData($key));
            }
        }
        return parent::getData($key, $index);
    }

    public function cleanCache()
    {
        $this->_cacheManager->clean('prnewsletterpopup_' . $this->getId());
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
                ['id' => $this->getId()]
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
        return $this->_webOrDirFormat($forWeb, DIRECTORY_SEPARATOR . 'prnewsletterpopup' . DIRECTORY_SEPARATOR . 'popup_' . $this->getId() . '.png');
    }

    public function getThumbnailCacheFilePath($forWeb = false)
    {
        return $this->_webOrDirFormat($forWeb, DIRECTORY_SEPARATOR . 'prnewsletterpopup' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . Adminhtml::THUMBNAIL_WIDTH . 'x' . Adminhtml::THUMBNAIL_WIDTH . DIRECTORY_SEPARATOR . 'popup_' . $this->getId() . '.png');
    }

    public function beforeSave()
    {
        if ($this->getTemplateId()) {
            if ($template = $this->_templateFactory->create()->load($this->getTemplateId())) {
                $templateCode = $this->_dataHelper->getNString($template->getCode());
                $popupCode = $this->_dataHelper->getNString($this->getCode());
                $templateStyle = $this->_dataHelper->getNString($template->getStyle());
                $popupStyle = $this->_dataHelper->getNString($this->getStyle());

                if (strnatcmp($templateCode, $popupCode) || strnatcmp($templateStyle, $popupStyle)) {
                    $countPopups = $this
                        ->getCollection()
                        ->addFieldToFilter('template_id', $template->getId())
                        ->getSize();

                    if ($template->getBaseTemplateId() == -1 || ($this->isObjectNew() && $countPopups >= 1) || (!$this->isObjectNew() && $countPopups > 1)) {
                        // Create new.
                        $template->setBaseTemplateId($template->getId());
                        $template->setId(null);
                        $template->setName($template->getName() . ' - '. $this->getName());
                    }

                    $template->addData([
                        'code'  => $popupCode,
                        'style' => $popupStyle,
                    ]);

                    if ($templateId = $template->save()->getId()) {
                        $template->generateThumbnail();
                        $this->setTemplateId($templateId);
                    }
                }
            }
        }

        $this->unsetData('template_name');
        $this->unsetData('code');
        $this->unsetData('style');

        // Prepare date fields.
        foreach (['start_date', 'end_date'] as $field) {
            $value = !$this->getData($field) ? null : $this->getData($field);
            $this->setData($field, $this->_dateTime->formatDate($value));
        }

        return parent::beforeSave();
    }

    protected function _afterLoad()
    {
        if ($this->getTemplateId()) {
            if ($template = $this->_templateFactory->create()->load($this->getTemplateId())) {
                $this->addData([
                    'template_name' => $template->getName(),
                    'code'    => $template->getCode(),
                    'style'    => $template->getStyle(),
                ]);
            }
        }
        return parent::_afterLoad();
    }
}
