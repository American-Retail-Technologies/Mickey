<?php
/*------------------------------------------------------------------------
 # SM Listing Tabs - Version 2.2.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingTabs\Controller\Index;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action {
	/** @var  \Magento\Framework\View\Result\Page */
	protected $resultPageFactory;
	protected $jsonEncoder;
	protected $_layout;
	protected $response;
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 */
	public function __construct(
		Context $context, 
		PageFactory $resultPageFactory,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
		 \Magento\Framework\App\Response\Http $response)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->jsonEncoder = $jsonEncoder;
		$this->_layout = $layout;
		$this->response = $response;
		parent::__construct($context);
	}

	/**
	 * Blog Index, shows a list of recent blog posts.
	 *
	 * @return \Magento\Framework\View\Result\PageFactory
	 */
	public function execute()
	{
		$isAjax = $this->getRequest()->isAjax();
		if ($isAjax){
			$layout =  $this->_layout;
			$layout->getUpdate()->load(['listingtabs_index_ajax']);
			$layout->generateXml();
            $output = $layout->getOutput();
            $this->getResponse()->setHeader('Content-type', 'application/json');

			die($this->jsonEncoder->encode(array('items_markup' => $output)));
        }
		$this->response->setRedirect($this->_redirect->getRedirectUrl());
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend(__('SM Listing Tabs'));
		return $resultPage;
	}
}