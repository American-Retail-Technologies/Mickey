<?php
/*------------------------------------------------------------------------
# SM Basic Products - Version 2.2.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\BasicProducts\Model\Config\Source;

class ColumnDevices implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		$array = array(1,2,3,4,5,6);
		$data = [];
		foreach ($array as $a)
		{
			$data[] = ['value' => $a, 'label' => __($a)];
		}
		$option =  [];
		foreach ($data as $d)
		{
			$option[] = $d;
		}
		return $option;
	}
}
