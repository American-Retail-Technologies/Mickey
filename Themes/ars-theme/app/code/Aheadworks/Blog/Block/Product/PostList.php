<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block\Product;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Block\Post\Listing as PostListing;

/**
 * Class PostList
 * @package Aheadworks\Blog\Block
 */
class PostList extends \Magento\Framework\View\Element\Template
{
    /**
     * Path to template file in theme
     * @var string
     */
    protected $_template = 'Aheadworks_Blog::product/post/list.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var PostListing
     */
    private $postListing;

    /**
     * @param Context $context
     * @param Config $config
     * @param Url $url
     * @param PostListing $postListing
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        Url $url,
        PostListing $postListing,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->url = $url;
        $this->postListing = $postListing;
    }

    /**
     * Retrieve product posts
     *
     * @return array|null
     */
    public function getPosts()
    {
        if ($this->config->isDisplayPostsOnProductPage()) {
            if ($productId = $this->getRequest()->getParam('id')) {
                $this->postListing->getSearchCriteriaBuilder()->addFilter(
                    'product_id',
                    $productId
                );
            }
            return $this->postListing->getPosts();
        }
        return [];
    }

    /**
     * Retrieve post url
     *
     * @param PostInterface $post
     * @return string
     */
    public function getPostUrl(PostInterface $post)
    {
        return $this->url->getPostUrl($post);
    }
}
