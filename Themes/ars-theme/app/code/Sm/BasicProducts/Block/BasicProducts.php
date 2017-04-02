<?php
/*------------------------------------------------------------------------
# SM Basic Products - Version 2.2.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\BasicProducts\Block;

use Magento\Eav\Model\Config;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Sm\BasicProducts\Block\Cache\Lite;

class BasicProducts extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_config = null;
	protected $_storeId = null;
	protected $_resource;
	protected $_eavConfig;
	protected $_storeManager;
	protected $_objectManager;
	protected $_localeResolver;
	protected $localeDate;
	protected $_scopeConfigInterface;
	protected $_directory;

	public function __construct(
		ResourceConnection $resourceConnection,
		ObjectManagerInterface $objectManager,
		ResolverInterface $localeResolver,
		Config $eavConfig,
		Context $context,
		array $data = [],
		$attr = null
	)
	{
		$this->_eavConfig = $eavConfig;
		$this->_resource = $resourceConnection;
		$this->_objectManager = $objectManager;
		$this->_localeResolver = $localeResolver;
		$this->_storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->localeDate = $this->_objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$this->_directory = $this->_objectManager->get('\Magento\Framework\Filesystem');
		$this->_storeId = (int)$this->_storeManager->getStore()->getId();
		$this->_config = $this->_getCfg($attr, $data);
		parent::__construct($context, $data);
	}
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _helper()
	{
		return $this->_objectManager->get('\Sm\BasicProducts\Helper\Data');
	}


	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('basicproducts',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('basicproducts',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('basicproducts/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);
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
		if (!$this->_getConfig('isactive', 1)) return;

		$use_cache = (int)$this->_getConfig('use_cache');
		$cache_time = (int)$this->_getConfig('cache_time');
		$folder_cache = $this->_directory->getDirectoryWrite(DirectoryList::CACHE)->getAbsolutePath();
		$folder_cache = $folder_cache.'Sm/BasicProducts/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);
		
		$options = array(
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		);
		$Cache_Lite = new \Sm\BasicProducts\Block\Cache\Lite($options);
		if ($use_cache){
			$hash = md5( serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode()]) );
			if ($data = $Cache_Lite->get($hash)) {
				return  $data;
			} else { 
				$template_file = $this->getTemplate();
				$template_file = (!empty($template_file)) ? $template_file : "Sm_BasicProducts::default.phtml";
				$this->setTemplate($template_file);
				$data = parent::_toHtml();
				$Cache_Lite->save($data);
			}
		}else{
			if(file_exists($folder_cache))
				$Cache_Lite->_cleanDir($folder_cache);
			$template_file = $this->getTemplate();
			$template_file = (!empty($template_file)) ? $template_file : "Sm_BasicProducts::default.phtml";
			$this->setTemplate($template_file);
		}
		
        return parent::_toHtml();
		
	}

	public function _getSelectSource(){
		$helper = $this->_helper();
		$image_config = [
			'width' => (int)$this->_getConfig('img_width', 200),
			'height' => $this->_getConfig('img_height', null),
			'background' => (string)$this->_getConfig('img_background'),
			'function' => (int)$this->_getConfig('img_function')
		];

		$product_source = $this->_getConfig('product_source');
		switch($product_source)
		{
			default:
			case 'media':
				$items = $this->_getProductMedia();
				$list = [];
				$i = 0;
				if (!empty($items)) 
				{
					foreach ($items as $item) 
					{
						$i++;
						if ($item['title'] != '' && $item['image'] != '') 
						{
                            $item['image'] = (strpos($item['image'], 'http') !== false) ? $item['image'] : $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$item['image'];
							$item['_image'] = $helper->_resizeImage($item['image'], $image_config,"product");
                            $description = $helper->_cleanText($item['content']);
                            $description = $helper->truncate($description, $this->_getConfig('product_description_maxlength'));
                            $item['_description'] = $description;
                            unset($item['content']);
                            $list[] = (object)$item;
                        }
					}
				}
				return $list;
				break;
			case 'catalog':
				$products = $this->_getProductCatalog();
				if ($products != null) {
					$_products = $products->getItems();
				
					if (!empty($_products)) {
						foreach ($_products as $_product) {
							$_product->setStoreId($this->_storeId);
							$_product->title = $_product->getName();
							$image = $helper->getProductImage($_product, $this->_getConfig());
							$_image = $helper->_resizeImage($image, $image_config,"product");
							$_product->_image = $_image;
							$_product->_description = $helper->_cleanText($_product->getDescription());
							$_product->_description = $helper->_trimEncode($_product->_description != '') ? $_product->_description : $helper->_cleanText($_product->getShortDescription());
							$_product->_description = $helper->_trimEncode($_product->_description != '') ? $helper->truncate($_product->_description, $this->_getConfig('product_description_maxlength')) : '';
							$_product->link = $_product->getProductUrl();
							$catid = (int) $_product->_getData('category_id');
							$category_model = $this->_objectManager->get('Magento\Catalog\Model\Category');	
							$category = $category_model->load((int)$catid);
							$_product->cat_title = $category->getName();
							$_product->cat_link = $category->getUrl();
						}
						return $_products;
					}
				}
				break;				
			case 'ids':
				$products = $this->_getProductsIDs();				

				if ($products != null) {
					$_products = $products->getItems();
				
					if (!empty($_products)) {
						foreach ($_products as $_product) {
							$_product->setStoreId($this->_storeId);
							$_product->title = $_product->getName();
							$image = $helper->getProductImage($_product, $this->_getConfig());
							$_image = $helper->_resizeImage($image, $image_config,"product");
							$_product->_image = $_image;
							$_product->_description = $helper->_cleanText($_product->getDescription());
							$_product->_description = $helper->_trimEncode($_product->_description != '') ? $_product->_description : $helper->_cleanText($_product->getShortDescription());
							$_product->_description = $helper->_trimEncode($_product->_description != '') ? $helper->truncate($_product->_description, $this->_getConfig('product_description_maxlength')) : '';
							$_product->link = $_product->getProductUrl();
							$catid = (int) $_product->_getData('category_id');
							$category_model = $this->_objectManager->get('Magento\Catalog\Model\Category');	
							$category = $category_model->load((int)$catid);
							$_product->cat_title = $category->getName();
							$_product->cat_link = $category->getUrl();
						}
						return $_products;
					}
				}
				break;
		}
	}

	public function _getProductMedia()
    {
        $items = $this->_getConfig('product_additem');
        $items = unserialize($items);
        if (empty($items)) return;
        return $items;
    }

	public function _getProductCatalog()
	{
		$catids = $this->_getConfig('product_category');
		$inlucde = (int)$this->_getConfig('child_category_products', 1);
		$level = (int)$this->_getConfig('max_depth', 1);
		if ($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		$_catids = ($inlucde && $level > 0) ? $this->_childCategory($_catids, true, 0, (int)$level) : $_catids;
		if (empty($_catids)) return;
		$products = $this->_getProductsBasic($_catids);
		return $products;
	}

	private function _getCatActive($catids = null, $orderby = true)
	{
		if (is_null($catids)) {
			$catids = $this->_getConfig('product_category');
		}
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);
		
		if (empty($catids)) return;
		$categoryIds = ['in' => $catids];
		$collection = $this->_objectManager->get('Magento\Catalog\Model\Category') ->getCollection();
		$collection->addAttributeToSelect('*')
			->setStoreId($this->_storeId)
			->addAttributeToFilter('entity_id', $categoryIds)
			->addIsActiveFilter();

		if ($orderby) {
			$attribute = 'random'; // name | position | entry_id | random
			$dir = $this->_getConfig('product_order_dir','ASC');
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

	private function _childCategory($catids, $allcat = true, $limitCat = 0, $levels = 0)
	{
		!is_array($catids) && settype($catids, 'array');
		$additional_catids = [];
		if (!empty($catids)) {

			foreach ($catids as $catid) {
				$_category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($catid);	
				$levelCat = $_category->getLevel();
				if ($_category->hasChildren()){
					$catid_childs = $_category->getAllChildren(true);
					foreach ($catid_childs as $cat_child) {
						$_cat_child = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($cat_child);	
						$cat_child_level = $_cat_child->getLevel();
						$condition = ($cat_child_level - $levelCat <= $levels);
						if ($condition) {
							$additional_catids[] = $_cat_child->getId();
						}
					}
				}
			}
			$catids = $allcat ? array_unique(array_merge($catids, $additional_catids)) : array_unique($additional_catids);
		}
		return $catids;
	}

	public function _getProductsBasic($catids, $countProduct = false)
	{
		$collection = [];
		!is_array($catids) && settype($catids, 'array');
		if (!empty($catids)) {
			$attributes = ['name', 'special_to_date', 'description', 'short_description', 'image', 'thumbnail', 'price', 'special_price'];
			$collection = $this->_objectManager->get('Magento\Catalog\Model\Product')
				->getCollection()
				->addAttributeToSelect($attributes)
				->addAttributeToSelect('featured')
				->addMinimalPrice()
				->addFinalPrice()
				->addUrlRewrite()
				->setStoreId($this->_storeId)
				->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null, 'left')
				->addAttributeToFilter([['attribute' => 'category_id', 'in' => [$catids]]]);
			if ($this->_getFeaturedProduct($collection) == false) return null;
			$this->_getFeaturedProduct($collection);
			$collection->setVisibility($this->_objectManager->get('\Magento\Catalog\Model\Product\Visibility')->getVisibleInCatalogIds());
			$this->_addViewsCount($collection);
			$this->_addReviewsCount($collection);
			$collection->getSelect()->group('entity_id')->distinct(true);
			$this->_getOrder($collection);
			
			$collection->clear();
			if ($countProduct) return $collection->count();
			$_start = 0;
			$_limit = (int)$this->_getConfig('product_limitation', 5);
			$_limit = $_limit <= 0 ? 0 : $_limit;
			if ($_limit >= 0) {
				$collection->getSelect()->limit($_limit, $_start);
			}
			$this->_objectManager->get('Magento\Review\Model\Review')->appendSummary($collection);
		}
		return $collection;
	}

	private function _getFeaturedProduct($collection)
	{
		$filter = (int)$this->_getConfig('product_featured', 0);
		$attributeModel = $this->_eavConfig->getAttribute('catalog_product', 'featured');
		switch ($filter) {
			// Show All
			case 0:
				break;
			// None Featured
			case 1:
				if ($attributeModel->usesSource()) {
					$collection->addAttributeToFilter([['attribute' => 'featured', 'eq' => 0]], null, 'left');
				}
				break;
			// Only Featured
			case 2:
				if ($attributeModel->usesSource()) {
					$collection->addAttributeToFilter([['attribute' => 'featured', 'eq' => 1]]);
				} else {
					return;
				}
				break;
		}
		return $collection;
	}

	/*
     *	Get Order
     */
	private function _getOrder($collection)
	{
		$attribute = (string)$this->_getConfig('product_order_by', 'name');
		$dir = (string)$this->_getConfig('product_order_dir', 'ASC');
		switch ($attribute) {
			case 'entity_id':
			case 'name':
			case 'created_at':
				$collection->setOrder($attribute, $dir);
				break;
			case 'price':
				$collection->getSelect()->order('final_price ' . $dir . '');
				break;
			case 'random':
				$collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
				break;
			case 'lastest_product':
				$this->_getLastestProduct($collection);
				break;
			case 'top_rating':
				$collection->getSelect()->order('num_rating_summary ' . $dir . '');
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('num_reviews_count ' . $dir . '');
				break;
			case 'most_viewed':
				$collection->getSelect()->order('num_view_counts ' . $dir . '');
				break;
			case 'best_sellers':
				$collection->getSelect()->order('ordered_qty ' . $dir . '');
				break;
			default:
		}
		return $collection;
	}

	/*
     *	Get Lastest Product
     */
	private function _getLastestProduct(& $collection)
	{		
		$todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0)->format('Y-m-d H:i:s');
		$todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
		
		$collection = $this->_addProductAttributesAndPrices($collection)
			->addStoreFilter()
			->addAttributeToFilter('news_from_date',
				['or' => [
					0 => ['date' => true, 'to' => $todayEndOfDayDate],
					1 => ['is' => new \Zend_Db_Expr('null')]
				]], 'left')
			->addAttributeToFilter('news_to_date',
				['or' => [
					0 => ['date' => true, 'from' => $todayStartOfDayDate],
					1 => ['is' => new \Zend_Db_Expr('null')]
				]], 'left')
			->addAttributeToSort('news_from_date', 'DESC');
		return $collection;
	}

	// add views_count
	private function _addViewsCount(& $collection)
	{
		$reports_event_table = $this->_resource->getTableName('report_event');
		$select = $this->_resource->getConnection('core_read')
			->select()
			->from($reports_event_table, ['*', 'num_view_counts' => 'COUNT(`event_id`)'])
			->where('event_type_id = 1')
			->group('object_id');
		$collection->getSelect()
			->joinLeft(['mv' => $select],
				'mv.object_id = e.entity_id');
		return $collection;
	}

	// add reviews_count and rating_summary
	private function _addReviewsCount(& $collection)
	{
		$review_summary_table = $this->_resource->getTableName('review_entity_summary');
		$collection->getSelect()
			->joinLeft(
				["ra" => $review_summary_table],
				"e.entity_id = ra.entity_pk_value AND ra.store_id=" . $this->_storeId,
				[
					'num_reviews_count' => "ra.reviews_count",
					'num_rating_summary' => "ra.rating_summary"
				]
			);
		return $collection;
	}


	public function _getProductsIDs()
	{
		$catids = $this->_getConfig('product_ids');
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);
		if (empty($catids)) return;
		$attributes = ['name', 'special_to_date', 'description', 'short_description', 'image', 'thumbnail', 'price', 'special_price'];
		$products = $this->_objectManager->get('Magento\Catalog\Model\Product')
				->getCollection()
				->addAttributeToSelect($attributes)
				->addIdFilter($catids);
		$products->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $catids) . ')'));
		return $products;
	}
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->_objectManager->get('\Magento\Framework\Url\Helper\Data')->getEncodedUrl($url),
            ]
        ];
    }	
}