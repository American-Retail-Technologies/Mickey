<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Ui\DataProvider;

use Aheadworks\Blog\Model\ResourceModel\Post\Grid\CollectionFactory;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Blog\Model\ResourceModel\Post\Collection;

/**
 * Post data provider
 */
class PostDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create()
            ->setFlag(Collection::IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS, false)
        ;
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get('aw_blog_post');
        if (!empty($dataFromForm)) {
            $object = $this->collection->getNewEmptyItem();
            $object->setData($dataFromForm);
            $data[$object->getId()] = $object->getData();
            $this->dataPersistor->clear('aw_blog_post');
        } else {
            $id = $this->request->getParam($this->getRequestFieldName());
            /** @var \Aheadworks\Blog\Model\Post $post */
            foreach ($this->getCollection()->getItems() as $post) {
                if ($id == $post->getId()) {
                    $data[$id] = $this->prepareFormData($post->getData());
                }
            }
        }
        return $data;
    }

    /**
     * Prepare form data
     *
     * @param array $itemData
     * @return array
     */
    private function prepareFormData(array $itemData)
    {
        $itemData['is_published'] = $itemData['status'] == Status::PUBLICATION ? 1 : 0;
        $itemData['is_scheduled'] = $itemData['status'] == Status::SCHEDULED ? 1 : 0;
        $itemData['has_short_content'] = !empty($itemData['short_content']);
        $itemData['tag_names'] = array_values($itemData['tag_names']);
        return $itemData;
    }
}
