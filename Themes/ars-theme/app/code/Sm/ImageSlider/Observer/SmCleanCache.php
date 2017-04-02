<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\ImageSlider\Observer;

use Magento\Framework\Event\ObserverInterface;

class SmCleanCache implements ObserverInterface {
    public function execute(\Magento\Framework\Event\Observer $observer){
        if (!defined('MAGENTECH_CLEAR_CACHE')) {
			$event = $observer->getEvent();
			$layout = $event->getLayout();
			$name = $event->getElementName();
			$block = $layout->getBlock($name);
			$transport = $event->getTransport();
			if($block instanceof \Magento\Backend\Block\Cache\Additional) {
				$transport = $event->getTransport();
				$insert = '<p>
					<button onclick="setLocation(\''. $block->getUrl('*/*/smClean').'\')" type="button">
						'.__('Flush Cache Sm Module').'
					</button>
					<span>'.__('Flush Cache All Sm Module').'</span>
				</p>';	
				$dom = new \DOMDocument();
				$dom->loadHTML( $transport->getData('output'));
				$p = $dom->createDocumentFragment();
				$p->appendXML($insert);
				$dom->getElementsByTagName('div')->item(0)->insertBefore($p, $dom->getElementsByTagName('div')->item(0)->getElementsByTagName('p')->item(0));
				$transport->setData('output', $dom->saveHTML());
				define('MAGENTECH_CLEAR_CACHE', 1);
			}
			
		}
    }
}
