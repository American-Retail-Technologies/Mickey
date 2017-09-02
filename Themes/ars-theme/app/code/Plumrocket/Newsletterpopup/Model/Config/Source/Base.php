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

use Magento\Cms\Model\Page;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Option\ArrayInterface;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Model\Template;

class Base implements ArrayInterface
{
    protected $_adminhtmlHelper;
    protected $_template;
    protected $_page;

    public function __construct(
        Adminhtml $adminhtmlHelper,
        Template $template,
        Page $page
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_template = $template;
        $this->_page = $page;
    }

    public function toOptionArray()
    {
        $values = $this->toOptionHash();
        $result = [];

        foreach ($values as $key => $value) {
            $result[] = [
                'value'    => $key,
                'label'    => $value,
            ];
        }
        return $result;
    }
}
