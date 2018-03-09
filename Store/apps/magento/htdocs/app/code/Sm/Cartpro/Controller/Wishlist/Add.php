<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Wishlist;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Wishlist\Controller\Index\Add
{
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
		if ($wishlist) {
			$session = $this->_customerSession;

			$requestParams = $this->getRequest()->getParams();

			if ($session->getBeforeWishlistRequest()) {
				$requestParams = $session->getBeforeWishlistRequest();
				$session->unsBeforeWishlistRequest();
			}

			$productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
			/** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */


			try {
				$product = $this->productRepository->getById($productId);
			} catch (NoSuchEntityException $e) {
				$product = null;
			}

			if($product){
				try {					
					$buyRequest = new \Magento\Framework\DataObject($requestParams);
					$result = $wishlist->addNewItem($product, $buyRequest);
					if (is_string($result)) {
						throw new \Magento\Framework\Exception\LocalizedException(__($result));
					}
					$wishlist->save();

					$this->_eventManager->dispatch(
						'wishlist_add_product',
						['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
					);

					$this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
					$output = array(
						'success' => 1,
						'message' => __('You added product %1 to the <a href="'.$this->_objectManager->get('Magento\Wishlist\Helper\Data')->getListUrl().'">'.__('Wishlist').'</a>.', $product->getName()),
					);
				} catch (\Magento\Framework\Exception\LocalizedException $e) {
					$output = array(
						'success' => 0,
						'message' => __('We can\'t add the item to Wish List right now: %1.', $e->getMessage()),
					);
				} catch (\Exception $e) {
					$output = array(
						'success' => 0,
						'message' => __('We can\'t add the item to Wish List right now.'),
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
