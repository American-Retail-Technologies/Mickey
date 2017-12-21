<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\TagRepositoryInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Listing
 * @package Aheadworks\Blog\Block\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Listing
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param PostRepositoryInterface $postRepository
     * @param TagRepositoryInterface $tagRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param DateTime $dateTime
     */
    public function __construct(
        PostRepositoryInterface $postRepository,
        TagRepositoryInterface $tagRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        DateTime $dateTime
    ) {
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->dateTime = $dateTime;
    }

    /**
     * Get posts list
     *
     * @return \Aheadworks\Blog\Api\Data\PostInterface[]
     */
    public function getPosts()
    {
        return $this->postRepository
            ->getList($this->buildSearchCriteria())
            ->getItems();
    }

    /**
     * Retrieves search criteria builder
     *
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * Apply pagination
     *
     * @param \Aheadworks\Blog\Block\PostList\Pager $pager
     * @return void
     */
    public function applyPagination(\Aheadworks\Blog\Block\PostList\Pager $pager)
    {
        $this->prepareSearchCriteriaBuilder();
        $pager->applyPagination($this->searchCriteriaBuilder, $this->postRepository);
    }

    /**
     * Build search criteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function buildSearchCriteria()
    {
        $this->prepareSearchCriteriaBuilder();
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Prepares search criteria builder
     *
     * @return void
     */
    private function prepareSearchCriteriaBuilder()
    {
        $this->searchCriteriaBuilder
            ->addFilter(PostInterface::STATUS, Status::PUBLICATION)
            ->addFilter(PostInterface::STORE_IDS, $this->storeManager->getStore()->getId());
        /** @var \Magento\Framework\Api\SortOrder $publishDateOrder */
        $publishDateOrder = $this->sortOrderBuilder
            ->setField(PostInterface::PUBLISH_DATE)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($publishDateOrder);
        if ($this->request->getParam('blog_category_id')) {
            $this->searchCriteriaBuilder->addFilter(
                PostInterface::CATEGORY_IDS,
                $this->request->getParam('blog_category_id')
            );
        }
        if ($tagId = $this->request->getParam('tag_id')) {
            $this->searchCriteriaBuilder->addFilter('tag_id', $tagId);
        }
    }
}
