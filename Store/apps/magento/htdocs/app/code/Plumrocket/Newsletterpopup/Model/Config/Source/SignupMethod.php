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

class SignupMethod extends Base
{
    const SIGNUP_ONLY         = 'signup_only';
    const SIGNUP_AND_REGISTER = 'register_signup';

    public function toOptionHash()
    {
        return [
            self::SIGNUP_ONLY         => __('Sign-up for email newsletter only'),
            self::SIGNUP_AND_REGISTER => __('Register customer account & sign-up for newsletter'),
        ];
    }
}
