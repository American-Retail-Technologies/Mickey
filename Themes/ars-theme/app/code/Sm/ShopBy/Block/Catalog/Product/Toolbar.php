<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.1.0
 # Copyright (c) 2016 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ShopBy\Block\Catalog\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar{
    protected $_registry;
	protected $_catalogSession;
	protected $catalogConfig;
	protected $urlEncoder;
	protected $productListHelper;
	protected $postDataHelper;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Helper\Product\ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,

        Registry $registry,
        array $data = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_catalogConfig = $catalogConfig;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_productListHelper = $productListHelper;
        $this->_postDataHelper = $postDataHelper;

        $this->_registry=$registry;
        parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper);
    }
}