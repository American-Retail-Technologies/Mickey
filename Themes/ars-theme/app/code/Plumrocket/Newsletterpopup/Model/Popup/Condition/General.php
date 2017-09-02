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

use Magento\Backend\Helper\Data;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Plumrocket\Newsletterpopup\Model\Config\Source\Devices;
use Plumrocket\Newsletterpopup\Model\Config\Source\Page;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;

class General extends AbstractCondition
{
    protected $_sourceDevices;
    protected $_sourceShow;
    protected $_sourcePage;
    protected $_backendHelper;

    public function __construct(
        Context $context,
        Devices $sourceDevices,
        Show $sourceShow,
        Page $sourcePage,
        Data $backendHelper,
        array $data = []
    ) {
        $this->_sourceDevices = $sourceDevices;
        $this->_sourceShow = $sourceShow;
        $this->_sourcePage = $sourcePage;
        $this->_backendHelper = $backendHelper;
        parent::__construct($context, $data);
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'current_device'    => __('Device Type'),
            'current_page_type' => __('Current Page Type'),
            'current_cms_page'  => __('Current CMS Page'),
            'current_page_url'  => __('Current Page URL'),
            'category_ids'      => __('Current Category Page'),
            'prev_popups_count' => __('Number of Displayed Popups Per Session'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'current_device':
            case 'current_page_type':
            case 'current_cms_page':
                return 'multiselect';

            case 'category_ids':
                return 'category';

            case 'prev_popups_count':
                return 'numeric';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'current_device':
            case 'current_page_type':
            case 'current_cms_page':
                return 'multiselect';
        }
        return 'text';
    }

    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType['string'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_defaultOperatorInputByType['multiselect'] = ['()', '!()'];
            $this->_defaultOperatorInputByType['category'] = ['()', '!()'];
            $this->_defaultOperatorInputByType['numeric'] = ['==', '!=', '>=', '>', '<=', '<'];
            $this->_arrayInputTypes[] = 'category';
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                $url .= '/form/' . ($this->getJsFormObject() ?: 'popup_conditions_fieldset');
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendHelper->getUrl($url) : '';
    }

    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'category_ids':
                return true;
        }
        switch ($this->getInputType()) {
            case 'date':
                return true;
        }
        return false;
    }

    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'category_ids':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'current_device':
                    $options = $this->_sourceDevices->toOptionArray();
                    break;

                case 'current_page_type':
                    $options = $this->_sourceShow->toOptionArray();
                    break;

                case 'current_cms_page':
                    $options = $this->_sourcePage->toOptionArray();
                    break;

                /*case 'category_ids':
                    $options = Mage::getModel('adminhtml/system_config_source_category')
                        ->toOptionArray();
                    break;*/

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
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
