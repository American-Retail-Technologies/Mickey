<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ImageSlider\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
class Theme implements  ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'theme1', 'label' => __('Theme 1')],
			['value' => 'theme2', 'label' => __('Theme 2')]
		];
	}
}