<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Wishlist;

class Remove extends \Magento\Wishlist\Controller\Index\Remove
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('item');
        $item = $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($id);
        if ($item->getId()) {
			$wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
			if ($wishlist) {
				try {
					$item->delete();
					$wishlist->save();
					$layout =  $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
					$layout->getUpdate()->load(['cartpro_wishlist_list']);
					$block_wishlist = $layout->getOutput();
					$output = array(
						'success' => 1,
						'block_wishlist' => $block_wishlist,
					);	
						
				} catch (\Magento\Framework\Exception\LocalizedException $e) {
					$output = array(
						'success' => 0,
						'message' => __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage()),
					);						
				} catch (\Exception $e) {
					$output = array(
						'success' => 0,
						'message' => __('We can\'t delete the item from the Wish List right now.'),
					);						
				}
			}
			$this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$jsonEncoder = $productName = $this->_objectManager->get('Magento\Framework\Json\EncoderInterface');
			die($jsonEncoder->encode(array('items_markup' => $output)));			
		}
    }
}
