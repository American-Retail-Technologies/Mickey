<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.1.0
 # Copyright (c) 2016 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ShopBy\Model\Aggregation;

use Magento\Framework\Search\Request\Aggregation\StatusInterface;

class Status implements StatusInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }
}
