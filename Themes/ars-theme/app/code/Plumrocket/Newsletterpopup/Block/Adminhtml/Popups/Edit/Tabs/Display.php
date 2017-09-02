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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Magento\Store\Model\System\Store;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Animation;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method;
use Plumrocket\Newsletterpopup\Model\Config\Source\Template as SourceTemplate;

class Display extends Generic implements TabInterface
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_sourceTemplate;
    protected $_sourceMethod;
    protected $_sourceAnimation;
    protected $_rendererFieldset;
    protected $_conditions;
    protected $_fieldFactory;
    protected $_systemStore;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        SourceTemplate $sourceTemplate,
        Method $sourceMethod,
        Animation $sourceAnimation,
        Fieldset $rendererFieldset,
        Conditions $conditions,
        FieldFactory $fieldFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_sourceTemplate = $sourceTemplate;
        $this->_sourceMethod = $sourceMethod;
        $this->_sourceAnimation = $sourceAnimation;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->_fieldFactory = $fieldFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('display_fieldset', ['legend' => __('Display Settings')]);

        $chooseButtonHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->addData(
            [
                'id'      => 'choose_template',
                'label'   => __('Choose Theme'),
                'type'    => 'button',
            ]
        )->toHtml();

        $selectButtonHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->addData(
            [
                'label'   => __('Select'),
                'type'    => 'button',
                'class'   => 'select_template',
            ]
        )->toHtml();

        $previewButtonHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->addData(
            [
                'label'   => __('Preview'),
                'type'    => 'button',
                'class'   => 'preview_template',
            ]
        )->toHtml();

        $items = $this->_adminhtmlHelper
            ->getTemplates()
            ->setOrder('base_template_id', 'ASC')
            ->setOrder('entity_id', 'ASC');

        $countBaseTemplates = 0;
        foreach ($items as $item) {
            if ($item->getBaseTemplateId() > -1) {
                break;
            } $countBaseTemplates++;
        }

        $html = $chooseButtonHtml . '<div id="template_id_picker" data-action="' . $this->getUrl('*/*/loadTemplate') . '"><div class="template-current"></div><div class="template-list" style="display: none;"><div class="template-title">Default Templates (' . $countBaseTemplates . ')<div class="template-expand"><span></span></div></div><div class="template-wrapper"><div class="shadow"></div><ul>';

        foreach ($items as $item) {
            if ($item->getBaseTemplateId() > -1 && empty($gotoMy)) {
                $html .= '</ul></div><div class="template-title">My Templates (' . (count($items) - $countBaseTemplates) . ')<div class="template-expand"><span></span></div></div><div class="template-wrapper"><div class="shadow"></div><ul>';
                $gotoMy = true;
            }

            $screenUrl = $this->_adminhtmlHelper->getScreenUrl($item);

            $previewUrl = $this->_dataHelper->validateUrl(
                $this->_adminhtmlHelper->getFrontendUrl('prnewsletterpopup/index/preview', ['id' => $item->getId(), 'is_template' => 1])
            );

            $html .= ('<li data-id="' . $item->getId() . '" title="' . $item->getName() . '"><div class="list-table-td">' . ($screenUrl? '<div class="screen-image" style="background-image: url(\'' . $screenUrl . '\'); background-size: cover;"></div>' : '') . '</div><span>' . $item->getName() . '</span><div>' . $selectButtonHtml . '<a href="' . $previewUrl . '" target="_blank">' . $previewButtonHtml . '</a></div></li>');
        }
        $html .= '</ul></div></div></div>';

        $fieldset->addField('template_id', 'select', [
            'name'      => 'template_id',
            'label'     => __('Popup Theme'),
            'required'  => true,
            'values'    => $this->_sourceTemplate->toOptionHash(),
            'style'     => 'display: none;',
            'after_element_html' => $html,
        ]);

        $displayPopup = $fieldset->addField('display_popup', 'select', [
            'name'      => 'display_popup',
            'label'     => __('Display Popup'),
            'values'    => $this->_sourceMethod->toOptionHash(),
            'note'      => 'See documentation for manual display method.'
        ]);

        $delayTime = $fieldset->addField('delay_time', 'text', [
            'name'      => 'delay_time',
            'label'     => __('Popup Time Delay'),
            'note'      => 'Time delay in seconds after which popup should be displayed. Enter "0" to display popup on page load.',
        ]);

        $pageScroll = $fieldset->addField('page_scroll', 'text', [
            'name'      => 'page_scroll',
            'label'     => __('Scroll Threshold Trigger (%)'),
            'note'      => 'Page threshold in percent\'s (%) on scroll down, after which popup should be displayed. Example: set "30" to display popup on page after visitor scrolled down 30% of the page.',
        ]);

        $cssSelector = $fieldset->addField('css_selector', 'text', [
            'name'      => 'css_selector',
            'label'     => __('CSS Selector'),
            'note'      => 'Enter the “ID” or “Class Name” of the object you want to use to trigger the newsletter popup. Example: enter “.btn-cart” to display newsletter popup on mouse over (or on-click) the “add to cart” button.',
        ]);

        $fieldset->addField('cookie_time_frame', 'text', [
            'name'      => 'cookie_time_frame',
            'label'     => __('Cookie Timeout (days)'),
            'note'      => 'If popup was closed it will be displayed again to the same user in specified number of days. 
                            Enter "0" to never display popup again after it was closed first time.'
        ]);

        $fieldset->addField('animation', 'select', [
            'name'      => 'animation',
            'label'     => __('Animation'),
            'values'    => $this->_sourceAnimation->toOptionHash()
        ]);

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Visible In'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        /**
         * Conditions
         */
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newConditionHtml/form/popup_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Display Popup Restrictions (leave blank to show popup for all customers on all pages and devices)')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->_conditions
        );

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                $displayPopup->getHtmlId(),
                $displayPopup->getName()
            )->addFieldMap(
                $delayTime->getHtmlId(),
                $delayTime->getName()
            )->addFieldDependence(
                $delayTime->getName(),
                $displayPopup->getName(),
                'after_time_delay'
            )
            ->addFieldMap(
                $pageScroll->getHtmlId(),
                $pageScroll->getName()
            )->addFieldDependence(
                $pageScroll->getName(),
                $displayPopup->getName(),
                'on_page_scroll'
            )
            ->addFieldMap(
                $cssSelector->getHtmlId(),
                $cssSelector->getName()
            )->addFieldDependence(
                $cssSelector->getName(),
                $displayPopup->getName(),
                $this->_fieldFactory->create(
                    ['fieldData' => ['value' => 'on_mouseover on_click', 'separator' => ' ']]
                )
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Display Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Display Settings');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
