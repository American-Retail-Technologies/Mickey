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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Template extends Generic implements TabInterface
{
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('template_');

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('Theme Template')]);

        /** @var $imageButton \Magento\Backend\Block\Widget\Button */
        $imageButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id'    => 'code_image',
                'label' => __('Insert Image...'),
                'class' => 'button',
                'onclick' => "cmSyncSelectionByEditor('#template_code', window.codeEditor); MediabrowserUtility.openDialog('" . $this->getUrl(
                    'cms/wysiwyg_images/index',
                    [
                        'target_element_id' => 'template_code',
                    ]
                ) . "', null, null,'" . $this->escapeQuote(
                    __('Insert Image...'),
                    true
                ) . "', {
                    closed: function() {
                        cmSyncChangesByTextarea('#template_code', window.codeEditor);
                    }
                });",
            ]
        );

        $fieldset->addField('code_image', 'note', [
            'label' => __('HTML Template'),
            'class'   => 'required-entry',
            'required'  => true,
            'text' => $imageButton->toHtml(),
            ]);

        $fieldset->addField('code', 'textarea', [
            'name'      => 'code',
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('style', 'textarea', [
            'name'      => 'style',
            'label'     => __('CSS Styles'),
            'note'      => 'Use hotkeys “CTRL” + “SPACE” (or “F1”) to show autocompletion hints.',
        ]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', [
                'name' => 'entity_id'
            ]);
        }

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
        return __('Theme Template');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Theme Template');
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
