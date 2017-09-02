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
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Model\Config\Source\Redirectto;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\Source\Email\Template;

class Main extends Generic
{
    protected $_adminhtmlHelper;
    protected $_sourceRedirectto;
    protected $_sourceStatus;
    protected $_sourceEmailTemplate;
    protected $_sourceYesno;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Adminhtml $adminhtmlHelper,
        Redirectto $sourceRedirectto,
        Status $sourceStatus,
        Template $sourceEmailTemplate,
        Yesno $sourceYesno,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_sourceRedirectto = $sourceRedirectto;
        $this->_sourceStatus = $sourceStatus;
        $this->_sourceEmailTemplate = $sourceEmailTemplate;
        $this->_sourceYesno = $sourceYesno;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

         $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General')]);

        $fieldset->addField('name', 'text', [
            'name'      => 'name',
            'label'     => __('Popup Name'),
            'class'     => 'required-entry',
            'required'  => true
        ]);

        $fieldset->addField('status', 'select', [
            'name'      => 'status',
            'label'     => __('Status'),
            'values'    => $this->_sourceStatus->toOptionHash()
        ]);

        $fieldset->addField('coupon_code', 'select', [
            'name'      => 'coupon_code',
            'label'     => __('Use Coupon Code'),
            'values'    => $this->_getCoupons(),
            'note'      => 'Select Shopping Cart Price Rule that should be used to award users who opted-in for email newsletter.'
        ]);

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::SHORT
        );
        $fieldset->addField('start_date', 'date', [
            'name'          => 'start_date',
            'label'         => __('Start Date'),
            'input_format'  => DateTime::DATETIME_INTERNAL_FORMAT,
            'date_format'   => $dateFormat,
            'time_format'   => $timeFormat,
        ]);

        $fieldset->addField('end_date', 'date', [
            'name'          => 'end_date',
            'label'         => __('End date'),
            'image'         => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format'  => DateTime::DATETIME_INTERNAL_FORMAT,
            'date_format'   => $dateFormat,
            'time_format'   => $timeFormat,
            'note'          => 'Period when newsletter popup is active. Dates will be automatically loaded from selected Shopping Cart Price Rule but can be manually changed.'
        ]);

        $successPage = $fieldset->addField('success_page', 'select', [
            'name'      => 'success_page',
            'label'     => __('Subscription Success Page'),
            'values'    => $this->_sourceRedirectto->toOptionHash(),
        ]);

        $customSuccessPage = $fieldset->addField('custom_success_page', 'text', [
            'name'      => 'custom_success_page',
            'label'     => __('Custom Success Page URL'),
            'note'      => 'Please enter the full URL of the page, including the domain name, to which you will be redirecting.',
        ]);

        $sendEmail = $fieldset->addField('send_email', 'select', [
            'name'      => 'send_email',
            'label'     => __('Send Autoresponder Email'),
            'values'    => $this->_sourceYesno->toOptionArray(),
            'note'      => 'Send email when user successfully subscribed to your email newsletter.'
        ]);

        $emailTemplate = $fieldset->addField('email_template', 'select', [
            'name'      => 'email_template',
            'label'     => __('Autoresponder Email Template'),
            'values'    => $this->_sourceEmailTemplate->toOptionArray(),
            'note'      => 'Magento will send this email after user successfully subscribed to your email newsletter.',
        ]);

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                $successPage->getHtmlId(),
                $successPage->getName()
            )->addFieldMap(
                $customSuccessPage->getHtmlId(),
                $customSuccessPage->getName()
            )->addFieldDependence(
                $customSuccessPage->getName(),
                $successPage->getName(),
                '__custom__'
            )
            ->addFieldMap(
                $sendEmail->getHtmlId(),
                $sendEmail->getName()
            )->addFieldMap(
                $emailTemplate->getHtmlId(),
                $emailTemplate->getName()
            )->addFieldDependence(
                $emailTemplate->getName(),
                $sendEmail->getName(),
                '1'
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getCoupons()
    {
        $coupons = [0 => __('No')];
        foreach ($this->_adminhtmlHelper->getCoupons() as $item) {
            $coupons[$item->getId()] = $item->getName();
        }

        return $coupons;
    }
}
