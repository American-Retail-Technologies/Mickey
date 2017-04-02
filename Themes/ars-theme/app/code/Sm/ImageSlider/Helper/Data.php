<?php
/*------------------------------------------------------------------------
# SM Image Slider - Version 2.1.0
# Copyright (c) 2015 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
namespace Sm\ImageSlider\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
	protected $_directory;
	protected $_imageFactory;
	protected $_storeManager;
	protected $_scopeConfigInterface;

	const XML_ENABLE_DISABLE = 'imageslider/general/isenabled';
	const XML_INCLUDE_JQUERY = 'imageslider/advanced/include_jquery';

	public function __construct(
		Filesystem $filesystem,
		AdapterFactory $imageFactory,
		ScopeConfigInterface $scopeConfigInterface,
		StoreManagerInterface $storeManagerInterface
	)
	{
		$this->_directory = $filesystem;
		$this->_imageFactory = $imageFactory;
		$this->_storeManager = $storeManagerInterface;
		$this->_scopeConfigInterface = $scopeConfigInterface;
	}

	public function getEnableDisable($store = null)
	{
		return $this->_scopeConfigInterface->isSetFlag(self::XML_ENABLE_DISABLE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $store);
	}

	public function getIncludeJquery($store = null)
	{
		return $this->_scopeConfigInterface->isSetFlag(self::XML_INCLUDE_JQUERY, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $store);
	}

	protected function _getImageSliderDirMedia()
	{
		$dir = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $dir;
	}

	protected function _getBaseDirPub()
	{
		$dir = $this->_directory->getDirectoryWrite(DirectoryList::PUB);
		return $dir->getAbsolutePath();
	}

	protected function _getBaseDirMedia()
	{
		$dir = $this->_directory->getDirectoryWrite(DirectoryList::MEDIA);
		return $dir->getAbsolutePath();
	}

	/**
	 * strips all tag, except a, em, strong
	 * @param string $text
	 * @return string
	 */
	public function _cleanText($text)
	{
		$text = strip_tags($text, '<a><b><blockquote><code><del><dd><dl><dt><em><h1><h2><h3><i><kbd><p><pre><s><sup><strong><strike><br><hr>');
		$text = trim($text);
		return $text;
	}

	/**
	 * Truncate string by $length
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @return string
	 */
	public function truncate($string, $length, $etc = '...')
	{
		return defined('MB_OVERLOAD_STRING')
			? $this->_mb_truncate($string, $length, $etc)
			: $this->_truncate($string, $length, $etc);
	}

	/**
	 * Truncate mutibyte string if it's size over $length
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @return string
	 */
	private function _mb_truncate($string, $length, $etc = '...')
	{
		$encoding = mb_detect_encoding($string);
		if ($length > 0 && $length < mb_strlen($string, $encoding)) {
			$buffer = '';
			$buffer_length = 0;
			$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$self_closing_tag = explode(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
			$open = [];

			foreach ($parts as $i => $s) {
				if (false === mb_strpos($s, '<')) {
					$s_length = mb_strlen($s, $encoding);
					if ($buffer_length + $s_length < $length) {
						$buffer .= $s;
						$buffer_length += $s_length;
					} else if ($buffer_length + $s_length == $length) {
						if (!empty($etc)) {
							$buffer .= ($s[$s_length - 1] == ' ') ? $etc : " $etc";
						}
						break;
					} else {
						$words = preg_split('/([^\s]*)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
						$space_end = false;
						foreach ($words as $w) {
							if ($w_length = mb_strlen($w, $encoding)) {
								if ($buffer_length + $w_length < $length) {
									$buffer .= $w;
									$buffer_length += $w_length;
									$space_end = (trim($w) == '');
								} else {
									if (!empty($etc)) {
										$more = $space_end ? $etc : " $etc";
										$buffer .= $more;
										$buffer_length += mb_strlen($more);
									}
									break;
								}
							}
						}
						break;
					}
				} else {
					preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
					//$tagclose = isset($m[1]) && trim($m[1])=='/';
					if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)) {
						array_push($open, $m[2]);
					} else if (trim($m[1]) == '/') {
						$tag = array_pop($open);
						if ($tag != $m[2]) {
							// uncomment to to check invalid html string.
						}
					}
					$buffer .= $s;
				}
			}
			// close tag openned.
			while (count($open) > 0) {
				$tag = array_pop($open);
				$buffer .= "</$tag>";
			}
			return $buffer;
		}
		return $string;
	}

	/**
	 * Truncate string if it's size over $length
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @return string
	 */
	private function _truncate($string, $length, $etc = '...')
	{
		if ($length > 0 && $length < strlen($string)) {
			$buffer = '';
			$buffer_length = 0;
			$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$self_closing_tag = split(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
			$open = [];

			foreach ($parts as $i => $s) {
				if (false === strpos($s, '<')) {
					$s_length = strlen($s);
					if ($buffer_length + $s_length < $length) {
						$buffer .= $s;
						$buffer_length += $s_length;
					} else if ($buffer_length + $s_length == $length) {
						if (!empty($etc)) {
							$buffer .= ($s[$s_length - 1] == ' ') ? $etc : " $etc";
						}
						break;
					} else {
						$words = preg_split('/([^\s]*)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
						$space_end = false;
						foreach ($words as $w) {
							if ($w_length = strlen($w)) {
								if ($buffer_length + $w_length < $length) {
									$buffer .= $w;
									$buffer_length += $w_length;
									$space_end = (trim($w) == '');
								} else {
									if (!empty($etc)) {
										$more = $space_end ? $etc : " $etc";
										$buffer .= $more;
										$buffer_length += strlen($more);
									}
									break;
								}
							}
						}
						break;
					}
				} else {
					preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
					//$tagclose = isset($m[1]) && trim($m[1])=='/';
					if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)) {
						array_push($open, $m[2]);
					} else if (trim($m[1]) == '/') {
						$tag = array_pop($open);
						if ($tag != $m[2]) {
							// uncomment to to check invalid html string.
						}
					}
					$buffer .= $s;
				}
			}
			// close tag openned.
			while (count($open) > 0) {
				$tag = array_pop($open);
				$buffer .= "</$tag>";
			}
			return $buffer;
		}
		return $string;
	}

	/**
	 * Parse and build target attribute for links.
	 * @param string $value (_self, _blank, _windowopen, _modal)
	 * _blank    Opens the linked document in a new window or tab
	 * _self    Opens the linked document in the same frame as it was clicked (this is default)
	 * _parent    Opens the linked document in the parent frame
	 * _top    Opens the linked document in the full body of the window
	 * _windowopen  Opens the linked document in a Window
	 * _modal        Opens the linked document in a Modal Window
	 */
	public function parseTarget($type = '_self')
	{
		$target = '';
		switch ($type) {
			default:
			case '_self':
				break;
			case '_blank':
			case '_parent':
			case '_top':
				$target = 'target="' . $type . '"';
				break;
			case '_windowopen':
				$target = "onclick=\"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,false');return false;\"";
				break;
			case '_modal':
				// user process
				break;
		}
		return $target;
	}

	public function HexToRGB($hex)
	{
		$hex = preg_replace("/#/", "", $hex);
		$color = [];
		if (strlen($hex) == 3) {
			$color['r'] = hexdec(substr($hex, 0, 1) . $r);
			$color['g'] = hexdec(substr($hex, 1, 1) . $g);
			$color['b'] = hexdec(substr($hex, 2, 1) . $b);
		} else if (strlen($hex) == 6) {
			$color['r'] = hexdec(substr($hex, 0, 2));
			$color['g'] = hexdec(substr($hex, 2, 2));
			$color['b'] = hexdec(substr($hex, 4, 2));
		}

		return array_values($color);
	}

	public function _resizeImage($image, $config = [], $type = "product", $folder = 'resized')
	{
		$baseDirPub = $this->_getBaseDirPub();
		$baseDirMedia = $this->_getBaseDirMedia();
		if ((int)$config['function'] == 0 || $config['width'] <= 0) return $image;
		$_file_name = substr(strrchr($image, "/"), 1);
		$_media_dir = $baseDirMedia.'catalog'.'/'.$type.'/';
		$absPath = $image;
		$cache_dir = $_media_dir . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config));
		$dirImg = $baseDirPub.str_replace("/", "/", strstr($image, 'media'));
		$from_skin_nophoto = $baseDirPub.str_replace("/", "/", strstr($image, 'static'));
		$dirImg = strpos($dirImg, 'media') !== false ? $dirImg : '';
		$dirImg = (strpos($from_skin_nophoto, 'skin') !== false && $dirImg == '') ? $from_skin_nophoto : $dirImg;

		if (file_exists($cache_dir . '/' . $_file_name) && @getimagesize($cache_dir . '/' . $_file_name) !== false) {
			$new_image = $this->_getImageSliderDirMedia().'catalog/' . $type . '/' . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config)) . '/' . $_file_name;
		} elseif ((file_exists($dirImg) && $dirImg != '')) {

			if (!is_dir($cache_dir)) {
				@mkdir($cache_dir, 0777, true);
			}
			$height = ($config['height'] == '') ? null : $config['height'];
			$hex = $config['background'];
			$rgbColor = $this->HexToRGB($hex);
			$image = $this->_imageFactory->create();
			$image->open($absPath);
			$image->backgroundColor($rgbColor);
			$image->resize($config['width'], $height);
			$image->save($cache_dir . '/' . $_file_name);

			$new_image = $this->_getImageSliderDirMedia().'catalog/' . $type . '/' . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config)) . '/' . $_file_name;
		} else {
			$new_image = $image;
			return $new_image;
		}
		return $new_image;
	}
}