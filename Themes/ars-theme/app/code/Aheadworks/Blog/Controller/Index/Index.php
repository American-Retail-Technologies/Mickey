<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Controller\Index;

/**
 * Class Index
 * @package Aheadworks\Blog\Controller\Index
 */
class Index extends \Aheadworks\Blog\Controller\Action
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $pageConfig = $resultPage->getConfig();

        if ($tagId = $this->getRequest()->getParam('tag_id')) {
            $tag = $this->tagRepository->get($tagId);
            $pageConfig->getTitle()->set(__("Tagged with '%1'", $tag->getName()));
        } else {
            $pageConfig->getTitle()->set($this->getBlogTitle());
        }
        $pageConfig->setMetadata('description', $this->getBlogMetaDescription());

        return $resultPage;
    }
}
