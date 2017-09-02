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

class Page extends Base
{
    protected $_options = null;

    public function toOptionHash()
    {
        if (null === $this->_options) {
            $collection = $this->_page->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('sort_order', 'asc');

            $options = [];
            foreach ($collection as $item) {
                $options[ $item->getIdentifier() ] = $item->getTitle();
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}
