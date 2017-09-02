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

namespace Plumrocket\Newsletterpopup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('plumrocket_newsletterpopup_history', 'entity_id');
    }

    public function getActionMainTable()
    {
        return $this->getTable('plumrocket_newsletterpopup_history_action');
    }

    public function insertOnDuplicate($table, array $data, array $fields = [])
    {
        // $table = $this->_resources->getTableName($table);
        // $table = $this->getTable($table)
        $this->_getConnection('write')->insertOnDuplicate($table, $data, $fields);
        return $this->_getConnection('write')->lastInsertId();
    }
}
