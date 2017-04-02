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

class Target implements ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => '_self', 'label' => __('Same Window')],
			['value' => '_blank', 'label' => __('New Window')],
			['value' => '_windowopen', 'label' => __('Popup Window')]
		];
	}
}