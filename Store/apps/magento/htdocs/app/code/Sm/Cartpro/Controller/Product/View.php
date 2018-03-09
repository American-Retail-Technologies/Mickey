<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Product;


use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;


class View extends \Magento\Catalog\Controller\Product\View
{

	public function execute()
	{
		if($this->getRequest()->getParam('product'))
			$productId = (int)$this->getRequest()->getParam('product');
	
		if (!$productId) {
			return false;
		}else{
			$this->getRequest()->setParam('id', $productId);
			$product = $this->_initProduct();
			
			$productType = $product->getTypeID();
			$layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
			if ($productType == 'bundle') {
				$layout->getUpdate()->load(['cartpro_index_index_type_bundle']);
            }elseif($productType == 'grouped'){
				$layout->getUpdate()->load(['cartpro_index_index_type_grouped']);	
			}
            elseif($productType == 'simple'){
				$layout->getUpdate()->load(['cartpro_index_index_type_simple']);	
			}
            elseif($productType == 'downloadable'){
				$layout->getUpdate()->load(['cartpro_index_index_type_downloadable']);	
			}				
			else{
				$layout->getUpdate()->load(['cartpro_index_index_type_configurable']);
			}	
			$product_info = $layout->getOutput();
            $output = array('name_product' => $product->getName(),'items_markup' => $product_info);
			return $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($output));
		 }
	}
}