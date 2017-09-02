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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action\Context;
use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;
use Plumrocket\Newsletterpopup\Model\Popup;

class Save extends Templates
{
    protected $_popup;

    public function __construct(
        Context $context,
        Popup $popup
    ) {
        $this->_popup = $popup;
        parent::__construct($context);
    }

    protected function _beforeSave($model, $request)
    {
        $data = $request->getParams();
        $data = $this->_filterPostData($data);
        $model->setData($data);
    }

    protected function _afterSave($model, $request)
    {
        if ($id = $model->getId()) {
            // Clean cache for popups.
            $popups = $this->_popup
                ->getCollection()
                ->addFieldToFilter('template_id', $id);

            foreach ($popups as $popup) {
                $popup->cleanCache();
            }

            $model->generateThumbnail();
        }
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        if (isset($data['entity_id']) && empty($data['entity_id'])) {
            unset($data['entity_id']);
        }

        if (!isset($data['code']) && !empty($data['code_base64'])) {
            $data['code'] = base64_decode($data['code_base64']);
        }
        if (!isset($data['style']) && !empty($data['style_base64'])) {
            $data['style'] = base64_decode($data['style_base64']);
        }

        return $data;
    }
}
