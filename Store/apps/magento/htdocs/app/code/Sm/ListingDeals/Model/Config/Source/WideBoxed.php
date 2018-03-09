<?php
/*------------------------------------------------------------------------
# SM Listing Deals - Version 1.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingDeals\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class WideBoxed implements ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'wide', 'label' => __('Wide')],
			['value' => 'boxed', 'label' => __('Boxed')]
		];
	}
}