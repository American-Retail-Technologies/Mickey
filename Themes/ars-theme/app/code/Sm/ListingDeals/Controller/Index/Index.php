<?php
/**------------------------------------------------------------------------
* SM Listing Deals - Version 1.1.0
* Copyright (c) 2015 YouTech Company. All Rights Reserved.
* @license - Copyrighted Commercial Software
* Author: YouTech Company
* Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\ListingDeals\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action {
	/**
	 *@var  \Magento\Framework\View\Result\Page
	 */
	protected $resultPageFactory;

	/**
	 * @var \Magento\Framework\Json\EncoderInterface
	 */
	protected $jsonEncoder;

	/**
	 * @var \Magento\Framework\View\LayoutInterface
	 */
	protected $_layout;
	protected $response;

	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\View\LayoutInterface $layout
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\LayoutInterface $layout,
		\Magento\Framework\App\Response\Http $response,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		$this->_layout = $layout;
		$this->response = $response;
		$this->jsonEncoder = $jsonEncoder;
		$this->resultPageFactory = $resultPageFactory;
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
			$layout->getUpdate()->load(['listingdeals_index_ajax']);
			$layout->generateXml();
			$output = $layout->getOutput();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			die($this->jsonEncoder->encode(['items_markup' => $output]));
		}
		$this->response->setRedirect($this->_redirect->getRedirectUrl());
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend(__('Sm Listing Deals'));
		return $resultPage;
	}
}