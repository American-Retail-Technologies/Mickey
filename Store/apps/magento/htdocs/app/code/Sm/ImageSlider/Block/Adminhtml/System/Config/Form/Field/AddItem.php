<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\ImageSlider\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\View\Layout;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class AddItem extends AbstractFieldArray
{
	/**
	 * Adminhtml data
	 *
	 * @var \Magento\Backend\Helper\Data
	 */
	protected $_backendData = null;
	protected $_blockFactory;
	/**
	 * Rows cache
	 *
	 * @var array|null
	 */
	private $_arrayRowsCache;

	public function __construct(
		Context $context,
		Data $backendData,
		Layout $layout,
		array $data = []
	)
	{
		$this->_backendData = $backendData;
		$this->_blockFactory = $layout;
		parent::__construct($context, $data);
	}

	protected function _construct()
	{
		$this->addColumn('title', [
			'label' => __('Title '),
			'style' => 'width:120px',
			'class'     => 'required-entry',
			'required'  => true
		]);

		$this->addColumn('link', [
			'label' => __('Link'),
			'style' => 'width:120px'
		]);

		$this->addColumn('image', [
			'label' => __('Media'),
			'style' => 'width:140px',
			'class'     => 'required-entry',
			'onclick' => "",
			'required'  => true
		]);

		$this->addColumn('content', [
			'label' => __('Content'),
			'style' => 'width:180px'
		]);
		$this->_addAfter = false;
		$this->_addButtonLabel = __('Add Items');
		parent::_construct();
	}
	
	 protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        $this->_arrayRowsCache = null;
        // doh, the object is used as singleton!
        $html = '<div id="imageslider_source_product_additem">' . $html . '</div>';
        return $html;
    }
}