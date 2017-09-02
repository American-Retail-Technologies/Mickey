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

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\Encryptor;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Helper\Data;

class Info extends Field
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;
    protected $_encryptor;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        Encryptor $encryptor,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_encryptor = $encryptor;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        $key = trim($this->_encryptor->decrypt($this->_dataHelper->getConfig($this->_dataHelper->getConfigSectionId() . '/mailchimp/key')));

        if (!$this->_dataHelper->getConfig($this->_dataHelper->getConfigSectionId() . '/mailchimp/enable')) {
            $message = 'Mailchimp Synchronization is disabled.';
        } elseif (!$key) {
            $message = 'Mailchimp API Key is not provided.';
        } else {
            $model = $this->_adminhtmlHelper->getMcapi();
            if ($model) {
                $message = $model->ping();
                if ($message == "Everything's Chimpy!" || $message == '') {
                    $profile = $model->getAccountDetails();

                    if (isset($profile['username']) && $profile['username']) {
                        return sprintf(
                            '<ul class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd; margin-top: 2px;">
                            <li>Username: %s</li>
                            <li>Plan type: %s</li>
                            <li>Is in trial mode?: %s</li>
                            </ul>',
                            $profile['username'],
                            $profile['plan_type'],
                            $profile['is_trial']? 'Yes' : 'No'
                        );
                    } else {
                        $message = 'Mailchimp API Key is not valid.';
                    }
                } else {
                    $message = 'Mailchimp server returned error: ' . $message;
                }
            } else {
                $message = 'Connection failed.';
            }
        }
        return '<div class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd; margin-top: 2px;">' . $message . '</div>';
    }
}
