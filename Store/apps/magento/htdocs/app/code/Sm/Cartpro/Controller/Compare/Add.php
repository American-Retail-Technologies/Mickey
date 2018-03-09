<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Compare;


class Add extends \Magento\Catalog\Controller\Product\Compare\Add
{
    /**
     * Add item to compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		if($this->getRequest()->isAjax()){
			if (!$this->_formKeyValidator->validate($this->getRequest())) {
				return $resultRedirect->setRefererUrl();
			}			
			$productId = (int)$this->getRequest()->getParam('product');
			if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
				$storeId = $this->_storeManager->getStore()->getId();
				try {
					$product = $this->productRepository->getById($productId, false, $storeId);
				} catch (NoSuchEntityException $e) {
					$product = null;
				}

				if ($product) {
					$this->_catalogProductCompareList->addProduct($product);
					$productName = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName());
					$this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
					$this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
					$layout =  $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
					//$layout->getUpdate()->load(['default']);
					//$catalog_compare_sidebar = $layout->getBlock('catalog_compare_sidebar')->toHtml();					
					$output = array(
						'success' => 1,
						'message' => __('You added product %1 to the comparison list.', $productName),
						'nb_items' => $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->getItemCount(),
						'catalog_compare_sidebar' => '',
					);
				}else{
					$output = array(
						'success' => 0,
						'message' => __('Can not add item to comparison list.'),
					);
				}
			
				$this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
				
				$this->getResponse()->setHeader('Content-type', 'application/json');
				$jsonEncoder = $productName = $this->_objectManager->get('Magento\Framework\Json\EncoderInterface');
				die($jsonEncoder->encode(array('items_markup' => $output)));
			}
		}
    }
}
