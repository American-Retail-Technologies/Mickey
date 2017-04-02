<?php
/*------------------------------------------------------------------------
# SM QuickView - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\QuickView\Controller\Index;


use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;


class Index extends \Magento\Catalog\Controller\Product\View
{

	public function execute()
	{
		$isAjax = $this->getRequest()->isAjax();
		if ($isAjax){
			$manager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
			$store_id =  $manager->getStore()->getId();
			// get connnect pdo
			$_resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
			$conn =  $_resource->getConnection('core_read');
			$_path = $this->getRequest()->getParam('path') ? $this->getRequest()->getParam('path') : strstr($this->_request->getRequestUri(), '/path');
			$_path = str_replace("/path/",'',$_path );
			$_path = (strpos($_path, '?') !== false) ? substr($_path , strpos($_path, '?') ) : $_path;
			// escape url path
			$str = $conn->quote($_path);
			$url_rewrite = $_resource->getTableName('url_rewrite');
			$select =  $conn->select()
				->from(array('rp' => $url_rewrite), new \Zend_Db_Expr('entity_id'))
				->where('rp.request_path in ('.$str.')')
				->where('rp.store_id = ?', $store_id);
			$productId =  $conn->fetchOne($select);
			
			if (!$productId) {
				return false;
			}else
			 {
				 $this->getRequest()->setParam('id', $productId);
				 $product = $this->_initProduct();
				 $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
				 switch($product->getTypeId()){
					 case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE :
						 $layout->getUpdate()->load(['product_type_bundle']);
					 break;
					 case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE :
						 $layout->getUpdate()->load(['product_type_downloadable']);
					 break;
					 case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE :
						  $layout->getUpdate()->load(['product_type_grouped']);
					 break;
					 case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE :
						  $layout->getUpdate()->load(['product_type_simple']);
					 break;
					 default:
						$layout->getUpdate()->load(['product_type_configurable']);
				 }
				
				 $product_info = $layout->getOutput();
				 $output = ['sucess' => true,'type_product' => $product->getTypeId(), 'title' => $product->getName(),'item_mark' => $product_info];
				return $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($output));
			 }
	   }else{
		  return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
	   }	 
	}
}