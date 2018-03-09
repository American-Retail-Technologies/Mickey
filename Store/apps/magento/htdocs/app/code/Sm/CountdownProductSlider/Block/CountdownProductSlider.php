<?php
/*------------------------------------------------------------------------
# SM Count Down Product Slider  - Version 1.1.0
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\CountdownProductSlider\Block;

use Magento\Eav\Model\Config;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Sm\CountdownProductSlider\Block\Cache\Lite;

class CountdownProductSlider extends \Magento\Catalog\Block\Product\AbstractProduct
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
		$this->_storeId = (int)$this->_storeManager->getStore()->getId();
		$this->_productsCollectionFactory = $productsCollectionFactory;
		$this->_reviewRenderer = $context->getReviewRenderer();
		$this->_directory = $this->_objectManager->get('\Magento\Framework\Filesystem');
		$this->_config = $this->_getCfg($attr, $data);
		
		parent::__construct($context, $data);
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _helper()
	{
		return $this->_objectManager->get('\Sm\CountdownProductSlider\Helper\Data');
	}


	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('countdownproductslider');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('countdownproductslider');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('countdownproductslider/'.$group.'', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
		$folder_cache = $folder_cache.'Sm/CountdownProductSlider/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);
		
		$options = array(
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		);
		$Cache_Lite = new \Sm\CountdownProductSlider\Block\Cache\Lite($options);	
		
		if ($use_cache){
			$hash = md5( serialize([$this->_getConfig(), $this->_storeId ,$this->_storeManager->getStore()->getCurrentCurrencyCode()]) );
			if ($data = $Cache_Lite->get($hash)) {
				return  $data;
			} else { 
				$template_file = $this->getTemplate();
				$template_file = (!empty($template_file)) ? $template_file : "Sm_CountdownProductSlider::default.phtml";
				$this->setTemplate($template_file);
				$data = parent::_toHtml();
				$Cache_Lite->save($data);
			}
		}else{
			if(file_exists($folder_cache))
				$Cache_Lite->_cleanDir($folder_cache);
			$template_file = $this->getTemplate();
			$template_file = (!empty($template_file)) ? $template_file : "Sm_CountdownProductSlider::default.phtml";
			
			$this->setTemplate($template_file);
		}
		
        return parent::_toHtml();
		
	}	
	
	public function _getSelectSource(){
		$helper = $this->_helper();
		$image_config = [
			'width' => (int)$this->_getConfig('img_width', 270),
			'height' => $this->_getConfig('img_height', null),
			'background' => (string)$this->_getConfig('img_background'),
			'function' => (int)$this->_getConfig('img_function')
		];		
		
		$products = $this->_getProductsBasic();
		if ($products != null) {
			$_products = $products->getItems();
			if (!empty($_products)) {
				foreach ($_products as $_product) {
					$_product->setStoreId($this->_storeId);
					$_product->title = $_product->getName();
					$image = $helper->getProductImage($_product, $this->_getConfig());
					$_image = $helper->_resizeImage($image, $image_config);
					$_product->_image = $_image;
					$_product->_description = $helper->_cleanText($_product->getDescription());
					$_product->_description = $helper->_trimEncode($_product->_description != '') ? $_product->_description : $helper->_cleanText($_product->getShortDescription());
					$_product->_description = $helper->_trimEncode($_product->_description != '') ? $helper->truncate($_product->_description, $this->_getConfig('product_description_maxlength')) : '';
					$_product->link = $_product->getProductUrl();
				}
				return $_products;
			}
		}
	}

	
	public function _moduleID()
	{
		return md5(serialize(array('sm_countdownproductslider', $this->_config)));
	}
	
	
	
	public function _getProductsBasic()
	{
		$collection = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection();
		if(!empty($collection)){
		$todayDate = date('m/d/y');	
		$date_from = $this->_getConfig('date_from', '');	
		$todayStartOfDayDate = $this->_localeDate->date($todayDate)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
		$todayEndOfDayDate = $this->_localeDate->date($date_from)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
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
			->addAttributeToFilter([
				 ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
				 ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
			])
			->addAttributeToFilter('special_from_date',
				['and' => [
					0 => ['date' => true, 'to' => $todayEndOfDayDate]
				]], 'left')
			->addAttributeToFilter('special_to_date',
				['and' => [
					0 => ['date' => true, 'from' => $todayStartOfDayDate]
				]], 'left')
			->addFieldToFilter('special_price', ['neq' => '']);
				
			if ($this->_getFeaturedProduct($collection) == false) return null;
			$this->_getFeaturedProduct($collection);
			$collection->setVisibility($this->_objectManager->get('\Magento\Catalog\Model\Product\Visibility')->getVisibleInCatalogIds());
			$this->_addViewsCount($collection);
			$this->_addOrderedCount($collection);
			$this->_addReviewsCount($collection);
			$collection->getSelect()->group('entity_id')->distinct(true);
			$this->_getOrder($collection);

			$collection->clear();
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
	
	private function _addOrderedCount(& $collection)
    {
       $order_item_table = $this->_resource->getTableName('sales_order_item');
       $select = $this->_resource->getConnection('core_read')
            ->select()
            ->from($order_item_table, array('product_id', 'ordered_qty' => 'SUM(`qty_ordered`)'))
            ->group('product_id');

        $collection->getSelect()
            ->joinLeft(array('bs' => $select),
                'bs.product_id = e.entity_id');
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
	 *	Get Lastest Product
	 */
	private function _getLastestProduct(& $collection)
	{
		$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
		$todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

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
				$collection->getSelect()->order('final_price ' . $dir . '');
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
			case 'best_sales':
				$collection->getSelect()->order('ordered_qty DESC ');
				break;
			default:
		}
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
				return __('Lastest Product');
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
	
}