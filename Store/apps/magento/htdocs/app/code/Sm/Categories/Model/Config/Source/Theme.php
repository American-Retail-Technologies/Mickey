<?php
/*------------------------------------------------------------------------
# SM Categories - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Categories\Model\Config\Source;

class Theme implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'theme1', 'label'=>__('Theme 1')],
			['value' => 'theme2', 'label'=>__('Theme 2')],
			['value' => 'theme3', 'label'=>__('Theme 3')],
			['value' => 'theme4', 'label'=>__('Theme 4')],
		];
	}
}