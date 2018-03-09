<?php
/*------------------------------------------------------------------------
 # SM Listing Tabs - Version 2.2.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingTabs\Block\Adminhtml\System\Config\Form\Field;
use \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
class AddItem extends AbstractFieldArray
{
	/**
	 * Rows cache
	 *
	 * @var array|null
	 */
	private $_arrayRowsCache;

	protected function _construct()
	{
		$this->addColumn('title', [
			'label' => __('Title '),
			'style' => 'width:120px'
		]);
		$this->addColumn('link', [
			'label' => __('Link'),
			'style' => 'width:120px'
		]);
		$this->addColumn('image', [
			'label' => __('Media'),
			'style' => 'width:120px'
		]);
		$this->addColumn('content', [
			'label' => __('Content'),
			'style' => 'width:220px'
		]);
		$this->_addAfter = false;
		$this->_addButtonLabel = __('Add Items');
		parent::_construct();
	}
	
	 protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        $this->_arrayRowsCache = null; // doh, the object is used as singleton!
        $html = '<div id="basicproducts_source_product_additem">' . $html . '</div>';
        return $html;
    }
}