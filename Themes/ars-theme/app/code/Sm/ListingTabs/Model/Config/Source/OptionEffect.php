<?php
/*------------------------------------------------------------------------
 # SM Listing Tabs - Version 2.2.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingTabs\Model\Config\Source;

class OptionEffect implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'slideLeft', 'label' => __('Slide Left')],
			['value' => 'slideRight', 'label' => __('Slide Right')],
			['value' => 'zoomOut', 'label' => __('Zoom Out')],
			['value' => 'zoomIn', 'label' => __('Zoom In')],
			['value' => 'flip', 'label' => __('Flip')],
			['value' => 'flipInX', 'label' => __('Fip in Vertical')],
			['value' => 'starwars', 'label' => __('Star Wars')],
			['value' => 'flipInY', 'label' => __('Flip in Horizontal')],
			['value' => 'bounceIn', 'label' => __('Bounce In')],
			['value' => 'fadeIn', 'label' => __('Fade In')],
			['value' => 'pageTop', 'label' => __('Page Top')],
		];
	}
}