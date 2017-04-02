<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Compare;

class Remove extends \Magento\Catalog\Controller\Product\Compare\Remove
{
    /**
     * Remove item from compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		if($this->getRequest()->isAjax()){
			$productId = (int)$this->getRequest()->getParam('product');
			if ($productId) {
				$storeId = $this->_storeManager->getStore()->getId();
				try {
					$product = $this->productRepository->getById($productId, false, $storeId);
				} catch (NoSuchEntityException $e) {
					$product = null;
				}

				if ($product) {
					/** @var $item \Magento\Catalog\Model\Product\Compare\Item */
					$item = $this->_compareItemFactory->create();
					if ($this->_customerSession->isLoggedIn()) {
						$item->setCustomerId($this->_customerSession->getCustomerId());
					} elseif ($this->_customerId) {
						$item->setCustomerId($this->_customerId);
					} else {
						$item->addVisitorId($this->_customerVisitor->getId());
					}

					$item->loadByProduct($product);
					/** @var $helper \Magento\Catalog\Helper\Product\Compare */
					$helper = $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare');
					if ($item->getId()) {
						$item->delete();
						$productName = $this->_objectManager->get('Magento\Framework\Escaper')
							->escapeHtml($product->getName());
						$this->_eventManager->dispatch(
							'catalog_product_compare_remove_product',
							['product' => $item]
						);
						$helper->calculate();
						
						$layout =  $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
						$layout->getUpdate()->load(['cartpro_compare_list']);
						$block_compare = $layout->getOutput();
						$output = array(
							'success' => 1,
							'message' => __('You removed product %1 from the comparison list.', $productName),
							'block_compare' => $block_compare,
						);
					}else{
						$output = array(
							'success' => 0,
							'message' => __('Can not remove item to comparison list.'),
						);
					}
					$this->getResponse()->setHeader('Content-type', 'application/json');
					$jsonEncoder = $productName = $this->_objectManager->get('Magento\Framework\Json\EncoderInterface');
					die($jsonEncoder->encode(array('items_markup' => $output)));
				}
			}
		}
    }
}
