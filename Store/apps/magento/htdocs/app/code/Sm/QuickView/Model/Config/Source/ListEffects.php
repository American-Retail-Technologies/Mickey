<?php
/*------------------------------------------------------------------------
# SM QuickView - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\QuickView\Model\Config\Source;

class ListEffects implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value'=>'elastic', 'label'=>__('Elastic')],
			['value'=>'fade', 'label'=>__('Fade')],
			['value'=>'none', 'label'=>__('None')]
		];
	}
}