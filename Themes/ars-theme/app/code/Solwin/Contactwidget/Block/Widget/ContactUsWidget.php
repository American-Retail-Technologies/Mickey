<?php
/**
 * Solwin Infotech
 * Solwin Contact Form Widget Extension
 *
 * @category   Solwin
 * @package    Solwin_Contactwidget
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\Contactwidget\Block\Widget;

class ContactUsWidget
extends \Magento\Framework\View\Element\Template
implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setTemplate('widget/custom_widget.phtml');
    }

    /**
     * Get form action url
     */
    public function getFormActionUrl() {
        return $this->getUrl('contactwidget/widget/index');
    }

    /**
     * Get config value
     */
    public function getConfigValue($value = '') {
        return $this->_scopeConfig
                ->getValue(
                        $value,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
    }

}