<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Publish
 * @package Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button
 */
class Publish implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Publish Post'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save']
                ],
                'form-role' => 'save',
            ],
            'style' => 'width:150px;',
            'sort_order' => 50,
        ];
    }
}
