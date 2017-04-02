<?php
/*------------------------------------------------------------------------
# SM Market - Version 1.0.0
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Market\Model\Config\Source;

class ListFooterStyles implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'footer-1', 'label' => __('Footer Style 1')],
			['value' => 'footer-2', 'label' => __('Footer Style 2')],
			['value' => 'footer-3', 'label' => __('Footer Style 3')],
			['value' => 'footer-4', 'label' => __('Footer Style 4')],
			['value' => 'footer-5', 'label' => __('Footer Style 5')],
			['value' => 'footer-6', 'label' => __('Footer Style 6')],
			['value' => 'footer-7', 'label' => __('Footer Style 7')],
			['value' => 'footer-8', 'label' => __('Footer Style 8')],
		];
	}
}