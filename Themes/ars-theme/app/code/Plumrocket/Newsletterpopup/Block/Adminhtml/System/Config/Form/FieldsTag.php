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

class FieldsTag extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->getLayout()->createBlock('Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag\InputTable')
            ->setContainerFieldId($element->getName())
            ->setRowKey('name')
            ->addColumn('orig_label', [
                'header'    => __('Newsletter Popup Field'),
                'index'     => 'orig_label',
                'type'      => 'label',
                'width'     => '36%',
                'class'     => 'test',
            ])
            ->addColumn('label', [
                'header'    => __('Mailchimp Field'),
                'index'     => 'label',
                'type'      => 'input',
                'width'     => '28%',
            ])
            ->setArray($element->getValue())
            ->toHtml();
    }
}
