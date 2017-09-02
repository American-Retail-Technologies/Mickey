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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\History\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\DataObject;
use Plumrocket\Newsletterpopup\Helper\Data;

class Order extends AbstractRenderer
{
    protected $_dataHelper;
    protected $_backendHelper;

    public function __construct(
        Context $context,
        Data $dataHelper,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_backendHelper = $backendHelper;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        if ($row->getOrderId()) {
            return sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $this->_backendHelper->getUrl('sales/order/view', ['order_id' => $row->getOrderId()]),
                '#' . $row->getIncrementId()
            );
        }
        return '';
    }
}
