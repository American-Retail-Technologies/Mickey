<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Controller\Category;

use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package Aheadworks\Blog\Controller\Category
 */
class View extends \Aheadworks\Blog\Controller\Action
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $category = $this->categoryRepository->get(
                $this->getRequest()->getParam('blog_category_id')
            );
            if ($category->getStatus() == CategoryStatus::DISABLED
                || (!in_array($this->getStoreId(), $category->getStoreIds())
                    && !in_array(0, $category->getStoreIds()))
            ) {
                /** @var \Magento\Framework\Controller\Result\Forward $forward */
                $forward = $this->resultForwardFactory->create();
                return $forward
                    ->setModule('cms')
                    ->setController('noroute')
                    ->forward('index');
            }

            $resultPage = $this->resultPageFactory->create();
            $pageConfig = $resultPage->getConfig();
            $pageConfig->getTitle()->set($category->getName());
            $pageConfig->setMetadata('description', $category->getMetaDescription());
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->goBack();
        }
    }
}
