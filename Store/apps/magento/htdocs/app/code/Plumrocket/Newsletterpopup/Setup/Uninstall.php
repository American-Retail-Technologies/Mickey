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

namespace Plumrocket\Newsletterpopup\Setup;

use Plumrocket\Base\Setup\AbstractUninstall;

class Uninstall extends AbstractUninstall
{
    protected $_configSectionId = 'prnewsletterpopup';
    protected $_pathes = ['/app/code/Plumrocket/Newsletterpopup'];
    protected $_tables =
    [
        'plumrocket_newsletterpopup_popups',
        'plumrocket_newsletterpopup_history',
        'plumrocket_newsletterpopup_mailchimp_list',
        'plumrocket_newsletterpopup_form_fields',
        'plumrocket_newsletterpopup_hold',
        'plumrocket_newsletterpopup_templates',
        'plumrocket_newsletterpopup_history_action',
    ];
}
