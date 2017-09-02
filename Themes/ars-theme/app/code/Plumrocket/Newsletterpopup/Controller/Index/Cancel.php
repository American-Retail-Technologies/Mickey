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

class Cancel extends AbstractIndex
{
    public function execute()
    {
        $this->_subscriber->cancel();
        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(json_encode(['error' => 0, 'messages' => []]));
    }
}
