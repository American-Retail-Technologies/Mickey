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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Label extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->setType('label');
    }

    function getHtml()
    {
        return '
            <div id="popup_code_container"' . ($this->getHidden()? ' style="display: none;"': '') . ' class="messages">
                <div class="message message-notice notice">
                    <div data-ui-id="messages-message-notice">
                        ' . $this->getEscapedValue() . '
                    </div>
                </div>
            </div>';
    }
}
