<?php
/*------------------------------------------------------------------------
 # SM Cart Pro - Version 2.2.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\Cartpro\Controller\Cart;

use Magento\Catalog\Block\Product\View;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    public function execute()
    {
		if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
			//if (isset($params['item'])){
				//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				//$product = $objectManager->get('Magento\Catalog\Model\Product')->load($params['item']);
			//}				
			//else
				$product = $this->_initProduct();
		
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

			$output = array(
				'success' => false,
				'message' =>  __('You added %1 to your shopping cart.',$product->getName())
			);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {			
			$output = array(
				'success' => true,
			);
        } catch (\Exception $e) {
			$output = array(
				'success' => true,
				'message' => __('We can\'t add this item to your shopping cart right now.'),
			);			
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
		if(isset($output) && $output){
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$jsonEncoder = $productName = $this->_objectManager->get('Magento\Framework\Json\EncoderInterface');
			die($jsonEncoder->encode(array('items_markup' => $output)));
		}	
    }
}
