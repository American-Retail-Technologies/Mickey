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

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Plumrocket\Newsletterpopup\Controller\AbstractIndex;
use Plumrocket\Newsletterpopup\Helper\Data;

class Subscribe extends AbstractIndex
{
    public function execute()
    {
        try {
            if (!$this->_dataHelper->moduleEnabled()) {
                throw new ValidatorException(__('The Plumrocket Newsletter Popup Module is disabled.'));
            }

            $email = $this->getRequest()->getParam('email');
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                throw new ValidatorException(__('Please enter a valid email address.'));
            }

            if ($this->_dataHelper->getConfig(Data::SECTION_ID . '/disposable_emails/disable')) {
                $_email = preg_replace('#[[:space:]]#', '', $email);
                preg_match('#@([\w-.]+$)#is', $_email, $domain);
                if (!empty($domain[1])) {
                    preg_match('#(?:^|[\s,]+)'. preg_quote($domain[1]) . '(?:$|[\s,]+)#i', $this->_dataHelper->getConfig(Data::SECTION_ID . '/disposable_emails/domains'), $math);
                    if (!empty($math)) {
                        throw new ValidatorException(__('This email address provider is blocked. Please try again with different email address.'));
                    }
                }
            }

            $subscriber = $this->_subscriber->load($email, 'subscriber_email');
            if ((int)$subscriber->getId() !== 0) {
                throw new ValidatorException(__('This email address is already assigned to another user.'));
            }

            $inputData = $this->getRequest()->getParams();
            // Prepare DOB.
            if (empty($inputData['dob']) && !empty($inputData['month']) && !empty($inputData['day']) && !empty($inputData['year'])) {
                $dateMapping = $this->_view
                    ->getLayout()
                    ->createBlock('Plumrocket\Newsletterpopup\Block\Popup\Fields\Dob')
                    ->getDateMapping(false);
                $inputData['dob'] = sprintf($dateMapping, (int)$inputData['month'], (int)$inputData['day'], (int)$inputData['year']);
            }

            $subscriber->customSubscribe($email, $this, $inputData);
        } catch (ValidatorException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            // $this->messageManager->addError($e->getMessage());
            $this->messageManager->addError(__('Unknown Error'));
        }

        $data = ['error' => 0, 'messages' => []];

        $messages = $this->messageManager->getMessages(true);
        foreach ($messages->getItems() as $message) {
            if ($message->getType() != MessageInterface::TYPE_SUCCESS) {
                $data['error'] = 1;
            }
            if (!array_key_exists($message->getType(), $data['messages'])) {
                $data['messages'][$message->getType()] = [];
            }
            $data['messages'][$message->getType()][] = $message->getText();
        }

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->clearHeader('Location')
            // ->clearRawHeader('Location')
            ->setHttpResponseCode(200)
            ->setBody(json_encode($data));
    }
}
