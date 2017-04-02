<?php
/**------------------------------------------------------------------------
 * SM Deals - Version 2.1.0
 * Copyright (c) 2015 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Deals\Model\Config\Source;

class OrderDirection implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value'=>'ASC', 'label'=>__('Asc')],
			['value'=>'DESC', 'label'=>__('Desc')]
		];
	}
}