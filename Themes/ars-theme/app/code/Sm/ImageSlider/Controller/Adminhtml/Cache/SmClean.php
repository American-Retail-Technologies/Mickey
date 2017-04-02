<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\ImageSlider\Controller\Adminhtml\Cache;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class SmClean extends \Magento\Backend\App\Action
{	
	protected $_directory;
	protected $_driver;
	public function __construct(Filesystem $filesystem,
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Filesystem\Driver\File $driver){
		$this->_directory = $filesystem;
		$this->_driver = $driver;
		parent::__construct($context);
	}
	public function execute()
    {
		try {
			$dir = $this->_directory->getDirectoryWrite(DirectoryList::CACHE);
			$folder_cache = $dir->getAbsolutePath();
			$folder_cache = $folder_cache.'/Sm/';
			if(file_exists($folder_cache))
			{
				$this->_driver->deleteDirectory($folder_cache);
				$this->messageManager->addSuccess(__('The SM Cache was cleaned.'));
			}
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while clearing the image cache.'));
        }
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*');
    }
}
