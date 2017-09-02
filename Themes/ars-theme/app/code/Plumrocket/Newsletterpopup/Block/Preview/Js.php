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

namespace Plumrocket\Newsletterpopup\Block\Preview;

use Magento\Framework\View\Element\Template as ViewTemplate;
use Plumrocket\Newsletterpopup\Block\Js as JsBase;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;

class Js extends JsBase
{
    protected function _toHtml()
    {
        return ViewTemplate::_toHtml();
    }

    public function getPopupArea()
    {
        return Show::ON_ACCOUNT_PAGES;
    }

    public function isEnableAnalytics()
    {
        return false;
    }

    public function getJsonConfig()
    {
        $config = array_merge(
            json_decode(parent::getJsonConfig(), true),
            ['is_preview' => true]
        );

        return json_encode($config);
    }
}
