<?php
/**------------------------------------------------------------------------
* SM Search Box - Version 2.1.0
* Copyright (c) 2015 YouTech Company. All Rights Reserved.
* @license - Copyrighted Commercial Software
* Author: YouTech Company
* Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\SearchBox\Model\Config\Source;

class ListCategories
{
	/**
	 * Object manager
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	private $_objectManager;

	protected $_categoryCollectionFactory;

	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
	)
	{
		$this->_objectManager = $objectManager;
		$this->_categoryCollectionFactory = $collectionFactory;
	}

	public function toOptionArray($root_id, $depth)
	{
		$options = [];
		$cats = [];
		$categories = [];
		$categories[] = $root_id;

		if ($depth >= 1)
		{
			$collection = $this->_objectManager->create('Magento\Catalog\Model\Category');
			$category = $collection->load($root_id);
			$subCategory = $category->getChildren();
			foreach(explode(',',$subCategory) as $subcat) {
				if ($subcat != '') {
					$categories[] = $subcat;
				}
			}

			if ($depth == 2)
			{
				foreach(explode(',',$subCategory) as $subcat) {
					$sub_category = $collection->load($subcat);
					$sub_subCat = $sub_category->getChildren();
					foreach(explode(',',$sub_subCat) as $s_subcat) {
						if ($s_subcat != '') {
							$categories[] = $s_subcat;
						}
					}
				}
			}
		}

		$modelCategory = $this->_categoryCollectionFactory->create();
		$modelCategory->addFieldToFilter('parent_id', ['in' => $categories])
			->addIsActiveFilter()
			->addAttributeToSelect('name');

		foreach ($modelCategory as $cat) {
			$c_depth = $cat->getLevel();
			if ($c_depth <= $depth ) {
				$c = new \stdClass();
				$c->label = $cat->getName();
				$c->value = $cat->getId();
				$c->level = $cat->getLevel();
				$c->parentid = $cat->getParentId();
				$cats[$c->value] = $c;
			}
		}

		foreach ($cats as $id => $c) {
			if (isset($cats[$c->parentid])) {
				if (!isset($cats[$c->parentid]->child)) {
					$cats[$c->parentid]->child = [];
				}
				$cats[$c->parentid]->child[] =& $cats[$id];
			}
		}
		/*$idAllCat = [];
		foreach ($cats as $c) {
			$idAllCat[] = $c->value;
		}
		$options = [['label'=>'All Categories','value'=>implode(',',$idAllCat)]];*/
		foreach ($cats as $id => $c) {
			if (!isset($cats[$c->parentid])) {
				$stack = [$cats[$id]];
				while (count($stack) > 0) {
					$opt = array_pop($stack);
					$option = [
						'label' => ($opt->level > 1 ? str_repeat('- - ', $opt->level - 1) : '') . $opt->label,
						'value' => $opt->value
					];
					array_push($options, $option);
					if (isset($opt->child) && count($opt->child)) {
						foreach (array_reverse($opt->child) as $child) {
							array_push($stack, $child);
						}
					}
				}
			}
		}
		unset($cats);
		return $options;
	}
}