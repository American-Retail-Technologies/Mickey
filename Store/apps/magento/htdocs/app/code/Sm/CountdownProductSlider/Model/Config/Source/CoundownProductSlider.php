<?php
/*------------------------------------------------------------------------
# SM Count Down Product Slider  - Version 1.1.0
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\CountdownProductSlider\Model\Config\Source;

class CountdownProductSlider implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'categories', 'label' => __('Categories')],
			['value' => 'fieldproducts', 'label' => __('Field Products')]
		];
	}
}