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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Popups extends Container
{
    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_popups';
        $this->_blockGroup = 'Plumrocket_Newsletterpopup';
        $this->_headerText = __('Manage Newsletter Popups');

        $this->updateButton('add', 'label', 'Add Popup');
    }

    /*protected function _prepareLayout()
       {
        $this->setChild('grid',
            $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
            $this->_controller . '.grid')->setSaveParametersInSession(true) );
        return parent::_prepareLayout();
       }*/
}
