<?php
/*------------------------------------------------------------------------
# SM Mega Menu - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\MegaMenu\Model\Config\Source;

class PositionItem implements \Magento\Framework\Option\ArrayInterface
{
	const BEFORE    = 1;
	const AFTER	    = 2;
	const FIRST		= 3;

	public function getOptionArray()
	{
		return [
			self::AFTER     => __('After'),
			self::BEFORE    => __('Before')
		];
	}

	public function toOptionArray()
	{
		return [
			[
				'value'     => self::AFTER,
				'label'     => __('After'),
			],
			[
				'value'     => self::BEFORE,
				'label'     => __('Before'),
			]
		];
	}
}