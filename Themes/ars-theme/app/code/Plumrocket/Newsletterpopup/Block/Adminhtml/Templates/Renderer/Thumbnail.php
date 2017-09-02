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

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Thumbnail extends AbstractRenderer
{
    protected $_adminhtmlHelper;

    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $path = $this->_adminhtmlHelper->getScreenUrl($row);

        $html = '';
        if ($path !== false) {
            $html = '<div style="text-align: center;"><img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . $path . '?b=' . time() . '" ';
            $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '" ';
            $html .= 'style="height: 85px; max-width: 200px;" /></div>';
        }
        return $html;
    }
}
