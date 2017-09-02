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

class Mass extends Popups
{
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        $ids = $this->getRequest()->getParam('popup_id');

        if (is_array($ids) && $ids) {
            try {
                foreach ($ids as $id) {
                    switch ($action) {
                        case 'enable':
                            $model = $this->_objectManager->create($this->_modelClass)
                                ->load($id)
                                ->setStatus('1')
                                ->save();
                            break;
                        case 'disable':
                            $model = $this->_objectManager->create($this->_modelClass)
                                ->load($id)
                                ->setStatus('0')
                                ->save();
                            break;
                        case 'delete':
                            $this->_delete($id);
                            break;
                        case 'duplicate':
                            $this->_duplicate($id);
                            break;
                    }
                }
                $messages =
                [
                    'enable'    => 'Total of %1 record(s) were successfully enabled',
                    'disable'    => 'Total of %1 record(s) were successfully disabled',
                    'delete'    => 'Total of %1 record(s) were successfully deleted',
                    'duplicate'    => 'Total of %1 record(s) were successfully duplicated',
                ];

                if ($ids) {
                    $this->messageManager->addSuccess(__($messages[$action], count($ids)));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select item(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
