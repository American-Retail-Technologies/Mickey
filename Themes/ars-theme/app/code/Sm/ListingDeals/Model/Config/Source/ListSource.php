<?php
/*------------------------------------------------------------------------
# SM Listing Deals - Version 1.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\ListingDeals\Model\Config\Source;

class ListSource implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'catalog', 'label' => __('Catalog')],
			['value' => 'fieldproducts', 'label' => __('Field Product')]
		];
	}
}