<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Controller\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package Aheadworks\Blog\Controller\Post
 */
class View extends \Aheadworks\Blog\Controller\Action
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $post = $this->postRepository->get(
                $this->getRequest()->getParam('post_id')
            );
            if ($post->getStatus() != Status::PUBLICATION
                || strtotime($post->getPublishDate()) > time()
                || (!in_array($this->getStoreId(), $post->getStoreIds())
                    && !in_array(0, $post->getStoreIds()))
            ) {
                /**  @var \Magento\Framework\Controller\Result\Forward $forward */
                $forward = $this->resultForwardFactory->create();
                return $forward
                    ->setModule('cms')
                    ->setController('noroute')
                    ->forward('index');
            }
            if ($this->getRequest()->getParam('blog_category_id')) {
                // Forced redirect to post url without category id
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($this->url->getPostUrl($post));
                return $resultRedirect;
            }

            $resultPage = $this->resultPageFactory->create();
            $pageConfig = $resultPage->getConfig();
            $pageConfig->getTitle()->set($post->getTitle());
            $pageConfig->setMetadata('description', $post->getMetaDescription());
            $pageConfig->setMetadata('og:description', $this->getMetaOgDescription($post));
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->goBack();
        }
    }

    /**
     * Retrieve og:description meta tag from post
     *
     * @param PostInterface $post
     * @return string
     */
    private function getMetaOgDescription($post)
    {
        $content = $this->getClearContent($post->getShortContent());
        if (strlen($content) == 0) {
            $content = $this->getClearContent($post->getContent());
        }
        return $content;
    }

    /**
     * Retrieve clear content
     *
     * @param string $content
     * @return string
     */
    private function getClearContent($content)
    {
        $lenContent = 256;
        $content = trim(strip_tags($content));

        return strlen($content) > $lenContent
            ? substr($content, 0, $lenContent)
            : $content;
    }
}
