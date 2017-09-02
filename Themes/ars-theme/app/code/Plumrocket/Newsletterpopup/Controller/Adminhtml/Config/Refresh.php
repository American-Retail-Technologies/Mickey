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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache;
use Plumrocket\Base\Controller\Adminhtml\Actions;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Refresh extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::config';

    protected $_adminhtmlHelper;
    protected $_cache;

    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        Cache $cache
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_cache = $cache;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [
            'error'     => true,
            'message'   => 'Wkhtmltoimage was not found. Please contact your webserver admin to install thumbnail generation tool.',
        ];

        // already found
        if ($this->_adminhtmlHelper->checkIfHtmlToImageInstalled()) {
            $result['error'] = false;
            $result['message'] = 'Wkhtmltoimage is already configured. Thumbnail generation is Enabled.';
        } else {
            $cacheKeyName = $this->_adminhtmlHelper->getHtmlToImageCacheKeyName();
            $path = '/sbin /usr/sbin /usr/local/bin ~';

            $resPath = shell_exec("find $path -name \"wkhtmltoimage\"");
            if ($resPath) {
                $this->_cache->save($resPath, $cacheKeyName, [], 86400 * 365 * 40);
                $result['error'] = false;
                $result['message'] = 'Wkhtmltoimage has been found. Thumbnail generation is now Enabled.';
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
