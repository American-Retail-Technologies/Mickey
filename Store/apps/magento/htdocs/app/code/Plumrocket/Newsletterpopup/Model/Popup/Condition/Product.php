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

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Product\AbstractProduct;

class Product extends AbstractProduct
{
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $attributes = [];
        foreach ($this->getAttributeOption() as $code => $label) {
            $attributes[$code] = __('Product') .' '. $label;
        }

        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                $url .= '/form/' . ($this->getJsFormObject() ?: 'popup_conditions_fieldset');
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    public function validate(AbstractModel $model)
    {
        $result = false;
        if (!$model instanceof CatalogProduct) {
            $model = $model->getProduct();
        }

        if ($model && $model->getId()) {
            if (!$model->hasData($this->getAttribute())) {
                $model->load($model->getId());
            }

            $result = parent::validate($model);
        }

        return $result;
    }

    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if (is_array($value) && isset($value[0]) && is_string($value[0]) && count($value) === 1) {
                $value = $value[0];
            }
            if ($this->isArrayOperatorType() /*&& $value*/ && is_string($value)) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }
}
