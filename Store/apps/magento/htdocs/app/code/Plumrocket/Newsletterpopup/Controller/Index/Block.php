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

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Plumrocket\Newsletterpopup\Controller\AbstractIndex;

class Block extends AbstractIndex
{
    public function execute()
    {
        $this->getResponse()
            ->clearHeader('Location')
            // ->clearRawHeader('Location')
            ->setHttpResponseCode(200)
            ->setBody(
                $this->_view->getLayout()->createBlock('Plumrocket\Newsletterpopup\Block\Template')->toHtml()
            );
    }
}
