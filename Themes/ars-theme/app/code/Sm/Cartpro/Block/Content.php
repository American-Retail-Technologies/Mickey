<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
namespace Sm\Cartpro\Block;

use Magento\Framework\View\Element\Template;

class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'content.phtml';

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        parent::__construct($context, $data);
    }
}
 
