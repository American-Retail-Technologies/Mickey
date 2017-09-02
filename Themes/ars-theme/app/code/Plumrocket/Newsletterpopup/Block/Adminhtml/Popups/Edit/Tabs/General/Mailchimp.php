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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\General;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\MailchimpList;
use Plumrocket\Newsletterpopup\Model\Config\Source\SubscriptionMode;

class Mailchimp extends Generic
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_sourceSubscriptionMode;
    protected $_sourceMailchimplist;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        SubscriptionMode $sourceSubscriptionMode,
        MailchimpList $sourceMailchimplist,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_sourceSubscriptionMode = $sourceSubscriptionMode;
        $this->_sourceMailchimplist = $sourceMailchimplist;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
        $disabled = !$this->_adminhtmlHelper->isMaichimpEnabled();

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('mailchimp_fieldset', ['legend' => __('Mailchimp List Managment')]);

        if ($disabled) {
            $fieldset->addType('extended_label', 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\Label');

            $fieldset->addField('mailchimp_label', 'extended_label', [
                'hidden'    => false
            ]);
            $model->setData('mailchimp_label', __('Mailchimp Synchronization is not enabled in System Configuration -> Newsltter Popup. This section is disabled.'));
        }

        $fieldset->addField('subscription_mode', 'select', [
            'name'      => 'subscription_mode',
            'label'     => __('User Subscription Mode'),
            'values'    => $this->_sourceSubscriptionMode->toOptionHash(),
            'note'      => 'Here you can allow users to subscribe to the list of their choice or automatically subscribe each new user to all Mailchimp Lists',
            'disabled'  => $disabled,
        ]);

        $fieldset->addField('mailchimp_list', 'text', [
            'name'      => 'mailchimp_list',
            'label'     => __('Enable Mailchimp Lists'),
            'note'      => 'Only enabled mailchimp lists will be displayes in newsletter popup',
            'disabled'  => $disabled,
        ]);

        $form->getElement('mailchimp_list')
        ->setRenderer(
            $this->getLayout()->createBlock('Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\InputTable')
        )
        ->getRenderer()
            ->setContainerFieldId('mailchimp_list')
            ->setRowKey('name')
            ->addColumn('enable', [
                'header'    => __('Enable'),
                'index'     => 'enable',
                'type'      => 'checkbox',
                'value'     => '1',
                'width'     => '5%',
            ])
            ->addColumn('orig_label', [
                'header'    => __('Mailchimp List'),
                'index'     => 'orig_label',
                'type'      => 'label',
                'width'     => '40%',
            ])
            ->addColumn('label', [
                'header'    => __('Displayed List Name'),
                'index'     => 'label',
                'type'      => 'input',
                'width'     => '40%',
            ])
            ->addColumn('sort_order', [
                'header'    => __('Sort Order'),
                'index'     => 'sort_order',
                'type'      => 'input',
                'width'     => '15%',
            ])
            ->setArray($this->_getMailchimpData($model->getId()));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function _getMailchimpData($id)
    {
        if (!$this->_adminhtmlHelper->isMaichimpEnabled()) {
            return [];
        }
        $collectionData = $this->_dataHelper->getPopupMailchimpList($id, false);

        $result = [];
        $mailchimpList = $this->_sourceMailchimplist->toOptionHash();
        foreach ($mailchimpList as $key => $name) {
            if (array_key_exists($key, $collectionData)) {
                $data = $collectionData[$key]->getData();
            } else {
                $data = [
                    'name'       => $key,
                    'label'      => $name,
                    'enable'     => '0',
                    'sort_order' => 0,
                ];
            }
            $data['orig_label'] = $name;
            $data['id'] = 'mailchimp_list_' . $key;
            $result[] = $data;
        }

        uasort($result, create_function('$a, $b', 'return $a["sort_order"] > $b["sort_order"]? 1 : 0;'));
        return $result;
    }
}
