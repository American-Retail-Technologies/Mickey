<?php
/*------------------------------------------------------------------------
# SM Market - Version 1.0.0
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Market\Model\Config\Source;

class ListDetail implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'detail-1', 'label' => __('Style 1')],
			['value' => 'detail-2', 'label' => __('Style 2')],
			['value' => 'detail-3', 'label' => __('Style 3')],
		];
	}
}

