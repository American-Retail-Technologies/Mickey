<?php
/*------------------------------------------------------------------------
# SM Categories - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Categories\Model\Config\Source;

class CatOrderBy implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value'=>'name', 'label'=>__('Name')],
			['value'=>'position', 'label'=>__('Position')],
			['value'=>'random', 'label'=>__('Random')]
		];
	}
}