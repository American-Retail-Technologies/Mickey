<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.1.0
 # Copyright (c) 2016 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ShopBy\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;

/**
 * Interface FilterRendererInterface
 * @api
 */
interface FilterRendererInterface
{
    /**
     * Render filter
     *
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter);
}
