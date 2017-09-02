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

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\ImageFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class Image extends Main
{
    protected $_store;
    protected $_filesystem;
    protected $_imageFactory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        StoreInterface $store,
        Filesystem $filesystem,
        ImageFactory $imageFactory
    ) {
        $this->_store = $store;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        parent::__construct($objectManager, $context);
    }

    public function getSquareImage($imgUrl, $width, $height)
    {
        $imgPath = $this->_splitImageValue($imgUrl, 'path');
        $imgName = $this->_splitImageValue($imgUrl, 'name');

        // Path with Directory Seperator
        $imgPath = str_replace('/', DIRECTORY_SEPARATOR, $imgPath);

        // Absolute full path of Image
        $mediaPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('');
        $imgPathFull = $mediaPath . DIRECTORY_SEPARATOR . $imgPath . DIRECTORY_SEPARATOR . $imgName;

        // Resize folder is widthXheight
        $resizeFolder = 'cache' . DIRECTORY_SEPARATOR . $width . 'x' . $height;

        // Image resized path will then be
        $imageResizedPath = $mediaPath . DIRECTORY_SEPARATOR . $imgPath . DIRECTORY_SEPARATOR . $resizeFolder . DIRECTORY_SEPARATOR . $imgName;

        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath)) {
            if (file_exists($imgPathFull)) {
                $imageObj = $this->_imageFactory->create(['fileName' => $imgPathFull]);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->quality(100);

                $imageObj->resize($width, $height);
                $imageObj->save($imageResizedPath);

                unset($imageObj);

                if (!file_exists($imageResizedPath)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Return full http path of the image

        return $this->_store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $imgPath . '/' . $resizeFolder . '/' . $imgName;
    }

    private function _splitImageValue($imageValue, $attr)
    {
        $imArray = explode('/', $imageValue);

        $name = $imArray[count($imArray)-1];
        if ($attr == 'path') {
            return implode('/', array_diff($imArray, [$name]));
        } else {
            return $name;
        }
    }

    public function resize($url, $width, $height = 0)
    {
        if ($height == 0) {
            $height = $width;
        }
        return $this->getSquareImage($url, $width, $height);
    }
}
