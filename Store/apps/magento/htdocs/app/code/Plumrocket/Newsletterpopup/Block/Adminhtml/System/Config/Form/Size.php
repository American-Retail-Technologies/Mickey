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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Size extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->getElementHtml($element);
    }

    public function getElementHtml($element)
    {
        $condition = 'eg';
        $value_height = 0;

        if ($value = $element->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 2) {
                $condition = $values[0];
                $value_height = $values[1];
            }
        }

        $html = sprintf(
            '<div style="width: 180px; float: left;">
            <select name="%s" %s style="width:175px;">
                <option value="eg"%s>equals or greater than</option>
                <option value="el"%s>equals or less than</option>
                <option value="g"%s>greater than</option>
                <option value="l"%s>less than</option>
            </select></div>',
            $element->getName() . '[]',
            $element->serialize($element->getHtmlAttributes()),
            ($condition == 'eg')? ' selected="selected"': '',
            ($condition == 'el')? ' selected="selected"': '',
            ($condition == 'g')? ' selected="selected"': '',
            ($condition == 'l')? ' selected="selected"': ''
        );

        $html .= sprintf(
            '<div style="width: 90px; float: left;"><input type="text" value="%s" name="%s" %s style="width:90px;"></div>',
            $value_height,
            $element->getName() . '[]',
            $element->serialize($element->getHtmlAttributes())
        );

        $html .= '<div class="clear" style="clear: both;"></div>';

        $html.= $element->getAfterElementHtml();
        return $html;
    }
}
