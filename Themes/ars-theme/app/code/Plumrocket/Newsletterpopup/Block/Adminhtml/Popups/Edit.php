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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;

class Edit extends Container
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_messageManager;
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        ManagerInterface $messageManager,
        Registry $registry,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_messageManager = $messageManager;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Plumrocket_Newsletterpopup';
        $this->_controller = 'adminhtml_popups';
        // $this->_mode = 'edit';
        parent::_construct();

        $model = $this->_coreRegistry->registry('current_model');


        if ($this->_isAllowedAction('Plumrocket_Newsletterpopup::popups')) {
            $this->updateButton('save', 'label', __('Save Popup'));
            $this->updateButton('delete', 'onclick', 'deleteConfirm(\''. __('By deleting popup you will also delete history. Are you sure?')
                    .'\', \'' . $this->getDeleteUrl() . '\')');

            $this->addButton('saveandcontinue', [
                'label'     => __('Save and Continue Edit'),
                'class'     => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ], -100);
        } else {
            $this->removeButton('save');
            $this->removeButton('delete');
        }

        if ($model->getId()) {
            if ($this->_isAllowedAction('Plumrocket_Newsletterpopup::popups')) {
                $this->addButton('duplicate', [
                    'label'     => __('Duplicate'),
                    'onclick'   => "setLocation('" . $this->_getDuplicateUrl() . "')",
                    'class'     => 'duplicate',
                ], 1, 5);
            }

            $this->addButton('preview', [
                'label'     => __('Preview'),
                'onclick'   => 'previewPopup()',
                'class'     => 'preview',
            ], 1, 6);
        }
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    protected function _getDuplicateUrl()
    {
        $model = $this->_coreRegistry->registry('current_model');
        $id = ($model)? $model->getId(): 0;

        return $this->getUrl('*/*/duplicate', [
            'id' => $id
        ]);
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', [
            '_current'  => true,
            'back'      => 'edit',
            'active_tab'       => '{{tab_id}}'
        ]);
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        $model = $this->_coreRegistry->registry('current_model');
        if ($model->getId()) {
            return __(
                'Edit Popup "%1"',
                $this->escapeHtml(ucfirst($model->getName()))
            );
        } else {
            return __('New Popup');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @see Mage_Adminhtml_Block_Widget_Container::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        $tabsBlock = $this->getLayout()->getBlock('prnewsletterpopup_edit_tabs');
        if ($tabsBlock) {
            $tabsBlockJsObject = $tabsBlock->getJsObjectName();
            $tabsBlockPrefix = $tabsBlock->getId() . '_';
        } else {
            $tabsBlockJsObject = 'edit_tabsJsTabs';
            $tabsBlockPrefix = 'edit_tabs_';
        }

        $previewUrl = $this->_adminhtmlHelper->getFrontendUrl('prnewsletterpopup/index/preview', [], true);
        $options = [
            'previewUrl'            => $this->_dataHelper->validateUrl($previewUrl),
            'coupons_date'          => $this->_getCouponsDates(),
            'tabsIdValue'           => $tabsBlockJsObject . '.activeTab.id',
            'tabsBlockPrefix'       => $tabsBlockPrefix,
            'templatePlaceholders'  => $this->_dataHelper->getTemplatePlaceholders(true)
        ];

        $this->_formScripts[] = 'window.prnewsletterpopupOptions = ' . json_encode($options) . ';';
        $this->_formScripts[] = 'require(["Plumrocket_Newsletterpopup/js/edit"]);';

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }
        ";

        return parent::_prepareLayout();
    }

    protected function _getCouponsDates()
    {
        $dates = [];
        foreach ($this->_adminhtmlHelper->getCoupons() as $item) {
            $startDate = strtotime($item->getFromDate());
            $endDate = strtotime($item->getToDate());

            $dates[$item->getId()] = [
                'from_date' => $startDate? strftime('%m/%d/%Y %I:%M %P', $startDate): '',
                'to_date'   => $endDate? strftime('%m/%d/%Y %I:%M %P', $endDate): ''
            ];
        }

        return $dates;
    }
}
