<?php
/**------------------------------------------------------------------------
* SM Categories - Version 3.1.0
* Copyright (c) 2015 YouTech Company. All Rights Reserved.
* @license - Copyrighted Commercial Software
* Author: YouTech Company
* Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Categories\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * Object manager
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	private $_objectManager;

	/**
	 * Application object
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Backend\Block\Template
	 */
	protected $_block;

	/**
	 * @var \Magento\Framework\Filesystem\Directory\WriteInterface
	 */
	protected $_directory;

	/**
	 * Image factory.
	 *
	 * @var \Magento\Framework\Image\AdapterFactory
	 */
	protected $_imageFactory;

	/**
	 * Class constructor
	 *
	 * @param \Magento\Backend\Block\Template $block
	 * @param \Magento\Framework\Filesystem $filesystem
	 * @param \Magento\Framework\Image\AdapterFactory $imageFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param string|null $scope
	 */
	public function __construct(
		\Magento\Backend\Block\Template $block,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Image\AdapterFactory $imageFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		$this->_block = $block;
		$this->_directory = $filesystem;
		$this->_storeManager = $storeManager;
		$this->_imageFactory = $imageFactory;
		$this->_objectManager = $objectManager;
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

	public function _trimEncode($text)
	{
		$str = strip_tags($text);
		$str = preg_replace('/\s(?=\s)/', '', $str);
		$str = preg_replace('/[\n\r\t]/', '', $str);
		$str = str_replace(' ', '', $str);
		$str = trim($str, "\xC2\xA0\n");
		return $str;
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

	public function getCatImage($category, $_config, $prefix = 'imgcat')
	{
		$images = $this->getCatImages($category, $_config, $prefix);
		return is_array($images) && count($images) ? $images[0] : null;
	}

	private function getCatImages($category, $_config, $prefix = 'imgcat')
	{

		$defaults = [
			'category_image' => 1,
			'category_description' => 1
		];

		$images_path = [];
		$priority = preg_split('/[\s|,|;]/', $_config[$prefix . '_order'], -1, PREG_SPLIT_NO_EMPTY);
		$mark = '';
		if (count($priority) > 0) {
			$priority = array_map('strtolower', $priority);
			$mark = [];

			for ($i = 0; $i < count($priority); $i++) {
				$type = $priority[$i];
				if (array_key_exists($type, $defaults))
					unset($defaults[$type]);
				if ($_config[$prefix . '_from_' . $type] == 1)
					$mark[$type] = 1;
			}
		}

		foreach ($defaults as $type => $val) {
			if ($_config[$prefix . '_from_' . $type] == 1)
				$mark[$type] = 1;
		}
		$helper_cms = $this->_objectManager->get('\Magento\Cms\Model\Template\FilterProvider');
		$helper_output = $this->_objectManager->get('\Magento\Catalog\Helper\Output');
		if (count($mark) > 0) {
			// prepare data.
			$images_data = null;

			foreach ($mark as $type => $true) {
				switch ($type) {
					case 'category_image':
						$image = '';
						if ($type == 'category_image') {
							//$image = ($category->getImage() != null) ? $category->getImage() :'';
							$image = ($category['image'] != null) ? $category['image'] :'';
						}
						$_media_dir = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/';
						$imagesUrl = $_media_dir . $image;
						if (file_exists($imagesUrl) || @getimagesize($imagesUrl) !== false) {
							array_push($images_path, $imagesUrl);
						}
						break;
					case 'category_description':
						$text = $helper_cms->getBlockFilter()->filter($helper_output->categoryAttribute($category, nl2br($category->getDescription()), 'description'));
						$inline_images = $this->getInlineImages($text);
						if (!empty($inline_images)) {
							for ($i = 0; $i < count($inline_images); $i++) {
								if(!empty($inline_images[$i]['src'])){
									if (file_exists($inline_images[$i]['src']) || @getimagesize($inline_images[$i]['src']) !== false) {
										array_push($images_path, $inline_images[$i]['src']);
									}
								}
							}
						}

						break;
					default:
				}
			}
		}
		$placeholder = $_config[$prefix .'_replacement'];
		$_placeholder = ($placeholder != '' && strpos($placeholder, 'http') !== false) ? $placeholder :  $this->_block->getViewFileUrl('Sm_Categories::images/nophoto.jpg');
		$_placeholder = @getimagesize($_placeholder) !== false ? $_placeholder : $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). $placeholder;
		if (count($images_path) == 0 && (@getimagesize($_placeholder) !== false)) {
			array_push($images_path, $_placeholder);
		}

		return $images_path;
	}

	private function getInlineImages($text)
	{
		$images = [];
		$searchTags = [
			'img' => '/<img[^>]+>/i',
			'input' => '/<input[^>]+type\s?=\s?"image"[^>]+>/i'
		];
		foreach ($searchTags as $tag => $regex) {
			preg_match_all($regex, $text, $m);
			if (is_array($m) && isset($m[0]) && count($m[0])) {
				foreach ($m[0] as $htmltag) {
					$tmp = $this->parseAttributes($htmltag);
					if (isset($tmp['src'])) {
						if ($tag == 'input') {
							array_push($images, ['src' => $tmp['src']]);
						} else {
							array_push($images, $tmp);
						}
					}
				}
			}
		}
		return $images;
	}

	private function parseAttributes($string)
	{
		//Initialize variables
		$attr = [];
		$retarray = [];

		// Lets grab all the key/value pairs using a regular expression
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

		if (is_array($attr)) {
			$numPairs = count($attr[1]);
			for ($i = 0; $i < $numPairs; $i++) {
				$retarray[$attr[1][$i]] = $attr[2][$i];
			}
		}
		return $retarray;
	}

	protected function _getBaseDirPub()
	{
		$dir = $this->_directory->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
		return $dir->getAbsolutePath();
	}

	protected function _getBaseDirMedia()
	{
		$dir = $this->_directory->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
		return $dir->getAbsolutePath();
	}

	public function _resizeImage($file, $config = [], $type = "product", $folder = 'resized')
	{
		$baseDirPub = $this->_getBaseDirPub();
		$baseDirMedia = $this->_getBaseDirMedia();
		if ((int)$config['function'] == 0 || $config['width'] <= 0) return $file;
		$_file_name = substr(strrchr($file, "/"), 1);
		$_media_dir = $baseDirMedia.'catalog'.'/'.$type.'/';
		$absPath = $file;
		$cache_dir = $_media_dir . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config));
		$dirImg = $baseDirPub.str_replace("/", "/", strstr($file, 'media'));
		$from_skin_nophoto = $baseDirPub.str_replace("/", "/", strstr($file, 'static'));
		$dirImg = strpos($dirImg, 'media') !== false ? $dirImg : $from_skin_nophoto;
		if (file_exists($cache_dir . '/' . $_file_name) && @getimagesize($cache_dir . '/' . $_file_name) !== false) {
			$new_image = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/' . $type . '/' . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config)) . '/' . $_file_name;
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

			$new_image = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/' . $type . '/' . $folder . '/' . $config['width'] . 'x' . $config['height'] . '/' . md5(serialize($config)) . '/' . $_file_name;
		} else {
			return $file;
		}
		return $new_image;
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
}