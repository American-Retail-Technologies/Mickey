<?php
/*------------------------------------------------------------------------
# SM QuickView - Version 3.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\QuickView\Block;

class QuickView extends \Magento\Framework\View\Element\Template
{
	protected $_config = null;

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfigInterface;

	/**
	 * Object manager
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	private $_objectManager;

	/**
	 * Class constructor
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Framework\App\ResourceConnection $resourceConnection
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param \Magento\Eav\Model\Config $eavConfig
	 * @param string|null $scope
	 */
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = [],
		$attr = null
	)
	{
		$this->_objectManager = $objectManager;
		$this->_scopeConfigInterface = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_config = $this->_getCfg($attr, $data);
		parent::__construct($context, $data);
	}
	public function _getCfg($attr = null , $data = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfigInterface->getValue('quickview');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfigInterface->getValue('quickview');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfigInterface->getValue('quickview/'.$group.'', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			foreach ($_cfgs as $_key => $_cfg) {
				$cfgs[$_key] = $_cfg;
			}
		}

		// get output config
		$configs = [];
		foreach ($defaults as $key => $def) {
			if (isset($defaults[$key])) {
				$configs[$key] = $cfgs[$key];
			} else {
				unset($cfgs[$key]);
			}
		}
		$cf = ($attr != null) ? array_merge($configs, $attr) : $configs;
		$this->_config = ($data != null) ? array_merge($cf, $data) : $cf;
		return $this->_config;
	}

	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

	public function _setConfig($name, $value = null)
	{

		if (is_null($this->_config)) $this->_getCfg();
		if (is_array($name)) {
			$this->_config = array_merge($this->_config, $name);

			return;
		}
		if (!empty($name) && isset($this->_config[$name])) {
			$this->_config[$name] = $value;
		}
		return true;
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _getCurrentUrl(){
		$urlinterface = $this->_objectManager->get('\Magento\Framework\UrlInterface');
		return $urlinterface->getCurrentUrl();
	}
}