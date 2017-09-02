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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Date extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $fieldName = $this->getColumn()->getIndex();
        $value = $row->getData($fieldName);

        if ($value) {
            if ($value == '0000-00-00 00:00:00') {
                $value = '';
            } else {
                $value = $this->formatDate($value, \IntlDateFormatter::MEDIUM, true);
            }
        }
        return (string)$value;
    }
}
