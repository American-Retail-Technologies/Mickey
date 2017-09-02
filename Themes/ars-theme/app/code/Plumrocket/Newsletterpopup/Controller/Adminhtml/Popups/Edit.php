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

class Edit extends Popups
{
    public function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit '.$this->_objectTitle);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New '.$this->_objectTitle);
            $breadcrumbLabel = __('Create '.$this->_objectTitle);
        }

        if ($model->getId()) {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                __(
                    'Edit ' . $this->_objectTitle . ' "%1"',
                    htmlspecialchars(ucfirst($model->getName()))
                )
            );
        } else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                __('New ' . $this->_objectTitle)
            );
        }

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
    }
}
