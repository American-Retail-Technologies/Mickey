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

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Redirectto extends Base
{
    const STAY_ON_PAGE  = '__stay__';
    const CUSTOM_URL    = '__custom__';
    const ACCOUNT_PAGE  = '__account__';
    const LOGIN_PAGE    = '__login__';

    public function toOptionHash()
    {
        $pages = [
            self::STAY_ON_PAGE  => 'Stay on current page',
            self::CUSTOM_URL    => 'Redirect to Custom URL',
            '__none__'          => '----',
            self::ACCOUNT_PAGE  => 'Customer -> Account Dashboard',
            self::LOGIN_PAGE    => 'Login Page',
        ];
        $items = $this->_page->getCollection();
        foreach ($items as $item) {
            $pages[ $item->getIdentifier() ] = $item->getTitle();
        }
        return $pages;
    }
}
