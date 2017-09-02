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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;

class InputTable extends Extended implements RendererInterface
{
    protected $_element;
    protected $_containerFieldId = null;
    protected $_rowKey = null;

    // ******************************************
    // *                                        *
    // *           Grid functions               *
    // *                                        *
    // ******************************************
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $column['sortable'] = false;
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock('Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag\InputTable\Column')
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function canDisplayContainer()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        return Widget::_prepareLayout();
    }

    public function setArray($array)
    {
        $collection = $this->_collectionFactory->create();
        $i = 1;
        foreach ($array as $item) {
            if (!$item instanceof DataObject) {
                $item = new DataObject($item);
            }
            if (!$item->getId()) {
                $item->setId($i);
            }
            $collection->addItem($item);
            $i++;
        }
        $this->setCollection($collection);
        return $this;
    }

    public function getRowKey()
    {
        return $this->_rowKey;
    }

    public function setRowKey($key)
    {
        $this->_rowKey = $key;
        return $this;
    }

    public function getContainerFieldId()
    {
        return $this->_containerFieldId;
    }

    public function setContainerFieldId($name)
    {
        $this->_containerFieldId = $name;
        return $this;
    }

    // ******************************************
    // *                                        *
    // *           Render functions             *
    // *                                        *
    // ******************************************

    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return '
            <tr>
                <td class="label">' . $element->getLabelHtml() . '</td>
                <td class="value">' . $this->toHtml() . '</td>
            </tr>';
    }

    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html = preg_replace('/<div class="messages">.*<\/div>/i', '', $html);
        $html = str_replace(
            '<div class="admin__data-grid-wrap',
            '<div id="' . $this->getHtmlId() . '_wrap" class="admin__data-grid-wrap',
            $html
        );
        $html .= $this->_getCss();
        return $html;
    }

    protected function _getCss()
    {
        $id = '#' . $this->getHtmlId() . '_wrap';
        return "<style>
            $id {
                margin-bottom: 0;
                padding-bottom: 0;
                padding-top: 0;
            }
            $id td {
                padding: 1rem;
                vertical-align: middle;
            }
            $id td input.checkbox[disabled] {
                display: none;
            }
            $id tr.not-active td,
            $id tr.not-active input.input-text {
                color: #999999;
            }
        </style>";
    }
}
