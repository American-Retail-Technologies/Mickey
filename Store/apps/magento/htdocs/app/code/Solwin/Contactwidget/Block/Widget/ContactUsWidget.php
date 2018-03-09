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
     * Determine which form is used
     */
    protected $formTitle;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$formTitle = $data['cc_title'];
		if($formTitle === "Request Catalog"){
			$this->setTemplate('widget/custom_widget.phtml');
		}elseif($formTitle === "Request Custom Tissue and Gift Wraps"){
			$this->setTemplate('widget/custom_tissue.phtml');
		}elseif($formTitle === "Request Custom Label and Sticker"){
			$this->setTemplate('widget/custom_label.phtml');
		}elseif($formTitle === "Request Custom Bags"){
			$this->setTemplate('widget/custom_bags.phtml');
		}elseif($formTitle === "Request Custom Boxes"){
			$this->setTemplate('widget/custom_box.phtml');
		}
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