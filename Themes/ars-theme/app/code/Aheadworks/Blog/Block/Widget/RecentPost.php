<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Aheadworks\Blog\Block\Post\ListingFactory ;

/**
 * Tag Cloud Widget
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecentPost extends \Aheadworks\Blog\Block\Sidebar\Recent implements BlockInterface
{
    /**
     * @var string
     */
    const WIDGET_NAME_PREFIX = 'aw_blog_widget_recent_post_';

    /**
     * Path to template file in theme
     * @var string
     */
    protected $_template = 'Aheadworks_Blog::widget/recent_post/default.phtml';

    /**
     * Is ajax request or not
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isAjax();
    }

    /**
     * {@inheritdoc}
     */
    public function getPosts($numberToDisplay = null)
    {
        return parent::getPosts($this->getData('number_to_display'));
    }

    /**
     * Checks blog is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isBlogEnabled();
    }

    /**
     * Retrieve widget encode data
     *
     * @return string
     */
    public function getWidgetEncodeData()
    {
        return base64_encode(
            serialize(
                [
                    'name' => $this->getNameInLayout(),
                    'number_to_display' => $this->getData('number_to_display'),
                    'title' => $this->getData('title'),
                    'template' => $this->getTemplate()
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNameInLayout()
    {
        return self::WIDGET_NAME_PREFIX . parent::getNameInLayout();
    }
}
