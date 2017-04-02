<?php
/*------------------------------------------------------------------------
 # SM Listing Tabs - Version 2.1.2
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingExtend\Model\Config\Source;

class ListSource implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'underprice', 'label' => __('Under Price')],
			['value' => 'fieldproducts', 'label' => __('Field Products')]
		];
	}
}