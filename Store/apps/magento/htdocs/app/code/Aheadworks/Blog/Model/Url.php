<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Controller\Router;
use Magento\Framework\UrlInterface;

/**
 * Class Url
 * @package Aheadworks\Blog\Model
 */
class Url
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Config $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(Config $config, UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieves url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route, $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieves blog home url
     *
     * @return string
     */
    public function getBlogHomeUrl()
    {
        return $this->getUrl(null, ['_direct' => $this->config->getRouteToBlog() . '/']);
    }

    /**
     * Retrieves post url
     *
     * @param PostInterface $post
     * @return string
     */
    public function getPostUrl(PostInterface $post)
    {
        $parts = [$this->config->getRouteToBlog()];
        $parts[] = $post->getUrlKey();
        return $this->getUrl(null, ['_direct' => implode('/', $parts) . '/']);
    }

    /**
     * @param PostInterface $post
     * @param int|null $storeId
     * @return string
     */
    public function getPostRoute(PostInterface $post, $storeId = null)
    {
        return $this->config->getRouteToBlog($storeId) . '/' . $post->getUrlKey() . '/';
    }

    /**
     * Retrieves post url
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category)
    {
        return $this->getUrl(null, ['_direct' => $this->getCategoryRoute($category)]);
    }

    /**
     * @param CategoryInterface $category
     * @param int|null $storeId
     * @return string
     */
    public function getCategoryRoute(CategoryInterface $category, $storeId = null)
    {
        return $this->config->getRouteToBlog($storeId) . '/' . $category->getUrlKey() . '/';
    }

    /**
     * Retrieves search by tag url
     *
     * @param TagInterface|string $tag
     * @return string
     */
    public function getSearchByTagUrl($tag)
    {
        $tagName = $tag instanceof TagInterface ? $tag->getName() : $tag;
        return $this->getUrl(
            null,
            ['_direct' => $this->config->getRouteToBlog() . '/' . Router::TAG_KEY . '/' . urlencode($tagName) . '/']
        );
    }
}
