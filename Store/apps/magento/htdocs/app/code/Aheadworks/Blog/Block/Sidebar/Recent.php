<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block\Sidebar;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Block\Post\ListingFactory ;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Recent posts sidebar
 * @package Aheadworks\Blog\Block\Sidebar
 */
class Recent extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var \Aheadworks\Blog\Block\Post\Listing
     */
    private $postListing;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param Context $context
     * @param PostRepositoryInterface $postRepository
     * @param ListingFactory $postListingFactory
     * @param Config $config
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostRepositoryInterface $postRepository,
        ListingFactory $postListingFactory,
        Config $config,
        Url $url,
        array $data = []
    ) {
        $this->postRepository = $postRepository;
        $this->postListing = $postListingFactory->create();
        $this->config = $config;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve recent posts
     *
     * @param int $numberToDisplay
     * @return PostInterface[]
     */
    public function getPosts($numberToDisplay = null)
    {
        $numberToDisplay = $numberToDisplay ? : $this->config->getNumRecentPosts();
        $this->postListing->getSearchCriteriaBuilder()->setPageSize(
            $numberToDisplay
        );
        if ($postId = $this->getRequest()->getParam('post_id')) {
            $this->postListing->getSearchCriteriaBuilder()->addFilter(
                PostInterface::ID,
                $postId,
                'neq'
            );
        }
        return $this->postListing->getPosts();
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

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [\Aheadworks\Blog\Model\Post::CACHE_TAG_LISTING];
    }
}
