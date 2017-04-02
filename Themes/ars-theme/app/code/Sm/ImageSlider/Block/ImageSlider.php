<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ImageSlider\Block;

use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\Filesystem;
use Sm\ImageSlider\Block\Cache\Lite;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ImageSlider extends AbstractProduct
{
	protected $_directory;
	protected $_config = null;
	protected $_storeId = null;
	protected $_objectManager;
	protected $_scopeConfigInterface;

	/**
	 * @var \Magento\Backend\Block\Template
	 */
	protected $_block;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	public function __construct(
		ObjectManagerInterface $objectManager,
		\Magento\Backend\Block\Template $template,
		PageFactory $resultPageFactory,
		Context $context,
		array $data = [],
		$attr = null
	)
	{
		$this->_objectManager = $objectManager;
		$this->_directory = $this->_objectManager->get('\Magento\Framework\Filesystem');
		$this->resultPageFactory = $resultPageFactory;
		$this->_storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_storeId = (int)$this->_storeManager->getStore()->getId();
		$this->_block = $template;
		$this->_config = $this->_getCfg($attr, $data);
		parent::__construct($context, $data);
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _getCfg($attr = null, $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('imageslider');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('imageslider');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('imageslider/'.$group.'', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			foreach ($_cfgs as $_key => $_cfg) {
				$cfgs[$_key] = $_cfg;
			}
		}

		// get output config
		$configs = [];
		foreach ($defaults as $key => $def) {
			if (isset($defaults[$key])) {
				$configs[$key] = $cfgs[$key];
			} else {
				unset($cfgs[$key]);
			}
		}
		$cf = ($attr != null) ? array_merge($configs, $attr) : $configs;
		$this->_config = ($data != null) ? array_merge($cf, $data) : $cf;
		return $this->_config;
	}

	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

	public function _setConfig($name, $value = null)
	{

		if (is_null($this->_config)) $this->_getCfg();
		if (is_array($name)) {
			$this->_config = array_merge($this->_config, $name);

			return;
		}
		if (!empty($name) && isset($this->_config[$name])) {
			$this->_config[$name] = $value;
		}
		return true;
	}

	protected function _toHtml()
	{
		if (!$this->_getConfig('isenabled', 1)) return;
		$use_cache = (int)$this->_getConfig('use_cache');
		$cache_time = (int)$this->_getConfig('cache_time');
		$folder_cache = $this->_directory->getDirectoryWrite(DirectoryList::CACHE)->getAbsolutePath();
		$folder_cache = $folder_cache.'Sm/ImageSlider/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);

		$options = [
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		];
		$Cache_Lite = new \Sm\ImageSlider\Block\Cache\Lite($options);
		if ($use_cache){
			$hash = md5( serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode()]) );
			if ($data = $Cache_Lite->get($hash)) {
				return  $data;
			} else {
				$template_file = $this->getTemplate();
				$template_file = (!empty($template_file)) ? $template_file : "Sm_ImageSlider::default.phtml";
				$this->setTemplate($template_file);
				$data = parent::_toHtml();
				$Cache_Lite->save($data);
			}
		}else{
			if(file_exists($folder_cache))
				$Cache_Lite->_cleanDir($folder_cache);
			$template_file = $this->getTemplate();
			$template_file = (!empty($template_file)) ? $template_file : "Sm_ImageSlider::default.phtml";
			$this->setTemplate($template_file);
		}

		return parent::_toHtml();
	}

	public function _helper(){
		return $this->_objectManager->get('\Sm\ImageSlider\Helper\Data');
	}

	public function _getProductMedia()
	{
		$items = $this->_getConfig('product_additem');
		$items = unserialize($items);
		if (empty($items)) return;
		return $items;
	}

	protected function _getImageSliderDirMedia()
	{
		$dir = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $dir;
	}

	public function _getProducts()
	{
		$helper = $this->_helper();
		$items = $this->_getProductMedia();

		$image_config = [
			'width' => (int)$this->_getConfig('img_width', 200),
			'height' => $this->_getConfig('img_height', null),
			'background' => (string)$this->_getConfig('img_background'),
			'function' => (int)$this->_getConfig('img_function')
		];

		$list = [];
		$i = 0;
		if (!empty($items)) {
			foreach ($items as $item) {
				$i++;
				if ($item['image'] != '' && $item['title'] != '') {
					$item['image'] = (strpos($item['image'], 'http') !== false) ? $item['image'] : $this->_getImageSliderDirMedia() . $item['image'];
					if ($this->_getConfig('img_function') == 1) {
						$item['_image'] = $helper->_resizeImage($item['image'], $image_config);
					} else {
						$item['_image'] = $item['image'];
					}

					if (@getimagesize($item['_image']) == false) {
						$placeholder = $this->_getConfig('img_replacement');
						$img_placeholder = ($placeholder != '' && strpos($placeholder, 'http') !== false) ? $placeholder :  $this->_block->getViewFileUrl('Sm_ImageSlider::images/nophoto.jpg');
						$img_placeholder = @getimagesize($img_placeholder) !== false ? $img_placeholder : $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). $placeholder;
						if ($this->_getConfig('img_function') == 1) {
							$item['_image'] = $helper->_resizeImage($img_placeholder, $image_config);
						} else {
							$item['_image'] = $img_placeholder;
						}
					}

					$_title = $helper->_cleanText($item['title']);
					$item['title'] = $helper->truncate($_title, $this->_getConfig('product_title_maxlength'));

					$description = $helper->_cleanText($item['content']);
					$description = $helper->truncate($description, $this->_getConfig('product_description_maxlength'));
					$item['_description'] = $description;
					$list[] = $item;
				}
			}
		}
		return $list;
	}


}