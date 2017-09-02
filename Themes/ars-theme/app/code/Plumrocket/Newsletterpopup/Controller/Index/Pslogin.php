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

use Plumrocket\Newsletterpopup\Controller\AbstractIndex;

class Pslogin extends AbstractIndex
{
    public function execute()
    {
        $js = [];
        if ($pslogin = $this->getRequest()->getParam('pslogin')) {
            $js[] = 'var newspopupForm = window.opener.jQuery(".newspopup_up_bg_form");';

            if (!empty($pslogin['firstname'])) {
                $js[] = 'newspopupForm.find("input[name=firstname]").val("'. trim($pslogin['firstname']) .'");';
            }

            if (!empty($pslogin['lastname'])) {
                $js[] = 'newspopupForm.find("input[name=lastname]").val("'. trim($pslogin['lastname']) .'");';
            }

            $helper = null;
            if ($this->_dataHelper->moduleExists('SocialLoginPro')) {
                $helper = $this->_objectManager->get('Plumrocket\SocialLoginPro\Helper\Data');
            } elseif ($this->_dataHelper->moduleExists('SocialLoginFree')) {
                $helper = $this->_objectManager->get('Plumrocket\SocialLoginFree\Helper\Data');
            }

            if (!empty($pslogin['email']) && $helper && !$helper->isFakeMail($pslogin['email'])) {
                $js[] = 'newspopupForm.find("input[type=email]").val("'. trim($pslogin['email']) .'");';
            }

            if (!empty($pslogin['dob'])) {
                list($year, $month, $day) = explode('-', $pslogin['dob'], 3);
                if ($year > 0) {
                    $js[] = 'newspopupForm.find("input[name=year]").val("'. $year .'");';
                }
                if ($month > 0) {
                    $js[] = 'newspopupForm.find("input[name=month]").val("'. $month .'");';
                }
                if ($day > 0) {
                    $js[] = 'newspopupForm.find("input[name=day]").val("'. $day .'");';
                }
            }

            if (!empty($pslogin['gender'])) {
                $js[] = 'newspopupForm.find("input[name=gender][value='. $pslogin['gender'] .']").prop("checked", true);';
            }

            if (count($js) > 1) {
                $js[] = 'window.opener.jQuery(".newspopup_up_bg_form:visible").submit();';
            }
        }

        $this->getResponse()->setBody(
            // $this->_view->getLayout()->createBlock('Magento\Framework\View\Element\Template')
            //     ->setJs('if(window.opener && window.opener.location && !window.opener.closed) { '. join(' ', $js) .' window.close(); }')
            //     ->setTemplate('runjs.phtml')
            //     ->toHtml()
            '<html><head></head><body><script type="text/javascript">if(window.opener && window.opener.location && !window.opener.closed) { ' . join(' ', $js) . ' window.close(); }</script></body></html>'
        );
    }
}
