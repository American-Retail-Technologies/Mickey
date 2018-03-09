<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Aheadworks\Blog\Api\Data\PostSearchResultsInterfaceFactory;
use Aheadworks\Blog\Model\PostFactory;
use Aheadworks\Blog\Model\PostRegistry;
use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;
use Aheadworks\Blog\Model\Indexer\ProductPost\Processor;

/**
 * Post repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements \Aheadworks\Blog\Api\PostRepositoryInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var PostInterfaceFactory
     */
    private $postDataFactory;

    /**
     * @var PostRegistry
     */
    private $postRegistry;

    /**
     * @var PostSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var Processor
     */
    private $indexProcessor;

    /**
     * @param PostFactory $postFactory
     * @param PostInterfaceFactory $postDataFactory
     * @param PostRegistry $postRegistry
     * @param PostSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param EntityManager $entityManager
     * @param ConditionConverter $conditionConverter
     * @param Processor $indexProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PostFactory $postFactory,
        PostInterfaceFactory $postDataFactory,
        PostRegistry $postRegistry,
        PostSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        EntityManager $entityManager,
        ConditionConverter $conditionConverter,
        Processor $indexProcessor
    ) {
        $this->postFactory = $postFactory;
        $this->postDataFactory = $postDataFactory;
        $this->postRegistry = $postRegistry;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->entityManager = $entityManager;
        $this->conditionConverter = $conditionConverter;
        $this->indexProcessor = $indexProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(PostInterface $post)
    {
        $origPostData = null;
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postFactory->create();
        if ($postId = $post->getId()) {
            $this->entityManager->load($postModel, $postId);
            $origPostData = $postModel->getData();
        }
        $postModel->addData(
            $this->dataObjectProcessor->buildOutputDataArray($post, PostInterface::class)
        );
        if ($postModel->getStatus() == PostStatus::DRAFT) {
            $postModel->setPublishDate(null);
        }
        if (is_array($postModel->getProductCondition())) {
            $postModel->setProductCondition(serialize($postModel->getProductCondition()));
        }
        $this->entityManager->save($postModel);
        $post = $this->convertPostConditionsToDataModel($postModel);
        $this->postRegistry->push($post);
        if ($this->isPostParamsChanged($post, $origPostData)) {
            if ($this->indexProcessor->isIndexerScheduled()) {
                $this->indexProcessor->markIndexerAsInvalid();
            } else {
                $this->indexProcessor->reindexRow($post->getId());
            }
        }

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function get($postId)
    {
        if (null === $this->postRegistry->retrieve($postId)) {
            /** @var PostInterface $postModel */
            $postModel = $this->postDataFactory->create();
            $this->entityManager->load($postModel, $postId);
            if (!$postModel->getId()) {
                throw NoSuchEntityException::singleField('postId', $postId);
            } else {
                $postModel = $this->convertPostConditionsToDataModel($postModel);
                $this->postRegistry->push($postModel);
            }
        }
        return $this->postRegistry->retrieve($postId);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUrlKey($postUrlKey)
    {
        $postModel = $this->postFactory->create();
        $postModel->loadByUrlKey($postUrlKey);
        if (!$postModel->getId()) {
            throw NoSuchEntityException::singleField('urlKey', $postUrlKey);
        } else {
            $postModel = $this->convertPostConditionsToDataModel($postModel);
        }

        return $postModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Aheadworks\Blog\Api\Data\PostSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process($collection, PostInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == PostInterface::STORE_IDS) {
                    $collection->addStoreFilter($filter->getValue());
                } elseif ($filter->getField() == PostInterface::CATEGORY_IDS) {
                    $collection->addCategoryFilter($filter->getValue());
                } elseif ($filter->getField() == 'tag_id') {
                    $collection->addTagFilter($filter->getValue());
                } elseif ($filter->getField() == 'product_id') {
                    $collection->addRelatedProductFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[] = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $posts = [];
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        foreach ($collection as $postModel) {
            $posts[] = $this->convertPostConditionsToDataModel($postModel);
        }

        $searchResults->setItems($posts);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PostInterface $post)
    {
        return $this->deleteById($post->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($postId)
    {
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postFactory->create();
        $this->entityManager->load($postModel, $postId);
        if (!$postModel->getId()) {
            throw NoSuchEntityException::singleField('postId', $postId);
        }
        $this->entityManager->delete($postModel);
        $this->postRegistry->remove($postId);
        return true;
    }

    /**
     * Convert post conditions from array to data model
     *
     * @param PostInterface $post
     * @return PostInterface
     */
    private function convertPostConditionsToDataModel(PostInterface $post)
    {
        if ($post->getProductCondition()) {
            $conditionArray = unserialize($post->getProductCondition());
            $conditionDataModel = $this->conditionConverter
                ->arrayToDataModel($conditionArray);
            $post->setProductCondition($conditionDataModel);
        } else {
            $post->setProductCondition('');
        }

        return $post;
    }

    /**
     * If the necessary post parameters have been changed
     *
     * @param PostInterface $post
     * @param array $origPostData
     * @return bool
     */
    private function isPostParamsChanged($post, $origPostData)
    {
        if (!$origPostData) {
            return true;
        }
        $origPost = $this->postDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $origPost,
            $origPostData,
            PostInterface::class
        );
        $origPost = $this->convertPostConditionsToDataModel($origPost);
        if ($post->getProductCondition() != $origPost->getProductCondition()) {
            return true;
        }
        if ($post->getStoreIds() != $origPost->getStoreIds()) {
            return true;
        }
        return false;
    }
}
