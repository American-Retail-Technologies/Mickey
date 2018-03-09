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
use Magento\SalesRule\Helper\Coupon as CouponHelper;

class Coupon extends Generic
{
    protected $_couponHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CouponHelper $couponHelper,
        array $data = []
    ) {
        $this->_couponHelper = $couponHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
        $disabled = $model->getCouponCode() == 0;

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('coupon_fieldset', ['legend' => __('Coupons Format')]);

        $fieldset->addType('extended_label', 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\Label');

        $fieldset->addField('just_label', 'extended_label', [
            'hidden' => !$disabled
        ]);
        $model->setData('just_label', __('Coupon code is not selected in the General section above. Coupons Format is disabled.'));

        $fieldset->addField('code_length', 'text', [
            'name'     => 'code_length',
            'label'    => __('Code Length'),
            'required' => true,
            'note'     => __('Excluding prefix, suffix and separators.'),
            'value'    => $this->_couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero',
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_format', 'select', [
            'label'    => __('Code Format'),
            'name'     => 'code_format',
            'options'  => $this->_couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $this->_couponHelper->getDefaultFormat(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_prefix', 'text', [
            'name'  => 'code_prefix',
            'label' => __('Code Prefix'),
            'value' => $this->_couponHelper->getDefaultPrefix(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_suffix', 'text', [
            'name'  => 'code_suffix',
            'label' => __('Code Suffix'),
            'value' => $this->_couponHelper->getDefaultSuffix(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_dash', 'text', [
            'name'  => 'code_dash',
            'label' => __('Dash Every X Characters'),
            'note'  => __('If empty no separation.'),
            'value' => $this->_couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits',
            'disabled' => $disabled,
        ]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
