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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Plumrocket\Base\Controller\Adminhtml\Actions;

abstract class History extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::history';

    protected $_formSessionKey  = 'prnewsletterpopup_form_data';

    protected $_modelClass      = 'Plumrocket\Newsletterpopup\Model\History';
    protected $_activeMenu        = 'Plumrocket_Newsletterpopup::prnewsletterpopup';
    protected $_objectTitles    = 'Newsletter Popup History';

    // protected $_statusField     = 'status';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }
}
