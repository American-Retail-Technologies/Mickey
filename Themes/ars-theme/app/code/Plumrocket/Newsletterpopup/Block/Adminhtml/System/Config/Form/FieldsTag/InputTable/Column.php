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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag\InputTable;

use Magento\Backend\Block\Widget\Grid\Column\Extended;
use Magento\Framework\DataObject;

class Column extends Extended
{
    protected $_rowKeyValue = null;

    public function getId()
    {
        return $this->getName();
        $id = str_replace(['][', '[', ']'], '-', $this->getName());
        $id = preg_replace('#-$#', '', $id);
        return $id;
    }

    public function getRowField(DataObject $row)
    {
        if (null !== $this->getGrid()->getRowKey()) {
            $this->_rowKeyValue = $row->getData($this->getGrid()->getRowKey());
        }
        if (!$this->_rowKeyValue) {
            return '';
        }
        return parent::getRowField($row);
    }

    public function getFieldName()
    {
        return $this->getName();
    }

    public function getHtmlName()
    {
        return $this->getName();
    }

    public function getName()
    {
        return sprintf(
            '%s[%s][%s]',
            $this->getGrid()->getContainerFieldId(),
            $this->_rowKeyValue,
            parent::getId()
        );
    }
}
