<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Controller\Block;

use Aheadworks\Blog\Block\Widget\RecentPost;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\App\Action\Context;

/**
 * Class Render
 *
 * @package Aheadworks\Blog\Controller\Block
 */
class Render extends \Magento\Framework\App\Action\Action
{
    /**
     * @var InlineInterface
     */
    private $translateInline;

    /**
     * @param Context $context
     * @param InlineInterface $translateInline
     */
    public function __construct(
        Context $context,
        InlineInterface $translateInline
    ) {
        parent::__construct($context);
        $this->translateInline = $translateInline;
    }

    /**
     * Returns block content depends on ajax request
     *
     * @return \Magento\Framework\Controller\Result\Redirect|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }

        $blocks = $this->getRequest()->getParam('blocks');
        $data = $this->getBlocks($blocks);

        $this->translateInline->processResponseBody($data);
        $this->getResponse()->appendBody(json_encode($data));
    }

    /**
     * Get blocks from layout
     *
     * @param string $blocks
     * @return string[]
     */
    private function getBlocks($blocks)
    {
        if (!$blocks) {
            return [];
        }
        $blocks = json_decode($blocks);

        $data = [];
        $layout = $this->_view->getLayout();
        foreach ($blocks as $blockDataEncode) {
            try {
                $blockData = unserialize(base64_decode($blockDataEncode));
                $blockName = '';
                if (isset($blockData['name'])) {
                    $blockName = $blockData['name'];
                    unset($blockData['name']);
                }

                if (strpos($blockName, RecentPost::WIDGET_NAME_PREFIX, 0) === false) {
                } else {
                    $blockInstance = $layout->createBlock(
                        RecentPost::class,
                        '',
                        ['data' => $blockData]
                    );
                    if (is_object($blockInstance)) {
                        $blockInstance->setNameInLayout($blockName);
                        $data[$blockDataEncode] = $blockInstance->toHtml();
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return $data;
    }
}
