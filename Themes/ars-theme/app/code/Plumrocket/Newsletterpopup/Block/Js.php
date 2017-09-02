<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block;

use Magento\Cms\Model\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Cookies;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;

class Js extends ViewTemplate
{
    protected $_dataHelper;
    protected $_registry;
    protected $_cmsPage;

    private $_disableThis = false;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Registry $registry,
        Page $cmsPage,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_registry = $registry;
        $this->_cmsPage = $cmsPage;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        if (!$this->_dataHelper->moduleEnabled() || $this->_disableThis) {
            $this->setTemplate('empty.phtml');
        }
        return parent::_toHtml();
    }

    public function getPopupArea()
    {
        if (!$this->_dataHelper->moduleEnabled()) {
            return Show::ON_ALL_PAGES;
        }

        // if cookie is global and array of locked popups is not empty
        if ($this->_dataHelper->getConfig($this->_dataHelper->getConfigSectionId() . '/general/cookies_usage') == Cookies::_GLOBAL
            && $this->_dataHelper->getLockedPopupIds()
        ) {
            return Show::ON_ACCOUNT_PAGES;
        }

        $request     = $this->getRequest();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();
        $route         = $request->getRouteName();
        $module     = $request->getModuleName();

        if (($route == 'cms' && $controller == 'index' && $action == 'index')
            || ($route == 'privatesales' && $controller == 'homepage' && $action == 'index')
            || ($route == 'catalog' && $controller == 'category' && $action == 'homepage')
        ) {
            return Show::ON_HOME_PAGE;
        } elseif ($route == 'catalog' && $controller == 'category') { //  && $action == 'view'
            return Show::ON_CATEGORY_PAGES;
        } elseif ($route == 'catalog' && $controller == 'product') { //  && $action == 'view'
            return Show::ON_PRODUCT_PAGES;
        } elseif ($route == 'cms') {
            return Show::ON_CMS_PAGES;
        } elseif ($route == 'customer' && $controller == 'account') {
            return Show::ON_ACCOUNT_PAGES;
        }

        return Show::ON_ALL_PAGES;
    }

    public function isEnableAnalytics()
    {
        return $this->_dataHelper->moduleEnabled() && (bool)$this->_dataHelper->getConfig($this->_dataHelper->getConfigSectionId() . '/general/enable_analytics');
    }

    public function disable()
    {
        $this->_disableThis = true;

        $parent = $this->getParentBlock();
        // Another our modules may share it js file
        // !! Remove function and change pathes.
        /*$parent->removeItem('skin_js', 'js/plumrocket/prnewsletterpopup/popup.js');
        $parent->removeItem('skin_css', 'css/plumrocket/prnewsletterpopup/prnewsletterpopup.css');
        $parent->removeItem('skin_css', 'css/plumrocket/prnewsletterpopup/prnewsletterpopup-additional.css');
        $parent->removeItem('skin_css', 'css/plumrocket/prnewsletterpopup/prnewsletterpopup-ie8.css');*/
    }

    public function getActionUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/subscribe'));
    }

    public function getCancelUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/cancel'));
    }

    public function getBlockUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/block'));
    }

    public function getHistoryUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/history'));
    }

    public function getJsonConfig()
    {
        $config =
        [
            'enable_analytics' => (int)$this->isEnableAnalytics(),
            'area' => $this->getPopupArea(),
            'cmsPage' => (string)$this->_cmsPage->getIdentifier(),
            'categoryId' => ($category = $this->_registry->registry('current_category'))? $category->getId() : 0,
            'productId' => ($product = $this->_registry->registry('current_product'))? $product->getId() : 0,
            'action_url' => $this->getActionUrl(),
            'cancel_url' => $this->getCancelUrl(),
            'block_url' => $this->getBlockUrl(),
            'history_url' => $this->getHistoryUrl(),
        ];

        return json_encode($config);
    }

}
