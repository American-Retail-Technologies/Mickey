<?php
/**------------------------------------------------------------------------
 * SM Categories - Version 3.1.0
 * Copyright (c) 2015 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Categories\Block;

class Categories extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_config = null;

	/**
	 * Currently selected store ID if applicable
	 *
	 * @var int
	 */
	protected $_storeId;

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfigInterface;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * Object manager
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	private $_objectManager;

	/**
	 * @var \Magento\Eav\Model\Config
	 */
	protected $_eavConfig;

	/**
	 * Resource
	 *
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $_resource;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $localeDate;

	/**
	 * @var \Magento\Framework\Filesystem\Directory\WriteInterface
	 */
	protected $_directory;

	/**
	 * @var \Magento\Backend\Block\Template
	 */
	protected $_block;

	/**
	 * Class constructor
	 *
	 * @param \Magento\Catalog\Block\Product\Context $context
	 * @param \Magento\Framework\App\ResourceConnection $resourceConnection
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param \Magento\Eav\Model\Config $eavConfig
	 * @param string|null $scope
	 */
	public function __construct(
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Block\Product\Context $context,
		\Magento\Backend\Block\Template $block,
		\Magento\Eav\Model\Config $eavConfig,
		array $data = [],
		$attr = null
	)
	{
		$this->_eavConfig = $eavConfig;
		$this->_resource = $resourceConnection;
		$this->_objectManager = $objectManager;
		$this->_directory = $this->_objectManager->get('\Magento\Framework\Filesystem');
		$this->_block = $block;
		$this->_storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_storeId = (int)$this->_storeManager->getStore()->getId();
		$this->localeDate = $this->_objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$this->_config = $this->_getCfg($attr, $data);
		parent::__construct($context, $data);
	}

	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('categories');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('categories');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('categories/'.$group.'', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
		$folder_cache = $this->_directory->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::CACHE)->getAbsolutePath();
		$folder_cache = $folder_cache.'Sm/Categories/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);

		$options = [
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		];
		$Cache_Lite = new \Sm\Categories\Block\Cache\Lite($options);
		if ($use_cache){
			$hash = md5( serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode()]) );
			if ($data = $Cache_Lite->get($hash)) {
				return  $data;
			} else {
				$template_file = $this->getTemplate();
				$template_file = (!empty($template_file)) ? $template_file : "Sm_Categories::default.phtml";
				$this->setTemplate($template_file);
				$data = parent::_toHtml();
				$Cache_Lite->save($data);
			}
		}else{
			if(file_exists($folder_cache))
				$Cache_Lite->_cleanDir($folder_cache);
			$template_file = $this->getTemplate();
			$template_file = (!empty($template_file)) ? $template_file : "Sm_Categories::default.phtml";
			$this->setTemplate($template_file);
		}

		return parent::_toHtml();
	}

	public function _helper()
	{
		return $this->_objectManager->get('\Sm\Categories\Helper\Data');
	}

	public function _getList()
	{
		$catids = $this->_getConfig('product_category');
		if ($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		if (empty($_catids)) return;
		$_list = $this->_getCatinfor($_catids);
		$list = [];
		foreach ( $_list as $item ) {
			$item['child_cat'] = $this->_getChildrenCat($item['id']);
			$list[] = $item;
		}
		return $list;
	}

	private function _getCatActive($catids = null, $orderby = true)
	{
		if (is_null($catids)) {
			$catids = $this->_getConfig('product_category');
		}
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);

		if (empty($catids)) return;
		$categoryIds = ['in' => $catids];
		$collection = $this->_objectManager->create('Magento\Catalog\Model\Category')->getCollection();
		$collection->addAttributeToSelect('*')
			->setStoreId($this->_storeId)
			->addAttributeToFilter('entity_id', $categoryIds)
			->addIsActiveFilter();

		if ($orderby) {
			$attribute = $this->_getConfig('category_order_by','name');
			$dir = $this->_getConfig('category_order_dir','ASC');
			switch ($attribute) {
				case 'name':
				case 'position':
				case 'entry_id':
					$collection->addAttributeToSort($attribute, $dir);
					break;
				case 'random':
					$collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
					break;
				default:
			}
		}
		$_catids = [];

		if (empty($collection)) return;
		foreach ($collection as $category) {
			$_catids[] = $category->getId();
		}
		return $_catids;
	}

	public function _getCatinfor($catids)
	{
		!is_array($catids) && settype($catids, 'array');
		$image_config = [
			'width' => (int)$this->_getConfig('imgcat_width', 300),
			'height' => $this->_getConfig('imgcat_height', 300),
			'background' => (string)$this->_getConfig('imgcat_background'),
			'function' => (int)$this->_getConfig('imgcat_function')
		];
		$list = [];
		if (!empty($catids)) {
			$helper = $this->_helper();
			foreach ($catids as $catid) {
				$_cat = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($catid);
				$_image = $helper->getCatImage($_cat, $this->_getConfig());
				$_cat['title'] = $_cat->getName();
				$_cat['id'] = $_cat->getEntityId();
				$_cat['link'] = $_cat->getUrl();
				if($_image)
					$_cat['image'] = $helper->_resizeImage($_image, $image_config, "category");
				else
					$_cat['image'] = $this->_block->getViewFileUrl('Sm_Categories::images/nophoto.jpg');
				$list[$_cat['id']] = $_cat->getData();
			}
		}
		return $list;
	}

	public function _getChildrenCat($catid) {
		$items = [];
		$limitCat = $this->_getConfig('product_limitation',5);
		$level = $this->_getConfig('max_depth',1);
		$_list = $this->_childCategory($catid, false, $limitCat, $level);
		$list = $this->_getCatinfor($_list, true);
		if(!empty($list)){
			foreach($list as $item){
				if ($this->_getConfig('total_item_cate', 1)) {
					$item['number_products'] = $this->_getProductsBasic($item['id'], true);
				}
				$items[] = $item;
			}
		}
		return $items;
	}

	private function _childCategory($catids, $allcat = true, $limitCat = 0, $levels = 0)
	{
		!is_array($catids) && settype($catids, 'array');
		$additional_catids = [];
		$additional_catids_s = [];
		if (!empty($catids)) {
			foreach ($catids as $catid) {
				$_category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($catid);
				$levelCat = $_category->getLevel();
				if ($_category->hasChildren()){
					$catid_childs = $_category->getAllChildren(true);
					foreach ($catid_childs as $cat_child) {
						if($catid == $cat_child) continue;
						$_cat_child = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($cat_child);
						$cat_child_level = $_cat_child->getLevel();
						$condition = ($cat_child_level - $levelCat <= $levels);
						if ($condition) {
							$additional_catids[] = $_cat_child->getId();
						}
					}
				}
			}
			if(count($additional_catids)>$limitCat)
			{
				for($i=0; $i<$limitCat; $i++)
					$additional_catids_s[] = $additional_catids[$i];
			} else {
				foreach($additional_catids as $ad)
					$additional_catids_s[] = $ad;
			}
			$catids = $allcat ? array_unique(array_merge($catids, $additional_catids_s)) : array_unique($additional_catids_s);
		}
		return $catids;
	}

	public function _getProductsBasic($catids, $countProduct = false)
	{
		$collection = [];
		!is_array($catids) && settype($catids, 'array');
		if (!empty($catids)) {
			$attributes = ['name', 'image', 'thumbnail'];
			$collection = $this->_objectManager->create('Magento\Catalog\Model\Product')
				->getCollection()
				->addAttributeToSelect($attributes)
				->addUrlRewrite()
				->setStoreId($this->_storeId)
				->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null, 'left')
				->addAttributeToFilter([['attribute' => 'category_id', 'in' => [$catids]]]);
			$collection->getSelect()->group('entity_id')->distinct(true);

			$collection->clear();
			if ($countProduct) return count($collection->getAllIds());
			$_start = 0;
			$_limit = (int)$this->_getConfig('product_limitation', 5);
			$_limit = $_limit <= 0 ? 0 : $_limit;
			if ($_limit >= 0) {
				$collection->getSelect()->limit($_limit, $_start);
			}
		}
		return $collection;
	}
}