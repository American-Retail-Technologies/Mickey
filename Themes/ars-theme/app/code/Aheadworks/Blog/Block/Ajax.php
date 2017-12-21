<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block;

/**
 * Class Ajax
 *
 * @package Aheadworks\Blog\Block
 */
class Ajax extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'url' => $this->getUrl(
                'aw_blog/block/render/',
                [
                    '_current' => true,
                    '_secure' => $this->templateContext->getRequest()->isSecure()
                ]
            )
        ];
        return json_encode($params);
    }
}
