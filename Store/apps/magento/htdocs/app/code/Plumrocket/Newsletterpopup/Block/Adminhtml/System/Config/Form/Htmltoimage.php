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
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Htmltoimage extends Field
{
    protected $_adminhtmlHelper;

    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        if ($this->_adminhtmlHelper->checkIfHtmlToImageInstalled()) {
            $message = 'Thumbnail Generation is Enabled.';
        } else {
            $message = 'Thumbnail Generation is <span style="color: #eb5e00;">Disabled</span>.
            In order for popup thumbnail to appear, please install the wkhtmltoimage command line tool
            to render HTML into image. See "installation" chapter of
            <a href="http://wiki.plumrocket.com/wiki/Magento_2_Newsletter_Popup_v3.x_Installation#Newsletter_Popup_Thumbnail_Generation" target="_blank">our online documentation</a> for more info.
            <br /><br />
            <span id="wkhtmltoimage_status">
                <button style="" onclick="htmltoimageSubmitRequest()" class="scalable"
                type="button" title="Find Wkhtmltoimage Tool">
                    <span>
                        <span>
                            <span>Find Wkhtmltoimage Tool</span>
                        </span>
                    </span>
                </button>
            </span>
            <script type="text/javascript">
            function htmltoimageSubmitRequest()
            {
                new Ajax.Request("' . $this->getUrl('prnewsletterpopup/config/refresh') . '", {
                    method: "get",
                    onSuccess: function successFunc(response) {
                        if (200 == response.status){
                            var json = response.responseText.evalJSON();
                            if (json) {
                                var tp = (json.error)? "error": "success";
                                var text = "<div id=\"messages\"><div class=\"message message-" + tp + " " + tp + "\"><div>" + json.message + "</div></div></div>";
                                $("wkhtmltoimage_status").update(text);
                            }
                        }
                    },
                    onFailure:  function () {}
                });
            }
            </script>';
        }
        return '<div class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd;">' . $message . '</div>';
    }
}
