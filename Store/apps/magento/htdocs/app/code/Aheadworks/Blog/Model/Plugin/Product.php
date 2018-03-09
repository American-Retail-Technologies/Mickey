<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\Plugin;

use Aheadworks\Blog\Model\Rule\Condition\Product\Attributes as BlogProductAttributes;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\StateInterface;
use Aheadworks\Blog\Model\Indexer\ProductPost\Processor as ProductPostProcessor;

/**
 * Class Product
 * @package Aheadworks\Blog\Model\Plugin
 */
class Product
{
    /**
     * @var array
     */
    private $productDataBeforeSave = [];

    /**
     * @var array
     */
    private $productDataAfterSave = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var BlogProductAttributes
     */
    private $blogProductAttributes;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StateInterface
     */
    private $indexerState;

    /**
     * @param BlogProductAttributes $blogProductAttributes
     * @param ProductRepositoryInterface $productRepository
     * @param StateInterface $indexerState
     */
    public function __construct(
        BlogProductAttributes $blogProductAttributes,
        ProductRepositoryInterface $productRepository,
        StateInterface $indexerState
    ) {
        $this->blogProductAttributes = $blogProductAttributes;
        $this->productRepository = $productRepository;
        $this->indexerState = $indexerState;
    }

    /**
     * Retrieve product data before product save
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null
     */
    public function beforeSave($product)
    {
        try {
            $productModel = $this->productRepository->getById($product->getId());
            $this->productDataBeforeSave = $this->getProductAttributesData($productModel);
        } catch (NoSuchEntityException $e) {
            $this->productDataBeforeSave = [];
        }
    }

    /**
     * Retrieve product data after product save
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($subject, $product)
    {
        $this->productDataAfterSave = $this->getProductAttributesData($product);
        $result = array_udiff($this->productDataBeforeSave, $this->productDataAfterSave, function ($item1, $item2) {
            if (is_array($item1) || is_array($item2)) {
                return $item1 != $item2;
            }
            return strcmp($item1, $item2);
        });
        if ($result || !$this->productDataBeforeSave) {
            $this->indexerState->loadByIndexer(ProductPostProcessor::INDEXER_ID);
            $this->indexerState->setStatus(StateInterface::STATUS_INVALID);
            $this->indexerState->save();
        }
        return $product;
    }

    /**
     * Retrieve product data on attributes are available in Post Rule
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getProductAttributesData($product)
    {
        $productAttributes = [];
        foreach ($this->getBlogProductAttributes() as $code => $label) {
            $productAttributes[$code] = $product->getData($code);
        }
        return $productAttributes;
    }

    /**
     * Retrieve product attributes available in Post Rule
     *
     * @return array
     */
    private function getBlogProductAttributes()
    {
        if (!$this->attributes) {
            $this->attributes = $this->blogProductAttributes
                ->loadAttributeOptions()
                ->getAttributeOption();
        }
        return $this->attributes;
    }
}
