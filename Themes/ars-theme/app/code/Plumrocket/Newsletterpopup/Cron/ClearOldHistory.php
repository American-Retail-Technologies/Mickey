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

namespace Plumrocket\Newsletterpopup\Cron;

use Magento\Framework\App\ResourceConnection;
use Plumrocket\Newsletterpopup\Helper\Data;

class ClearOldHistory
{
    protected $_dataHelper;
    protected $_resourceConnection;

    public function __construct(
        Data $dataHelper,
        ResourceConnection $resourceConnection
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        if ($this->_dataHelper->moduleEnabled() && $this->_dataHelper->getConfig(Data::SECTION_ID . '/general/enable_history')) {
            // count of months
            $offset = (int)$this->_dataHelper->getConfig(Data::SECTION_ID . '/general/erase_history') * 86400;
            // if 0 then never erase
            if ($offset) {
                $this->_resourceConnection->getConnection('write')
                    ->query(sprintf(
                        "DELETE FROM %s WHERE `date_created` <= '%s'",
                        $this->_resourceConnection->getTableName('plumrocket_newsletterpopup_history'),
                        strftime('%F %T', time() - $offset)
                    ));
            }
        }
    }
}
