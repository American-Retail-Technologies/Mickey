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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml;

use Plumrocket\Base\Controller\Adminhtml\Actions;

abstract class Templates extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::templates';

    protected $_formSessionKey  = 'prnewsletterpopup_form_data';

    protected $_modelClass      = 'Plumrocket\Newsletterpopup\Model\Template';
    protected $_activeMenu        = 'Plumrocket_Newsletterpopup::prnewsletterpopup';
    protected $_objectTitle     = 'Theme';
    protected $_objectTitles    = 'Newsletter Popup Themes';

    // protected $_idKey            = 'entity_id';

    // protected $_statusField     = 'status';

    protected function _getModel($load = true)
    {
        parent::_getModel($load);
        if (!$this->_model->getEntityId()) {
            $id = (int)$this->getRequest()->getParam('entity_id');
            if ($id && $load) {
                $this->_model->load($id);
            }
        }
        return $this->_model;
    }

    protected function _delete($id)
    {
        return $this->_objectManager->create($this->_modelClass)->load($id)->delete()->isDeleted();
    }

    protected function _duplicate($id)
    {
        $orig = $this->_objectManager->create($this->_modelClass)->load($id);
        if ($orig->getId()) {
            $clone = clone $orig;

            $cloneData = $clone->getData();
            $cloneData['name'] .= __(' (duplicate)');
            unset($cloneData['entity_id']);

            $clone->setData($cloneData);
            $clone->save();
            $id = $clone->getId();
        }
        return $id;
    }
}
