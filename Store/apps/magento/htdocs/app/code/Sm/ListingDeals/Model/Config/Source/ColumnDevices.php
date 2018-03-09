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

class ColumnDevices implements ArrayInterface
{
	public function toOptionArray()
	{
		$array = [1,2,3,4,5,6];
		$data = [];
		foreach ($array as $a)
		{
			$data[] = ['value' => $a, 'label' => __($a)];
		}
		return $data;
	}
}
