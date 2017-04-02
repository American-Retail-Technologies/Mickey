<?php
/*------------------------------------------------------------------------
 # SM Listing Tabs - Version 2.2.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ListingTabs\Block;

use Magento\Eav\Model\Config;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Sm\ListingTabs\Block\Cache\Lite;

class ListingTabs extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_config = null;
	protected $_storeId = null;
	protected $_status;
	protected $_resource;
	protected $_eavConfig;
	protected $_visibility;
	protected $_stockHelper;
	protected $_storeManager;
	protected $_objectManager;
	protected $_localeResolver;
	protected $_categoryCollectionFactory;
	protected $localeDate;
	protected $_productsCollectionFactory;
	protected $_scopeConfigInterface;
	protected $_reviewRenderer;
	protected $_directory;

	public function __construct(
		ResourceConnection $resourceConnection,
		Collection $productsCollectionFactory,
		ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\ResourceModel\Category\Collection $collectionFactory,
		ResolverInterface $localeResolver,
		Visibility $visibility,
		Stock $stockHelper,
		Config $eavConfig,
		Context $context,
		Status $status,
		array $data = [],
		$attr = null
	)
	{

		$this->_status = $status;
		$this->_eavConfig = $eavConfig;
		$this->_visibility = $visibility;
		$this->_stockHelper = $stockHelper;
		$this->_resource = $resourceConnection;
		$this->_objectManager = $objectManager;
		$this->_localeResolver = $localeResolver;
		$this->_storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$this->_catalogConfig = $context->getCatalogConfig();
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_categoryCollectionFactory = $collectionFactory;
		$this->localeDate = $this->_objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$this->_storeId = (int)$this->_storeManager->getStore()->getId();
		$this->_productsCollectionFactory = $productsCollectionFactory;
		$this->_reviewRenderer = $context->getReviewRenderer();
		$this->_directory = $this->_objectManager->get('\Magento\Framework\Filesystem');
		if ($context->getRequest() && $context->getRequest()->isAjax()) {
			$_cfg =  $context->getRequest()->getParam('config');
			$this->_config = (array)json_decode(base64_decode(strtr($_cfg, '-_', '+/')));
		} else {
			$this->_config = $this->_getCfg($attr, $data);
		}
		parent::__construct($context, $data);
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _helper()
	{
		return $this->_objectManager->get('\Sm\ListingTabs\Helper\Data');
	}


	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('listingtabs',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);
		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('listingtabs',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('listingtabs/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->_storeId);
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
		$folder_cache = $folder_cache.'Sm/ListingTabs/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);

		$options = array(
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		);
		$Cache_Lite = new \Sm\ListingTabs\Block\Cache\Lite($options);
		if ($this->_isAjax()) {
			$ajax_listingtags_start = $this->getRequest()->getPost('ajax_listingtags_start', 0);
			$catid = $this->getRequest()->getPost('categoryid');
			$datacustom_content = $this->getRequest()->getPost('datacustomcontent');
			
			if ($use_cache){
				$cacheid_items = md5(serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode(), $ajax_listingtags_start, $catid]));
				if ( $dataitems = $Cache_Lite->get($cacheid_items)) {
					return  $dataitems;
				} else {
					if($datacustom_content == 'data-custom-content'){
						$template_file = "default_items_id9.phtml";
					} else if($datacustom_content == 'data-custom-center'){
						$template_file = "default_items_id11.phtml";
					} else {
						$template_file = "default_items.phtml";
					}
					$this->setTemplate($template_file);
					$dataitems = parent::_toHtml();
					$Cache_Lite->save($dataitems);
				}
			}else{
				if(file_exists($folder_cache))
					$Cache_Lite->_cleanDir($folder_cache);
					if($datacustom_content == 'data-custom-content'){
						$template_file = "default_items_id9.phtml";
					} else if($datacustom_content == 'data-custom-center'){
						$template_file = "default_items_id11.phtml";
					} else {
						$template_file = "default_items.phtml";
					}
					$this->setTemplate($template_file);
			}

		}else{
			if ($use_cache){
				$hash = md5( serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode()]) );
				if ($data = $Cache_Lite->get($hash)) {
					return  $data;
				} else {
					$template_file = $this->getTemplate();
					$template_file = (!empty($template_file)) ? $template_file : "Sm_ListingTabs::default.phtml";
					$this->setTemplate($template_file);
					$data = parent::_toHtml();
					$Cache_Lite->save($data);
				}
			}else{
				if(file_exists($folder_cache))
					$Cache_Lite->_cleanDir($folder_cache);
				$template_file = $this->getTemplate();
				$template_file = (!empty($template_file)) ? $template_file : "Sm_ListingTabs::default.phtml";
				$this->setTemplate($template_file);
			}
		}

        return parent::_toHtml();

	}

	public function _getSelectSource(){
		$type_filter = $this->_getConfig('filter_type');
		$list = [];
		switch ($type_filter) {
			case 'categories':
				$catids = $this->_getCatIds();
				!is_array($catids) && settype($catids, 'array');

				if (empty($catids)) return;
				$cats = $this->_getCatinfor($catids);
				if (empty($cats)) return;

				if ($this->_getConfig('tab_all_display', 1)) {
					$all = [];
					$all['entity_id'] = '*';
					$all['count'] = $this->_countProducts($catids);
					$all['title'] = 'ALL';
					array_unshift($cats, $all);
				}


				$catidpreload = $this->_getConfig('category_preload');
				$selected = false;

				foreach ($cats as $cat) {
					if (isset($cat['count']) && $cat['count']) {
						if ($cat['entity_id']== $catidpreload) {
							$cat['sel'] = 'sel';
							$cat['child'] = $this->_getProductInfor($catidpreload);
							$selected = true;
						}
						$list[$cat['entity_id']] = $cat;
					}
				}

				if (!$selected) {
					foreach ($cats as $cat) {
						if ($cat['count'] > 0) {
							$cat['sel'] = 'sel';
							$cat['child'] = $this->_getProductInfor($cat['entity_id']);
							$list[$cat['entity_id']] = $cat;
							break;
						}
					}
				}

				// first tab is active
				break;

			case 'fieldproducts':
				$catids = $this->_getCatIds();
				$filters = explode(',', $this->_getConfig('filter_order_by'));
				$filter_preload = $this->_getConfig('field_preload');
				if (empty($filters)) return;
				if (!in_array($filter_preload, $filters)) {
					$filter_preload = $filters[0];
				}

				foreach ($filters as $filter) {
					$product = [];
					$product['count'] = $this->_countProducts($catids, $filter);
					$product['entity_id'] = $filter;
					$product['title'] = $filter;
					if ($product['count'] > 0) {
						if ($product['entity_id'] == $filter_preload) {
							$product['sel'] = 'sel';
							$product['child'] = $this->_getProductInfor($catids, $filter_preload);
						}
						$list[$product['entity_id']] = $product;
					}
				}
				break;

		}
		if (empty($list)) return;
		return $list;
	}

	public function _getCatIds()
	{
		$catids = $this->_getConfig('product_category');
		if ($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		if (empty($_catids)) return;

		return $_catids;
	}
	public function _moduleID()
	{
		return md5(serialize(['sm_listingtabs', $this->_config]));
	}
	public function _isAjax()
	{
		$isAjax = $this->getRequest()->isAjax();
		$is_ajax_listing_tabs = $this->getRequest()->getPost('is_ajax_listing_tabs');
		if ($isAjax && $is_ajax_listing_tabs == 1) {
			return true;
		} else {
			return false;
		}
	}
	public function _getProductInfor($_catids, $field_order = null)
	{
		$small_image_config = [
			'width' => (int)$this->_getConfig('img_width', 200),
			'height' => $this->_getConfig('img_height', null),
			'background' => (string)$this->_getConfig('img_background'),
			'function' => (int)$this->_getConfig('img_function')
		];

		if ($_catids == '*') {
			$_catids = $this->_getCatIds();
		}
		!is_array($_catids) && settype($_catids, 'array');
		if (!empty($_catids)) {
			$products = $this->_getProductsBasic($_catids, $field_order);
			if ($products != null) {
				$_products = $products->getItems();
				if (!empty($_products)) {
					$helper = $this->_helper();
					foreach ($_products as $_product) {
						$_product->setStoreId($this->_storeId);
						$_product->title = $_product->getName();
						$image = $helper->getProductImage($_product, $this->_getConfig());
						$_image = $helper->_resizeImage($image, $small_image_config,"product");
						$_product->_image = $_image;
						$_product->_description = $helper->_cleanText($_product->getDescription());
						$_product->_description = $helper->_trimEncode($_product->_description != '') ? $_product->_description : $helper->_cleanText($_product->getShortDescription());
						$_product->_description = $helper->_trimEncode($_product->_description != '') ? $helper->truncate($_product->_description, $this->_getConfig('product_description_maxlength')) : '';
						$_product->link = $_product->getProductUrl();
						//$_product->num_view_counts = (!isset($_product->num_view_counts) || $_product->num_view_counts == null) ? 0 : $_product->num_view_counts;
					}

					return $_products;
				}
			}
		}
		return null;
	}

	/*
	* Check Categories is Active ?
	*/
	private function _getCatActive($catids = null, $orderby = true)
	{
		if (is_null($catids)) {
			$catids = $this->_getConfig('product_category');
		}
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);
		if (empty($catids)) return;
		$categoryIds = array(
			'in' => $catids
		);

		$categories = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection()->addAttributeToSelect('*')->setStoreId($this->_storeId)->addAttributeToFilter('entity_id', $categoryIds)->addIsActiveFilter();
		if ($orderby) {
			$attribute = $this->_getConfig('category_order_by', 'name'); // name | position | entry_id | random
			$dir = $this->_getConfig('category_order_dir', 'ASC');
			switch ($attribute) {
				case 'name':
				case 'position':
				case 'entry_id':
					$categories->addAttributeToSort($attribute, $dir);
					break;

				case 'random':
					$categories->getSelect()->order(new Zend_Db_Expr('RAND()'));
					break;

				default:
			}
		}
		$_catids = array();
		if (empty($categories)) return;
		foreach ($categories as $category) {
			$_catids[] = $category->getId();
		}
		return $_catids;
	}

	/*
	* array $catids
	* bool $allcat = true return with parentid else return only childId
	* int $limitCat = 0 return unlimit else return limit
	* int $levels =  1
	* return $catids
	*/
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

	public function _getCatinfor($catids, $orderby = null)
	{
		$helper = $this->_helper();
		$category_image_config = array(
			'width' => (int)$this->_getConfig('imgcfgcat_width', 50),
			'height' => (int)$this->_getConfig('imgcfgcat_height', 50),
			'background' => (string)$this->_getConfig('imgcfgcat_background'),
			'function' => (int)$this->_getConfig('imgcfgcat_function', 0)
		);
		$list = [];

		if (!empty($catids)) {
			foreach ($catids as $catid) {
				$cat = [];
				$category_model = $this->_objectManager->create('Magento\Catalog\Model\Category');
				$category = $category_model->load((int)$catid);
				$cat['title'] = $category->getName();
				$cat['count'] = $this->_countProducts($catid);
				$cat['link'] = $category->getUrl();
				$cat['entity_id'] = $catid;
				$cat['_description'] = $helper->_cleanText($category->getDescription());
				$cat['_description'] = $helper->_trimEncode($cat['_description'] != '') ? $helper->truncate($cat['_description'], $this->_getConfig('category_title_maxlength', 5)) : '';
				$_image = $helper->getCatImage($category, $this->_getConfig());
				if($_image)
					$cat['_image'] = $helper->_resizeImage($_image, $category_image_config,"category");
				else
					$cat['_image'] = '';

				$list[$catid] = $cat;

			}
		}
		return $list;
	}

	/*
	* return countProduct;
	*/
	protected function _countProducts($catids, $field_order = null)
	{
		!is_array($catids) && settype($catids, 'array');
		$countProduct = $this->_getCountProductsBasic($catids, $field_order, true);
		return $countProduct;
	}

	public function _getCountProductsBasic($catids,$field_order = null, $countProduct = false)
	{
		$collection = [];
		$inlucde = (int)$this->_getConfig('child_category_products', 1);
		$level = (int)$this->_getConfig('max_depth', 1);
		$catids = ($inlucde && $level > 0) ? $this->_childCategory($catids, true, 0, (int)$level) : $catids;
		if (!empty($catids)) {
			$attributes = ['name'];
			$collection = $this->_objectManager->get('Magento\Catalog\Model\Product')
				->getCollection()
				->addAttributeToSelect($attributes)
				->addAttributeToSelect('featured')
				->setStoreId($this->_storeId)
				->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null, 'left')
				->addAttributeToFilter([['attribute' => 'category_id', 'in' => [$catids]]]);
			if ($this->_getFeaturedProduct($collection) == false) return null;
			$this->_getFeaturedProduct($collection);
			$collection->getSelect()->group('entity_id')->distinct(true);
			$this->_getOrder($collection, $field_order);

			$collection->clear();

			if ($countProduct) return  count($collection->getAllIds());
			$start = (int)$this->getRequest()->getPost('ajax_listingtags_start');
			if (!$start) $start = 0;
			$_limit = (int)$this->_getConfig('product_limitation', 5);
			$_limit = $_limit <= 0 ? 0 : $_limit;
			if ($_limit >= 0) {
				$collection->getSelect()->limit($_limit, $start);
			}
		}
		return $collection;
	}

	public function _getProductsBasic($catids,$field_order = null, $countProduct = false)
	{
		$collection = [];
		!is_array($catids) && settype($catids, 'array');
		$inlucde = (int)$this->_getConfig('child_category_products', 1);
		$level = (int)$this->_getConfig('max_depth', 1);
		$catids = ($inlucde && $level > 0) ? $this->_childCategory($catids, true, 0, (int)$level) : $catids;
		if (!empty($catids)) {
			$attributes = ['name', 'special_to_date', 'description', 'short_description', 'image', 'thumbnail'];
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
			$this->_addViewsCount($collection); // For Most Viewed
			$this->_addReviewsCount($collection); // For Most Reviews and Top Ratting
			$collection->getSelect()->group('entity_id')->distinct(true);
			$this->_getOrder($collection, $field_order);
			$collection->clear();

			if ($countProduct) return  count($collection->getAllIds());
			$start = (int)$this->getRequest()->getPost('ajax_listingtags_start');
			if (!$start) $start = 0;
			$_limit = (int)$this->_getConfig('product_limitation', 5);
			$_limit = $_limit <= 0 ? 0 : $_limit;
			if ($_limit >= 0) {
				$collection->getSelect()->limit($_limit, $start);
			}
			$this->_objectManager->get('Magento\Review\Model\Review')->appendSummary($collection);
		}

		return $collection;
	}

	/*
	 *	Get Featured Product
	 */
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
					$collection->addAttributeToFilter(array(array('attribute' => 'featured', 'eq' => 0)), null, 'left');
				}
				break;
			// Only Featured
			case 2:
				if ($attributeModel->usesSource()) {
					$collection->addAttributeToFilter(array(array('attribute' => 'featured', 'eq' => 1)));
				} else {
					return;
				}
				break;
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
				array('or' => array(
					0 => array('date' => true, 'to' => $todayEndOfDayDate),
					1 => array('is' => new \Zend_Db_Expr('null'))
				)), 'left')
			->addAttributeToFilter('news_to_date',
				array('or' => array(
					0 => array('date' => true, 'from' => $todayStartOfDayDate),
					1 => array('is' => new \Zend_Db_Expr('null'))
				)), 'left')
			->addAttributeToSort('news_from_date', 'DESC');
		return $collection;
	}

	/*
	 * For Most Viewed
	 * add views_count
	 * */
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

	/*
	 * For Most Reviews and Top Ratting
	 * add reviews_count and rating_summary
	 * */
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

	/*
	 *	Get Order
	 */
	private function _getOrder($collection, $fileld_order = null)
	{
		$attribute = ($fileld_order == null) ? (string)$this->_getConfig('product_order_by', 'name') : $fileld_order;
		$dir = (string)$this->_getConfig('product_order_dir', 'ASC');
		switch ($attribute) {
			case 'entity_id':
			case 'name':
			case 'created_at':
				$collection->setOrder($attribute, $dir);
				break;
			case 'price':
				$collection->getSelect()->order('minimal_price ' . $dir . '');
				break;
			case 'lastest_product':
				$this->_getLastestProduct($collection);
				break;
			case 'top_rating':
				$collection->getSelect()->order('num_rating_summary DESC');
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('num_reviews_count DESC');
				break;
			case 'most_viewed':
				$collection->getSelect()->order('num_view_counts DESC');
				break;
			case 'best_sellers':
				$collection->getSelect()->order('ordered_qty DESC ');
				break;
			default:
		}
		return $collection;
	}

	public function getLabel($filter)
	{
		switch ($filter) {
			case 'name':
				return __('Name');
			case 'entity_id':
				return __('Id');
			case 'created_at':
				return __('Date Created');
			case 'price':
				return __('Price');
			case 'lastest_product':
				return __('Lastest Products');
			case 'top_rating':
				return __('Top Rating');
			case 'most_reviewed':
				return __('Most Reviews');
			case 'most_viewed':
				return __('Most Viewed');
			case 'best_sales':
				return __('Most Selling');
			case 'random':
				return __('Random');
		}
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