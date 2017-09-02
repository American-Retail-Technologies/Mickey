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

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Template;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Plumrocket\Newsletterpopup\Model\Template', 'Plumrocket\Newsletterpopup\Model\ResourceModel\Template');
    }

    public function addFieldToFilter($field, $alias = null)
    {
        if ($field == 'template_type') {
            $field = 'base_template_id';
            if (isset($alias['eq']) && $alias['eq'] != -1) {
                $alias = [
                    'neq' => -1
                ];
            }
        }

        return parent::addFieldToFilter($field, $alias);
    }
}
