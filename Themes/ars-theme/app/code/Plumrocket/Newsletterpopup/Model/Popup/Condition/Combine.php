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

namespace Plumrocket\Newsletterpopup\Model\Popup\Condition;

use Magento\Rule\Model\Condition\Combine as CombineCondition;
use Magento\Rule\Model\Condition\Context;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\Cart;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\Customer;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\General;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\Product;

class Combine extends CombineCondition
{
    protected $_conditionGeneral;
    protected $_conditionCustomer;
    protected $_conditionCart;
    protected $_conditionProduct;

    public function __construct(
        Context $context,
        General $conditionGeneral,
        Customer $conditionCustomer,
        Cart $conditionCart,
        Product $conditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType('Plumrocket\Newsletterpopup\Model\Popup\Condition\Combine');

        $this->_conditionGeneral = $conditionGeneral;
        $this->_conditionCustomer = $conditionCustomer;
        $this->_conditionCart = $conditionCart;
        $this->_conditionProduct = $conditionProduct;
    }

    public function getNewChildSelectOptions()
    {
        // $generalCondition = $this->_conditionGeneral;
        // $generalAttributes = $generalCondition->loadAttributeOptions()->getAttributeOption();
        $generalAttributes = $this->_conditionGeneral->loadAttributeOptions()->getAttributeOption();
        $general = [];
        foreach ($generalAttributes as $code => $label) {
            $general[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\General|'.$code, 'label'=>$label];
        }

        // $customerCondition = $this->_conditionCustomer;
        // $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();
        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $customer = [];
        foreach ($customerAttributes as $code => $label) {
            $customer[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Customer|'.$code, 'label'=>$label];
        }

        // $cartCondition = $this->_conditionCart;
        // $cartAttributes = $cartCondition->loadAttributeOptions()->getAttributeOption();
        $cartAttributes = $this->_conditionCart->loadAttributeOptions()->getAttributeOption();
        $cart = [];
        foreach ($cartAttributes as $code => $label) {
            $cart[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Cart|'.$code, 'label'=>$label];
        }

        // $productCondition = $this->_conditionProduct;
        // $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $productAttributes = $this->_conditionProduct->loadAttributeOptions()->getAttributeOption();
        $product = [];
        foreach ($productAttributes as $code => $label) {
            $product[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Product|'.$code, 'label'=>$label];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Combine', 'label'=>__('Conditions combination')],
            ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Found', 'label'=>__('Product attribute combination in shopping cart')],
            ['label'=>__('General'), 'value'=>$general],
            ['label'=>__('Customer Attribute'), 'value'=>$customer],
            ['label'=>__('Cart Attribute'), 'value'=>$cart],
            ['label'=>__('Current Product Page'), 'value'=>$product],
        ]);

        return $conditions;
    }
}
