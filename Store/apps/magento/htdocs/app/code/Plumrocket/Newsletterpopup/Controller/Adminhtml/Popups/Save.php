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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\Filter\DateFactory as DateFilterFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\MailchimpList;
use Plumrocket\Newsletterpopup\Model\FormFieldFactory;
use Plumrocket\Newsletterpopup\Model\MailchimpListFactory;

class Save extends Popups
{
    protected $_formFieldFactory;
    protected $_mailchimpListFactory;
    protected $_mailchimpListSource;
    protected $_timezone;
    protected $_dateFilterFactory;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        ResourceConnection $resource,
        FormFieldFactory $formFieldFactory,
        MailchimpListFactory $mailchimpListFactory,
        MailchimpList $mailchimpListSource,
        TimezoneInterface $timezone,
        DateFilterFactory $dateFilterFactory
    ) {
        $this->_formFieldFactory = $formFieldFactory;
        $this->_mailchimpListFactory = $mailchimpListFactory;
        $this->_mailchimpListSource = $mailchimpListSource;
        $this->_timezone = $timezone;
        $this->_dateFilterFactory = $dateFilterFactory;
        parent::__construct($context, $dataHelper, $adminhtmlHelper, $resource);
    }

    protected function _beforeSave($model, $request)
    {
        $data = $request->getParams();
        $data = $this->_filterPostData($data);
        $model->loadPost($data);
    }

    protected function _afterSave($model, $request)
    {
        $model->cleanCache();
        if ($id = $model->getId()) {
            if ($fieldsData = $request->getParam('signup_fields')) {
                $this->_saveFormFields($fieldsData, $id);
            }
            if ($mailchimpData = $request->getParam('mailchimp_list')) {
                $this->_saveMailChimpList($mailchimpData, $id);
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
        if (isset($data['stores'])) {
            if (in_array(0, $data['stores'])) {
                $data['store_id'] = '0';
            } else {
                $data['store_id'] = implode(',', $data['stores']);
            }
        }

        if (isset($data['entity_id']) && empty($data['entity_id'])) {
            unset($data['entity_id']);
        }

        // Prepare dates.
        if (!empty($data['start_date'])) {
            $inputFilter = new \Zend_Filter_Input(
                ['start_date' => $this->_dateFilterFactory->create()],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
        }

        if (!empty($data['end_date'])) {
            $inputFilter = new \Zend_Filter_Input(
                ['end_date' => $this->_dateFilterFactory->create()],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
        }

        /*$dateFormat = $this->_timezone->getDateTimeFormat(\IntlDateFormatter::SHORT);
        if (isset($data['start_date']) && $data['start_date'] != null) {
            $value = new \Zend_Date($data['start_date'], $dateFormat, 'en');
            $data['start_date'] = strftime('%F %T', $value->get());
        }

        if (isset($data['end_date']) && $data['end_date'] != null) {
            $value = new \Zend_Date($data['end_date'], $dateFormat, 'en');
            $data['end_date'] = strftime('%F %T', $value->get());
        }*/

        if (!isset($data['code']) && !empty($data['code_base64'])) {
            $data['code'] = base64_decode($data['code_base64']);
        }
        if (!isset($data['style']) && !empty($data['style_base64'])) {
            $data['style'] = base64_decode($data['style_base64']);
        }

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        if (isset($data['rule']['actions'])) {
            $data['actions'] = $data['rule']['actions'];
        }
        unset($data['rule']);

        return $data;
    }

    protected function _saveFormFields($data, $popupId)
    {
        if (!$popupId) {
            return false;
        }
        // Email is require field
        if (isset($data['email'])) {
            $data['email']['enable'] = 1;
        }
        // If Confirmation is enabled but Password not then enable Password
        if (isset($data['confirm_password'])
            && isset($data['confirm_password']['enable'])
            && isset($data['password'])
            && !isset($data['password']['enable'])
        ) {
            $data['password']['enable'] = 1;
        }

        $systemItemsKeys = $this->_dataHelper->getPopupFormFieldsKeys(0, false);
        $popupItems = $this->_dataHelper->getPopupFormFields($popupId, false);

        foreach ($systemItemsKeys as $name) {
            if (array_key_exists($name, $data)) {
                if (array_key_exists($name, $popupItems)) {
                    $field = $popupItems[$name];
                } else {
                    $field = $this->_formFieldFactory->create();
                    $field->setData('popup_id', $popupId);
                    $field->setData('name', $name);
                }
                $field->setData('label', $data[$name]['label']);
                $field->setData('enable', (int)isset($data[$name]['enable']));
                $field->setData('sort_order', (int)$data[$name]['sort_order']);
                $field->save();
            }
        }
        return true;
    }

    protected function _saveMailChimpList($data, $popupId)
    {
        if (!$this->_adminhtmlHelper->isMaichimpEnabled()) {
            return false;
        }
        $collectionData = $this->_dataHelper->getPopupMailchimpList($popupId, false);
        $mailchimpList = $this->_mailchimpListSource->toOptionHash();

        foreach ($mailchimpList as $key => $name) {
            if (array_key_exists($key, $data)) {
                if (array_key_exists($key, $collectionData)) {
                    $list = $collectionData[$key];
                } else {
                    $list = $this->_mailchimpListFactory->create();
                    $list->setData('popup_id', $popupId);
                    $list->setData('name', $key);
                }
                $list->setData('label', $data[$key]['label']);
                $list->setData('enable', (int)isset($data[$key]['enable']));
                $list->setData('sort_order', (int)$data[$key]['sort_order']);
                $list->save();
            }
        }
        return true;
    }
}
