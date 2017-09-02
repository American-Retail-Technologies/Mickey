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

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Plumrocket\Base\Controller\Adminhtml\Actions;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;

abstract class Popups extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::popups';

    protected $_formSessionKey  = 'prnewsletterpopup_form_data';

    protected $_modelClass      = 'Plumrocket\Newsletterpopup\Model\Popup';
    protected $_activeMenu        = 'Plumrocket_Newsletterpopup::prnewsletterpopup';
    protected $_objectTitle     = 'Popup';
    protected $_objectTitles    = 'Newsletter Popups';

    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_resource;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        ResourceConnection $resource
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    protected function _getModel($load = true)
    {
        parent::_getModel($load);
        if (!$this->_model->getEntityId()) {
            $id = (int)$this->getRequest()->getParam('entity_id');
            if ($id && $load) {
                $this->_model->load($id);
            }
        }
        return $this->_model;
    }

    protected function _delete($id)
    {
        if ($this->_objectManager->create($this->_modelClass)->load($id)->delete()->isDeleted()) {
            $connection = $this->_resource->getConnection('write');

            $connection->query(sprintf(
                "DELETE FROM %s WHERE `popup_id` = '%u'",
                $this->_resource->getTableName('plumrocket_newsletterpopup_history'),
                $id
            ));

            $connection->query(sprintf(
                "DELETE FROM %s WHERE `popup_id` = '%u'",
                $this->_resource->getTableName('plumrocket_newsletterpopup_mailchimp_list'),
                $id
            ));

            $connection->query(sprintf(
                "DELETE FROM %s WHERE `popup_id` = '%u'",
                $this->_resource->getTableName('plumrocket_newsletterpopup_form_fields'),
                $id
            ));

            $connection->query(sprintf(
                "DELETE FROM %s WHERE `popup_id` = '%u'",
                $this->_resource->getTableName('plumrocket_newsletterpopup_hold'),
                $id
            ));

            return true;
        }
    }

    protected function _duplicate($id)
    {
        $orig = $this->_objectManager->create($this->_modelClass)->load($id);
        if ($orig->getId()) {
            $clone = clone $orig;

            $cloneData = $clone->getData();
            $cloneData['status'] = 0;
            $cloneData['name'] .= __(' (duplicate)');
            $cloneData['views_count'] = 0;
            $cloneData['subscribers_count'] = 0;
            $cloneData['orders_count'] = 0;
            $cloneData['total_revenue'] = 0;
            unset($cloneData['entity_id']);

            $clone->setData($cloneData);
            $clone->save();

            $oldId = $id;
            $id = $clone->getId();

            $dublicatedTable = ['plumrocket_newsletterpopup_form_fields', 'plumrocket_newsletterpopup_mailchimp_list'];
            $connection = $this->_resource->getConnection('write');
            foreach ($dublicatedTable as $tableName) {
                $tableName = $this->_resource->getTableName($tableName);
                $connection->query(sprintf(
                    "INSERT INTO %s (`popup_id`, `name`, `label`, `enable`, `sort_order`)
                        SELECT %u, `name`, `label`, `enable`, `sort_order`
                        FROM %s
                        WHERE `popup_id` = %u;",
                    $tableName,
                    $id,
                    $tableName,
                    $oldId
                ));
            }
        }
        return $id;
    }
}
