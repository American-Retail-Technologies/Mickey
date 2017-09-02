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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

class Delete extends Popups
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($this->_delete($id)) {
            $this->messageManager->addSuccess(__('The Popup has been deleted.'));
        } else {
            $this->messageManager->addError(__('The Popup has not been deleted.'));
        }
        $this->_redirect('*/*/index', ['_current' => true]);
    }
}
