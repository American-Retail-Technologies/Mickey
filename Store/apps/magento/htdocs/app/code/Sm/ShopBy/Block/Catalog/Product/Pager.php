<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.1.0
 # Copyright (c) 2016 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ShopBy\Block\Catalog\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

class Pager extends \Magento\Catalog\Block\Product\ListProduct{
	
	protected $_config = null;
	protected $_storeId = null;
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';
	protected $_cartHelper;
    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
	
	public $min_price;
	
	public $max_price;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
	protected $_directory;
	public $_objectManager;
	protected $_scopeConfigInterface;	 
	
    public function __construct(
		\Magento\Catalog\Block\Product\Context $context, 
		\Magento\Framework\Data\Helper\PostHelper $postDataHelper, 
		\Magento\Catalog\Model\Layer\Resolver $layerResolver, 
		\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, 
		\Magento\Framework\Url\Helper\Data $urlHelper, 
		ObjectManagerInterface $objectManager,
		$attr = null,		
		array $data = array())
    {
        $this->___init();
		$this->_objectManager = $objectManager;
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_cartHelper = $context->getCartHelper();
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
	
    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }	
	
    protected function _getProductCollection()
    {
		$layer = $this->getLayer();	
        if ($this->_productCollection === null) {
            /* @var $layer \Magento\Catalog\Model\Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }
		
		$_category = $layer->getCurrentCategory();
		$currentCategoryId= $_category->getId();
		$attributes = $this->_objectManager->get('Magento\Catalog\Model\Config')->getProductAttributes();
		$collection = $this->_objectManager->get('Magento\Catalog\Model\Product')
			->getCollection()
			->addAttributeToSelect($attributes)
			->addAttributeToSelect('featured')
			->addAttributeToSelect('*')
			->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addTierPriceData()
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null, 'left')
			->addAttributeToFilter([['attribute' => 'category_id', 'in' => [$currentCategoryId]]]);
				
		$this->min_price = $collection->getMinPrice();
		$this->max_price = $collection->getMaxPrice();
		
		//var_dump($this->_productCollection->getMaxPrice(), $this->_productCollection->getMinPrice());die;
        return $this->_productCollection;
    }	
	
	public function _helper()
	{
		return $this->_objectManager->get('\Sm\ShopBy\Helper\Data');
	}	
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	
	

	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('shopby');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('shopby');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('shopby/'.$group.'');
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

	
}