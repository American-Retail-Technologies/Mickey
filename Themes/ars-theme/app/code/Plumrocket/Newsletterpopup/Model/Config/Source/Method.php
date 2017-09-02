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

class Method extends Base
{
    const AFTER_TIME_DELAY  = 'after_time_delay';
    const LEAVE_SITE        = 'leave_page';
    const PAGE_SCROLL       = 'on_page_scroll';
    const MOUSEOVER         = 'on_mouseover';
    const CLICK             = 'on_click';
    const MANUALLY          = 'manually';
    //const CLOSE_PAGE        = 'close_page';

    public function toOptionHash()
    {
        return [
            self::AFTER_TIME_DELAY  => __('After time delay'),
            self::LEAVE_SITE        => __('When leaving site (out of focus)'),
            self::PAGE_SCROLL       => __('On Page Scroll'),
            self::MOUSEOVER         => __('On Mouse Over'),
            self::CLICK             => __('On Click'),
            //self::CLOSE_PAGE        => __('On window close'),
            self::MANUALLY          => __('Manually (for web developers)'),
        ];
    }
}
