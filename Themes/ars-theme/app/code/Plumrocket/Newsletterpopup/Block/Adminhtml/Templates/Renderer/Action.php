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

class Action extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $html = sprintf(
            '<a href="%s"><span>%s</span></a>',
            $this->getUrl('*/*/edit', ['id' => $row->getId()]),
            __('Edit')
        );

        if ($row->isBase()) {
            $html .= sprintf(
                '<div><img src="%s" title="%s" style="height: 18px;" /></div>',
                $this->getViewFileUrl('Plumrocket_Newsletterpopup::images/lock.png'),
                __('This theme is one of the default Newsletter popup themes. It cannot be edited or deleted. Instead, you can duplicate it and then edit.')
            );
        }

        return $html;
    }
}
