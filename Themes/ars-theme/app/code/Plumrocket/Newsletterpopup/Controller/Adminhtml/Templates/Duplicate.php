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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

class Duplicate extends Templates
{
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $newId = $this->_duplicate($id);
                if ($newId && ($id != $newId)) {
                    $id = $newId;
                    $this->messageManager->addSuccess(__('The Theme has been duplicated.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __($e->getMessage()));
            }
        }
        $this->_redirect('*/*/edit', ['id' => $id]);
    }
}
