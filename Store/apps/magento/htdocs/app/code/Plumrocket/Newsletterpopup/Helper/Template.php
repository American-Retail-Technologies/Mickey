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
 * @package   Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license   http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Config\Model\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Model\PopupFactory;
use Plumrocket\Newsletterpopup\Model\TemplateFactory;

class Template extends Main
{
    /**
     * @var DateTime
     */
    protected $dataTime;

    /**
     * Template constructor.
     *
     * @param ObjectManagerInterface    $objectManager
     * @param Context                   $context
     * @param DateTime                  $dateTime
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        DateTime $dateTime
    ) {
        $this->dataTime = $dateTime;
        parent::__construct($objectManager, $context);
    }

    /**
       * Get all data
       *
       * @param  null|int $templateId
       * @return array
       */
    public function getAllData($templateId = null)
    {
        $rows = $this->getValues($templateId);
        foreach ($rows as $id => $row) {
            $rows[$id] = array_merge(
                $rows[$id],
                $this->getCodeStyle($id)
            );
        }

        return $rows;
    }

  /**
   * Get template values
   *
   * @param  null|int $templateId
   * @return array
   */
    public function getValues($templateId = null)
    {
        // $currentTime = $this->_dateTime->date('Y-m-d H:i:s');
        $currentTime = $this->dataTime->date('Y-m-d H:i:s');

        $rows = [
        1   => [1, -1, '1. Minimalist', $currentTime, null, 'a:7:{s:10:"text_title";s:26:"Sign Up For Our Newsletter";s:16:"text_description";s:92:"<p>Want to be the first to hear latest news and find out about our exclusive promotions.</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:9:"Subscribe";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:5:"Email";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        2   => [2, -1, '2. Emerald', $currentTime, null, "a:7:{s:10:\"text_title\";s:46:\"Sign up for our emails and get a warm welcome:\";s:16:\"text_description\";s:91:\"<div class=\"logo-title\">25% off</div>\r\n<div class=\"logo-subtitle\">regular price style</div>\";s:12:\"text_success\";s:39:\"<p>Thank you for your subscription.</p>\";s:11:\"text_submit\";s:6:\"Submit\";s:11:\"text_cancel\";s:9:\"No Thanks\";s:9:\"animation\";s:13:\"fadeInDownBig\";s:13:\"signup_fields\";a:2:{s:5:\"email\";a:5:{s:4:\"name\";s:5:\"email\";s:5:\"label\";s:5:\"Email\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"10\";s:10:\"orig_label\";s:5:\"Email\";}s:6:\"gender\";a:5:{s:4:\"name\";s:6:\"gender\";s:5:\"label\";s:6:\"Gender\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"80\";s:10:\"orig_label\";s:6:\"Gender\";}}}"],
        3   => [3, -1, '3. Red Wine', $currentTime, null, 'a:7:{s:10:"text_title";s:6:"&nbsp;";s:16:"text_description";N;s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:6:"Get it";s:11:"text_cancel";s:10:"No, Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:2:{s:9:"firstname";a:5:{s:4:"name";s:9:"firstname";s:5:"label";s:10:"First Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:10:"First Name";}s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:5:"Email";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"20";s:10:"orig_label";s:5:"Email";}}}'],
        4   => [4, -1, '4. Strict Fashion', $currentTime, null, "a:7:{s:10:\"text_title\";s:5:\"Hello\";s:16:\"text_description\";s:180:\"<h2>Subscribe to our newsletter</h2>\r\n<div class=\"newspopup-text\">and you&rsquo;ll be the first to know about our newest arrivals, special offers and store events near you...</div>\";s:12:\"text_success\";s:39:\"<p>Thank you for your subscription.</p>\";s:11:\"text_submit\";s:6:\"Submit\";s:11:\"text_cancel\";s:9:\"No Thanks\";s:9:\"animation\";s:13:\"fadeInDownBig\";s:13:\"signup_fields\";a:2:{s:5:\"email\";a:5:{s:4:\"name\";s:5:\"email\";s:5:\"label\";s:5:\"Email\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"10\";s:10:\"orig_label\";s:5:\"Email\";}s:6:\"gender\";a:5:{s:4:\"name\";s:6:\"gender\";s:5:\"label\";s:6:\"Gender\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"80\";s:10:\"orig_label\";s:6:\"Gender\";}}}"],
        5   => [5, -1, '5. Summertime', $currentTime, null, 'a:7:{s:10:"text_title";s:38:"Join our email list today and  receive";s:16:"text_description";N;s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:9:"Subscribe";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:3:{s:9:"firstname";a:5:{s:4:"name";s:9:"firstname";s:5:"label";s:10:"First Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:1:"5";s:10:"orig_label";s:10:"First Name";}s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}s:3:"dob";a:5:{s:4:"name";s:3:"dob";s:5:"label";s:13:"Date of Birth";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"70";s:10:"orig_label";s:13:"Date of Birth";}}}'],
        6   => [6, -1, '6. Glamour Blue', $currentTime, null, "a:7:{s:10:\"text_title\";s:65:\"Be the first to hear about new arrivals, exclusive savings & more\";s:16:\"text_description\";s:135:\"<p>plus, sign up for our emails and get a warm welcome:</p>\r\n<div class=\"price-off\"><strong>25% off</strong> regular price styles</div>\";s:12:\"text_success\";s:39:\"<p>Thank you for your subscription.</p>\";s:11:\"text_submit\";s:6:\"Submit\";s:11:\"text_cancel\";s:10:\"No, Thanks\";s:9:\"animation\";s:13:\"fadeInDownBig\";s:13:\"signup_fields\";a:2:{s:5:\"email\";a:5:{s:4:\"name\";s:5:\"email\";s:5:\"label\";s:18:\"Your Email Address\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"10\";s:10:\"orig_label\";s:5:\"Email\";}s:13:\"confirm_email\";a:5:{s:4:\"name\";s:13:\"confirm_email\";s:5:\"label\";s:26:\"Confirm Your Email Address\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"20\";s:10:\"orig_label\";s:13:\"Confirm Email\";}}}"],
        7   => [7, -1, '7. Pink Style', $currentTime, null, 'a:7:{s:10:"text_title";s:28:"Don’t be the last to know!";s:16:"text_description";s:82:"<p>Sign up to recieve the hottest arrivals, latest trends and exclusive offers</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:7:"Sign up";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        8   => [8, -1, '8. Nightlife', $currentTime, null, 'a:7:{s:10:"text_title";s:15:"Join the Party!";s:16:"text_description";s:80:"<p>Sign up for exclusive updates, new arrivals, events, contests, and more !</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:5:"Join!";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        9   => [9, -1, '9. Golden Black', $currentTime, null, 'a:7:{s:10:"text_title";s:56:"On your next online  order when you join  our email list";s:16:"text_description";s:80:"<p>Plus, be the first to hear about new products, exclusive offers and more.</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:6:"Submit";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:2:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}s:10:"country_id";a:5:{s:4:"name";s:10:"country_id";s:5:"label";s:7:"Country";s:6:"enable";s:1:"1";s:10:"sort_order";s:3:"180";s:10:"orig_label";s:7:"Country";}}}'],
        10  => [10, -1, '10. Adventure', $currentTime, null, 'a:7:{s:10:"text_title";s:16:"Never miss a bit";s:16:"text_description";s:71:"<p>sign up now and<br /><strong>get $15 off</strong> your purchase!</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:9:"Subscribe";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:3:{s:9:"firstname";a:5:{s:4:"name";s:9:"firstname";s:5:"label";s:10:"First Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:1:"5";s:10:"orig_label";s:10:"First Name";}s:8:"lastname";a:5:{s:4:"name";s:8:"lastname";s:5:"label";s:9:"Last Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:1:"6";s:10:"orig_label";s:9:"Last Name";}s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        11  => [11, -1, '11. Golden Diamond', $currentTime, null, 'a:7:{s:10:"text_title";s:51:"plus, sign up for our emails and get a warm welcome";s:16:"text_description";s:76:"<p>Be the first to hear about new arrivals, exclusive savings &amp; more</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:6:"Submit";s:11:"text_cancel";s:10:"No, Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:2:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}s:13:"confirm_email";a:5:{s:4:"name";s:13:"confirm_email";s:5:"label";s:26:"Confirm Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"20";s:10:"orig_label";s:13:"Confirm Email";}}}'],
        12  => [12, -1, '12. Fireworks', $currentTime, null, 'a:7:{s:10:"text_title";s:18:"Sign Up & Save 10%";s:16:"text_description";s:102:"<p>Join our email list &amp; receive a coupon for an extra 10% off + other great exclusive offers!</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:21:"Sign Up & Get  Coupon";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        13  => [13, -1, '13. Sticky Footer Bar', $currentTime, null, 'a:7:{s:10:"text_title";s:9:"You first";s:16:"text_description";s:76:"<p>Sign up for emails to get our latest style news before everybody else</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:6:"Submit";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:11:"fadeInUpBig";s:13:"signup_fields";a:2:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}s:9:"firstname";a:5:{s:4:"name";s:9:"firstname";s:5:"label";s:10:"First Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"30";s:10:"orig_label";s:10:"First Name";}}}'],
        14  => [14, -1, '14. Sticky Footer Window', $currentTime, null, 'a:7:{s:10:"text_title";s:27:"Don’t be the last to know";s:16:"text_description";s:82:"<p>Sign up to recieve the hottest arrivals, latest trends and exclusive offers</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:6:"Submit";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:11:"fadeInUpBig";s:13:"signup_fields";a:2:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}s:6:"gender";a:5:{s:4:"name";s:6:"gender";s:5:"label";s:6:"Gender";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"80";s:10:"orig_label";s:6:"Gender";}}}'],
        15  => [15, -1, '15. Giant', $currentTime, null, 'a:7:{s:10:"text_title";s:27:"Don’t be the last to know";s:16:"text_description";s:82:"<p>Sign up to recieve the hottest arrivals, latest trends and exclusive offers</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:9:"Subscribe";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:3:{s:9:"firstname";a:5:{s:4:"name";s:9:"firstname";s:5:"label";s:10:"First Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:1:"5";s:10:"orig_label";s:10:"First Name";}s:8:"lastname";a:5:{s:4:"name";s:8:"lastname";s:5:"label";s:9:"Last Name";s:6:"enable";s:1:"1";s:10:"sort_order";s:1:"8";s:10:"orig_label";s:9:"Last Name";}s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        16  => [16, -1, '16. Right Slide Out', $currentTime, null, "a:7:{s:10:\"text_title\";s:15:\"Best deal ever!\";s:16:\"text_description\";s:148:\"<div class=\"line-1\">Free shipping</div>\r\n<div class=\"line-2\">On orders of $21+</div>\r\n<div class=\"line-3\">sign up &amp; reveal your offers now</div>\";s:12:\"text_success\";s:39:\"<p>Thank you for your subscription.</p>\";s:11:\"text_submit\";s:6:\"Submit\";s:11:\"text_cancel\";s:9:\"No Thanks\";s:9:\"animation\";s:14:\"fadeInRightBig\";s:13:\"signup_fields\";a:1:{s:5:\"email\";a:5:{s:4:\"name\";s:5:\"email\";s:5:\"label\";s:18:\"Your Email Address\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"10\";s:10:\"orig_label\";s:5:\"Email\";}}}"],
        17  => [17, -1, '17. Hidden Treasure', $currentTime, null, 'a:7:{s:10:"text_title";s:20:"Sign Up and Save 10%";s:16:"text_description";s:102:"<p>Join our email list &amp; receive a coupon for an extra 10% off + other great exclusive offers!</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:21:"Sign Up & Get  Coupon";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        18  => [18, -1, '18. White Lotus', $currentTime, null, 'a:7:{s:10:"text_title";s:27:"Sign Up for our  Newsletter";s:16:"text_description";N;s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:4:"Send";s:11:"text_cancel";s:9:"No Thanks";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:18:"Your Email Address";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        19  => [19, -1, '19. Chocolate', $currentTime, null, "a:7:{s:10:\"text_title\";s:33:\"Join our newsletter instantly get\";s:16:\"text_description\";s:93:\"<p><span>20</span><span class=\"sup\">%<span class=\"sub\">OFF</span></span></p>\r\n<p>any item</p>\";s:12:\"text_success\";s:39:\"<p>Thank you for your subscription.</p>\";s:11:\"text_submit\";s:6:\"&nbsp;\";s:11:\"text_cancel\";s:9:\"No Thanks\";s:9:\"animation\";s:13:\"fadeInDownBig\";s:13:\"signup_fields\";a:1:{s:5:\"email\";a:5:{s:4:\"name\";s:5:\"email\";s:5:\"label\";s:18:\"Your Email Address\";s:6:\"enable\";s:1:\"1\";s:10:\"sort_order\";s:2:\"10\";s:10:\"orig_label\";s:5:\"Email\";}}}"],
        20  => [20, -1, '20. Default Theme', $currentTime, null, 'a:7:{s:10:"text_title";s:28:"GET $10 OFF YOUR FIRST ORDER";s:16:"text_description";s:105:"<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>";s:12:"text_success";s:39:"<p>Thank you for your subscription.</p>";s:11:"text_submit";s:11:"Sign Up Now";s:11:"text_cancel";s:4:"Hide";s:9:"animation";s:13:"fadeInDownBig";s:13:"signup_fields";a:1:{s:5:"email";a:5:{s:4:"name";s:5:"email";s:5:"label";s:5:"Email";s:6:"enable";s:1:"1";s:10:"sort_order";s:2:"10";s:10:"orig_label";s:5:"Email";}}}'],
        ];

        foreach ($rows as $n => $row) {
            $rows[$n] = array_combine([
            'entity_id',
            'base_template_id',
            'name',
            'created_at',
            'updated_at',
            'default_values'
            ], $row);
        }

        if ($templateId) {
            return isset($rows[$templateId]) ? $rows[$templateId] : null;
        }

        return $rows;
    }

  /**
   * Get code and style of templates
   *
   * @param  null|int $templateId
   * @return array
   */
    public function getCodeStyle($templateId = null)
    {
        $rows = [];

        $rows[1]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="small_text">
  {{text_description}}
</div>

<div class="prnp-title">{{text_title}}</div>

<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}
  <div>
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
  <div class="pl-clearfix"></div>
  {{mailchimp_fields}}
</form>
HTML;
        $rows[1]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);

.newspopup_up_bg .validation-failed {
  border: 1px solid red!important;
}

.newspopup_up_bg div.mage-error {margin-bottom: 0;}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 1 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  display: block;
  width: 570px;
  margin: 10% auto 5% auto;
  position: relative;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #fff;
  font-size: 18px;
  color: #4c5669;
  text-align: center;
  padding: 20px 50px;
  position: relative;
  border: 1px solid #aaaaaa;
  -webkit-box-shadow: 12px 10px 40px 0 #414141;
  -moz-box-shadow: 12px 10px 40px 0 #414141;
  box-shadow: 12px 10px 40px 0 #414141;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #808795;
  top: 8px;
  right: 8px;
  position: absolute;
  font-weight: 700;
  font-size: 16px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #353B3E;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 37px;
  margin-right: -57px;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 16px;
  font-weight: 700;
  color: #4c5669;
  margin: 30px 0;
  font-family: 'Open Sans', Arial, sans-serif;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme h3 {clear: both; font-size: 14px;}

.newspopup-up-form.newspopup-theme .mailchimp_item {font-size: 13px;}

.newspopup-up-form.newspopup-theme .small_text {
  line-height: 22px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 340px;
  height: 35px;
  line-height: 35px;
  font-size: 12px;
  color: #525252;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #dce1ec;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 35px;
  font-size: 12px;
  color: #525252;
  border: 1px solid #dce1ec;
  outline: none;
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 340px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .pl-clearfix + h3 {
  text-align: left;
  font-size: 15px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #888888;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #888888;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #888888;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #888888;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #888888;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #888888;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  float: left;
  width: 340px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error ,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error ,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error  {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  float: right;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item+div:not(.mailchimp_item) {
  float: none;
  position: relative;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #363c3f;
  color: #fff;
  font-size: 11px;
  height: 35px;
  width: 115px;
  text-align: center;
  line-height: 33px;
  display: inline-block;
  border-radius: 2px;
  margin-left: 10px;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #555B5D;
}

@media screen and (max-width: 800px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error  {
    position: static !important;
    background: none;
    color: red !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
    float: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    float: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 100%;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    font-size: 16px;
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px;
  }

  .newspopup-up-form.newspopup-theme .small_text {
    line-height: 19px;
    padding: 0 10px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    margin: 20px 0;
  }
}

/*==================== end Theme 1 ====================*/
CSS;
        $rows[2]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="prnp-title">{{text_title}}</div>

<div class="newspopup-logo">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}
  <div style="position: relative;">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
</form>

<div class="privacy-policy">
  <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank">Privacy Policy</a>
</div>
HTML;
        $rows[2]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);

.newspopup_up_bg .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup_up_bg div.mage-error {
  bottom: 5px;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 2 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: relative;
  display: block;
  width: 350px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #f6f6f6;
  -webkit-box-shadow: 12px 10px 40px 0 #414141;
  -moz-box-shadow: 12px 10px 40px 0 #414141;
  box-shadow: 12px 10px 40px 0 #414141;
  font-size: 18px;
  color: #4c5669;
  text-align: center;
  padding: 25px 0 0 0;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  background-color: #d9d9d9;
  color: #fff;
  top: 0;
  right: 0;
  position: absolute;
  font-weight: 700;
  font-size: 16px;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  width: 35px;
  height: 35px;
  text-shadow: 1px 1px 0px #a9a9a9;
  line-height: 35px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  background-color: #CFCFCF;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 18px;
  font-weight: 700;
  color: #5f565d;
  margin: 0 0 20px 0;
  font-family: 'Open Sans', Arial, sans-serif;
  padding: 0 40px;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  min-height: 105px;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme2_logo.jpg"}}");
  color: #fff;
  text-align: center;
  padding-top: 20px;
  padding-bottom: 20px;
}

.newspopup-up-form.newspopup-theme .newspopup-logo .logo-title {
  font-size: 41px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-weight: 700;
  color: #fff;
  margin: 0;
  padding: 0;
  line-height: 41px;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .newspopup-logo .logo-subtitle {
  font-size: 16px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-weight: 700;
  color: #fff;
  margin: 0;
  padding: 0;
  line-height: 20px;
  margin-top: 5px;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 45px;
  margin-top: 20px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 45px;
  line-height: 45px;
  font-size: 14px;
  color: #393939;
  padding: 0 10px;
  outline: none;
  border: 1px solid #b1b1b1;
  text-align: center;
  border-radius: 4px;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding: 0 0 0 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 45px;
  font-size: 14px;
  color: #888888;
  border: 1px solid #b1b1b1;
  outline: none;
  width: 100%;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #a4a4a4;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #a4a4a4;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #a4a4a4;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #a4a4a4;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #a4a4a4;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #a4a4a4;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-full:before {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li.customer-dob div.mage-error {
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  position: relative;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #0ba857;
  color: #fff;
  font-size: 16px;
  height: 45px;
  line-height: 45px;
  width: 100%;
  text-align: center;
  display: inline-block;
  border-radius: 5px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  outline: none;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #0B904C;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 48px;
  margin-right: -52px;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  background-color: #e8e8e8;
  text-align: center;
  height: 40px;
  line-height: 40px;
  margin-top: 18px;
}

.newspopup-up-form.newspopup-theme .privacy-policy a {
  text-decoration: underline;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10px;
  color: #797979;
  display: inline;
  vertical-align: top;
}

.newspopup-up-form.newspopup-theme .privacy-policy a:hover {
  text-decoration: none;
}

@media screen and (max-width: 640px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: red !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
    text-align: center;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 35px 0 0 0;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    margin: 0 5px 15px 5px;
    padding: 0 15px;
    font-size: 16px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0 25px;
    margin-top: 10px;
  }
}

@media screen and (max-width: 400px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }
}
/*==================== end Theme 2 ====================*/
CSS;
        $rows[3]['code'] = <<<HTML
<div class="newspopup-logo">
  {{text_description}}
  <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme3_logo.png"}}" />
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div style="position:relative;">
    {{form_fields}}

    <div style="position:relative;">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </div>

  <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank" class="privacy-policy">Privacy Policy</a>
</form>

<div class="cross close" title="{{text_cancel}}">{{text_cancel}}</div>
HTML;
        $rows[3]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);

.newspopup_up_bg .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup_up_bg div.mage-error {
  bottom: 3px;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -52px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup_up_bg {
  padding: 0 5%;
}

/*==================== Theme 3 ====================*/
.newspopup-up-form.newspopup-theme {
  position: relative;
}

.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  display: block;
  width: 350px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #fbfbfb;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme3_bg.jpg"}}");
  font-size: 18px;
  color: #454545;
  text-align: center;
  padding: 0px;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  padding-bottom: 10px;
}

.newspopup-up-form.newspopup-theme .cross {
  background: #e8e8e8;
  color: #726b6d;
  position: static;
  font-weight: 400;
  font-size: 8px;
  font-family: Times, Arial, Helvetica, sans-serif;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  height: 25px;
  line-height: 25px;
  text-align: center;
  margin: 0 10px;
  width: auto;
  border-radius: 2px;
  -moz-border-radius: 2px;
  -webkit-border-radius: 2px;
  text-decoration: underline;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  background: #DFDFDF;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  color: #000;
  text-align: center;
  position: relative;
  width: 100%;
  background-repeat: no-repeat;
  padding-top: 15px;
}

.newspopup-up-form.newspopup-theme .newspopup-logo img {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 40px 15px 40px;
  margin-top: 25px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 40px;
  line-height: 40px;
  font-size: 12px;
  color: #454545;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #e1e1e1;
  -webkit-appearance: none;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #A2A2A2;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 40px;
  font-size: 12px;
  color: #454545;
  border: 1px solid #e1e1e1;
  outline: none;
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme input[placeholder]      {color:#454545;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder   {color:#454545; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder    {color:#454545; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color:#454545;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder{color:#454545; opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color:#454545;}


.newspopup-up-form.newspopup-theme .send {
  border: 1px solid #d7d7d7;
  background-color: #F54646;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #F54646),color-stop(1, #DB1F1F));
  background-image: -o-linear-gradient(bottom, #F54646 0%, #DB1F1F 100%);
  background-image: -moz-linear-gradient(bottom, #F54646 0%, #DB1F1F 100%);
  background-image: -webkit-linear-gradient(bottom, #F54646 0%, #DB1F1F 100%);
  background-image: -ms-linear-gradient(bottom, #F54646 0%, #DB1F1F 100%);
  background-image: linear-gradient(to bottom, #F54646 0%, #DB1F1F 100%);
  color: #fff;
  font-size: 15px;
  height: 40px;
  width: 100%;
  text-align: center;
  line-height: 39px;
  display: inline-block;
  border-radius: 5px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  margin-bottom: 5px;
  outline: none;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  opacity: 0.9;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  color: #000;
  font-size: 8px;
  text-decoration: underline;
  font-family: Times, Arial, Helvetica, sans-serif;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .privacy-policy:hover {
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 40px;
}


@media screen and (max-width: 640px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme {
    margin: 5% auto;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static!important;
    background: none;
    color: red!important;
    font-weight: 400;
    box-shadow: none;
    padding: 0!important;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.theme-3 {
    margin: 5% auto;
  }
}


@media screen and (max-width: 420px) {

  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0 20px 15px 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo h3 {
    top: 116px;
    right: auto
    text-align: center;
    width: 100%;
  }
}

@media screen and (max-width: 320px) {
  .newspopup-up-form.newspopup-theme .newspopup-logo h2 {
    font-size: 22px;
  }
}
/*==================== end Theme 3 ====================*/
CSS;
        $rows[4]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="newspopup-left-col">
  <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme4_bg.jpg"}}" />
</div>

<div class="newspopup-right-col">
  <div class="newspopup-right-col-wrapper">
    <div class="prnp-title">{{text_title}}</div>

    <div class="newspopup-logo">
      {{text_description}}
    </div>

    <form class="newspopup_up_bg_form" method="POST">
      {{form_fields}}
      <div>
        <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
          {{text_submit}}
        </a>
        <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
      </div>
    </form>
  </div>
</div>
HTML;
        $rows[4]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Quicksand:400,700);

.newspopup_up_bg .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -54px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 4 ====================*/
.newspopup-up-form.newspopup-theme {
  position: relative;
}

.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  display: table;
  width: 690px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme:before,
.newspopup-up-form.newspopup-theme:after {
  display: block;
  content: "";
  clear: both;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #726f69;
  text-align: center;
  padding: 0px;
  display: block;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col,
.newspopup-up-form.newspopup-theme .newspopup-right-col {
  display: table-cell;
  width: 50%;
  background-color: #f6f6f6;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme4_bg.jpg"}}");
  -webkit-background-size: cover;
  -moz-background-size: cover;
  background-size: cover;
  background-position: 25% 50%;
  width: 55%;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col img {
  display: none;
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col {
  padding: 11px;
  width: 50%;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col .newspopup-right-col-wrapper {
  border: 1px solid #c1bfbb;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #7e7e7e;
  top: 5px;
  right: 7px;
  position: absolute;
  font-weight: 700;
  font-size: 13px;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  width: 21px;
  height: 22px;
  line-height: 23px;
  background-color: #f6f6f6;
}

.newspopup-up-form.newspopup-theme .cross:after {
  border-left: 1px solid #c1bfbb;
  border-bottom: 1px solid #c1bfbb;
  width: 17px;
  height: 16px;
  display: block;
  content: "";
  position: absolute;
  top: 6px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #585858;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 36px;
  font-weight: 700;
  color: #3e3a3a;
  margin: 20px 0 20px 0;
  font-family: 'Quicksand', Arial, sans-serif;
  padding: 0 45px;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  padding: 0 25px;
}

.newspopup-up-form.newspopup-theme .newspopup-logo h2 {
  font-size: 24px;
  font-family: 'Open Sans', Arial, sans-serif;
  text-transform: none;
  font-weight: 400;
  color: #796c5b;
  margin: 0;
  padding: 0;
  line-height: 41px;
  border-top: 1px solid #d4d1cc;
  border-bottom: 1px solid #d4d1cc;
  line-height: 26px;
  padding: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup-text {
  padding: 0;
  margin-top: 25px;
  line-height: 19px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 25px 25px 25px;
  margin-top: 20px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 45px;
  line-height: 45px;
  font-size: 14px;
  color: #22211E;
  padding: 0;
  outline: none;
  border: 1px solid #e2e2e0;
  text-align: center;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  -webkit-appearance: none;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #CDCDCD;
  padding: 0px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 45px;
  font-size: 14px;
  color: #22211E;
  border: 1px solid #e2e2e0;
  outline: none;
  width: 100%;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme input[placeholder]      {color:#726f69;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder   {color:#726f69; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder    {color:#726f69; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color:#726f69;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder{color:#726f69; opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color:#726f69;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-full:before {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  position: relative;
}

.newspopup-up-form.newspopup-theme div.mage-error {
  margin: 5px 0px 5px 0;
  text-align: left;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  content: "";
  display: block;
  position: absolute;
  right: -8px;
  bottom: 8px;
  content: " ";
  width: 0px;
  height: 0px;
  border-top: 8px solid transparent;
  border-bottom: 8px solid transparent;
  border-left: 8px solid #BA0000;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li.customer-dob div.mage-error {
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #796c5b;
  border: 1px solid #5b5110;
  color: #fff;
  font-size: 14px;
  height: 42px;
  line-height: 42px;
  width: 100%;
  text-align: center;
  display: inline-block;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  -webkit-box-shadow: inset 0 2px 0 0 #968c7f, 0 2px 1px 0 #ded7d3;
  -moz-box-shadow: inset 0 2px 0 0 #968c7f, 0 2px 1px 0 #ded7d3;
  box-shadow: inset 0 2px 0 0 #968c7f, 0 2px 1px 0 #ded7d3;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #6F6455;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 49px;
  right: 50%;
}

@media screen and (max-width: 767px) {
  .newspopup_up_bg {
    padding: 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
    max-width: 500px;
  }

  .newspopup-up-form.newspopup-theme .cross {
    width: 25px;
    height: 25px;
    line-height: 25px;
    top: 10px;
    right: 10px;
  }

  .newspopup-up-form.newspopup-theme .cross:after {
    display: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col,
  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    width: 100%;
    display: block;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col img {
    display: block;
  }

  .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: red !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
    text-align: left;
    margin: 5px 0 0 0;
  }
  .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 30px;
    margin: 15px 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo h2 {
    font-size: 20px;
    line-height: 22px;
  }
}


@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    margin: 0 auto 5% auto;
  }
}

/*==================== end Theme 4 ====================*/
CSS;
        $rows[5]['code'] = <<<HTML
<div class="newspopup-left-col">
  <div class="newspopup-left-col-wrapper">
    <div class="cross close" title="{{text_cancel}}">&#10005;</div>
    <div class="prnp-title">{{text_title}}</div>
    <div class="newspopup-logo">
      {{text_description}}
    </div>
    <a class="privacy-policy" href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank">Privacy Policy</a>
  </div>
</div>
<div class="newspopup-right-col">
  <div class="cross close" title="{{text_cancel}}">&#10005;</div>

  <div class="newspopup-right-col-wrapper">
    <form class="newspopup_up_bg_form" method="POST">
      {{form_fields}}

      <div style="position: relative;">
        <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
          {{text_submit}}
        </a>
        <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
      </div>
    </form>
  </div>
</div>
HTML;
        $rows[5]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Quicksand:300,400);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}


/*==================== Theme 5 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: relative;
  width: 855px;
  margin: 10% auto 5% auto;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #726f69;
  text-align: center;
  padding: 0px;
  padding-right: 30px;
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme:before,
.newspopup-up-form.newspopup-theme:after {
  display: block;
  content: "";
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col,
.newspopup-up-form.newspopup-theme .newspopup-right-col {
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme5_bg.png"}}");
  height: 333px;
  width: 542px;
  position: relative;
  z-index: 100;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col img {
  display: none;
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col a.privacy-policy {
  color: #2a4450;
  text-decoration: underline;
  font-size: 11px;
  position: absolute;
  bottom: 35px;
  right: 75px;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col a.privacy-policy:hover {
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col {
  background-color: #fff;
  width: 310px;
  margin-top: 70px;
  margin-left: -29px;
  padding: 30px 40px 25px 50px;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col .cross {
  display: none;
  top: 35px;
  right: 20px;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #d7d7d7;
  top: 8px;
  right: 8px;
  position: absolute;
  font-weight: 700;
  font-size: 11px;
  border-radius: 50%;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  border: 2px solid #d7d7d7;
  width: 18px;
  height: 18px;
  padding: 0;
  margin: 0;
  line-height: 14px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #BEBEBE;
  border: 2px solid #BEBEBE;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 40px;
  margin-right: -47px;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 24px;
  font-weight: 400;
  color: #fff;
  font-family: "Quicksand", Arial, Helvetica, sans-serif;
  float: right;
  width: 214px;
  text-align: right;
  margin: 0;
  position: absolute;
  right: 33px;
  top: 60px;
  letter-spacing: 0px;
  text-transform: uppercase;
  line-height: 27px;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme5_subtitle.png"}}");
  background-repeat: no-repeat;
  float: right;
  color: #000;
  margin-top: 0px;
  width: 170px;
  font-size: 35px;
  position: absolute;
  right: 23px;
  top: 161px;
  line-height: 36px;
  font-weight: 700;
  min-height: 115px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
  font-size: 10px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-weight: 400;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.field > label[for^="nl_month"] {
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 32px;
  line-height: 32px;
  font-size: 12px;
  color: #000000;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #a9a9a9;
  transition: background-color 0.5s;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #FAF5C4;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 35px;
  font-size: 12px;
  color: #000000;
  border: 1px solid #a9a9a9;
  outline: none;
  width: 100%;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #616161;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #616161;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #616161;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #616161;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #616161;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #616161;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  display: block;
  content: "";
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  position: relative;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
  position: static !important;
  background: none;
  color: red !important;
  font-weight: 400;
  box-shadow: none;
  padding: 0 !important;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #FFA50A;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #FFA50A),color-stop(1, #FF810B));
  background-image: -o-linear-gradient(bottom, #FFA50A 0%, #FF810B 100%);
  background-image: -moz-linear-gradient(bottom, #FFA50A 0%, #FF810B 100%);
  background-image: -webkit-linear-gradient(bottom, #FFA50A 0%, #FF810B 100%);
  background-image: -ms-linear-gradient(bottom, #FFA50A 0%, #FF810B 100%);
  background-image: linear-gradient(to bottom, #FFA50A 0%, #FF810B 100%);
  color: #fff;
  font-size: 14px;
  height: 35px;
  padding: 0 35px;
  text-align: center;
  line-height: 33px;
  display: inline-block;
  border-radius: 2px;
  text-transform: none;
  border-top: 1px solid #feab0a;
  border-right: 1px solid #ec8c09;
  border-bottom: 1px solid #dd7208;
  border-left: 1px solid #ec8c09;
  box-shadow: inset 0 2px 0 0 #ffdc9d;
  -moz-box-shadow: inset 0 2px 0 0 #ffdc9d;
  -webkit-box-shadow: inset 0 2px 0 0 #ffdc9d;
  outline: none;
  text-decoration: none;
  text-shadow: 0 1px 0 #c66b07;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  opacity: 0.8;
}

@media screen and (max-width: 863px) {
  .newspopup_up_bg .error,
  .newspopup_up_bg .success {
    width: 90%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 610px;
    margin: 10% auto 5% auto;
    padding-right: 30px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col {
    background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme5_bg_retina.png"}}");
    width: 100%;
    background-position: 50%;
    background-repeat: no-repeat;
    float: none;
    height: 335px;
    -webkit-background-size: 550px;
    -moz-background-size: 550px;
    background-size: 550px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    width: 404px;
    float: right;
    margin: 0px 45px 0 0;
    padding: 25px 30px 25px 30px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col .cross {
    display: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col .cross {
    display: block;
    top: 35px;
    right: 36px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    right: 45px;
    top: 60px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo {
    top: 163px;
    right: 37px;
  }
}

@media screen and (max-width: 600px) {
  .newspopup-up-form.newspopup-theme {
    padding-right: 0px;
    width: 100%;
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col {
    background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme5_bg_retina.png"}}");
    height: 270px;
    -webkit-background-size: 440px;
    -moz-background-size: 440px;
    background-size: 440px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col-wrapper {
    width: 92%;
    margin: 0 auto;
    max-width: 400px;
    position: relative;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    width: 92%;
    float: none;
    margin: 0px auto;
    padding: 25px 30px 25px 30px;
    max-width: 300px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col .cross {
    display: none;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    right: 0;
    top: 55px;
    width: 126px;
    font-size: 14px;
    line-height: 17px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col .cross {
    top: 25px;
    right: 0px;
    line-height: 12px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col a.privacy-policy {
    bottom: auto;
    right: 40px;
    top: 228px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo {
    top: 140px;
    -webkit-background-size: contain;
    -moz-background-size: contain;
    background-size: contain;
    width: 100px;
    right: 21px;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .newspopup-left-col a.privacy-policy {
    right: 10px;
  }
}

@media screen and (max-width: 420px) {
  .newspopup-up-form.newspopup-theme .newspopup-logo {
    right: 10px;
  }
}

@media screen and (max-width: 380px) {
  .newspopup-up-form.newspopup-theme .newspopup-logo {
    right: -10px;
  }
  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 13px;
  }
}
/*==================== end Theme 5 ====================*/
CSS;
        $rows[6]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">{{text_cancel}}</div>
<div class="newspopup-left-col">
  <div class="prnp-title">{{text_title}}</div>
  <div class="newspopup-descr">
    {{text_description}}
  </div>
  <form class="newspopup_up_bg_form" method="POST">
    {{form_fields}}
    <div class="newspopup-right-col">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme6_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </form>
</div>
HTML;
        $rows[6]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Quicksand:300,400);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 6 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  width: 645px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #726f69;
  text-align: center;
  padding: 0px;
  padding-right: 40px;
  background-color: #1f1f1f;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme6_bg.jpg"}}");
  background-position: bottom right;
  background-repeat: no-repeat;
  min-height: 380px;
  -webkit-box-shadow: 0 0 30px 0 #292929;
  -moz-box-shadow: 0 0 30px 0 #292929;
  box-shadow: 0 0 30px 0 #292929;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  width: 55%;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #898989;
  top: 10px;
  right: 30px;
  position: absolute;
  font-weight: 700;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 8px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: underline;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 34px;
  font-weight: 300;
  text-transform: none;
  color: #79d5e6;
  margin: 0 0 20px 0;
  font-family: 'Quicksand', Arial, sans-serif;
  padding: 30px 0 0 40px;
  text-align: left;
  line-height: 33px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  width: 270px;
  color: #a7a7a7;
  text-align: left;
  font-size: 14px;
  line-height: 18px;
  padding-left: 40px;
  margin-bottom: 30px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr .price-off {
  font-family: 'Open Sans', Arial, sans-serif;
  text-transform: uppercase;
  color: #cd8407;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 220px;
  margin-left: 40px;
  padding-bottom: 25px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .newspopup-right-col {
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 25px;
  line-height: 25px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #ffffff;
  padding: 0 5px;
  outline: none;
  border: 1px solid #a9a9a9;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  background: none;
  background-color: #585858;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #848484;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 25px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #ffffff;
  border: 1px solid #a9a9a9;
  background-color: #585858;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  padding-left: 10px;
  line-height: 25px;
  padding-top: 0;
  padding-bottom: 0;
}

.newspopup-up-form.newspopup-theme input[placeholder]      {color:#B0B0B0;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder   {color:#B0B0B0; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder    {color:#B0B0B0; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color:#B0B0B0;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder{color:#B0B0B0; opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color:#B0B0B0;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
  position: static!important;
  background: none;
  color: red!important;
  font-weight: 400;
  box-shadow: none;
  padding: 0!important;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #0ea7ef;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #17c5f4),color-stop(1, #0ea7ef));
  background-image: -o-linear-gradient(bottom, #17c5f4 0%, #0ea7ef 100%);
  background-image: -moz-linear-gradient(bottom, #17c5f4 0%, #0ea7ef 100%);
  background-image: -webkit-linear-gradient(bottom, #17c5f4 0%, #0ea7ef 100%);
  background-image: -ms-linear-gradient(bottom, #17c5f4 0%, #0ea7ef 100%);
  background-image: linear-gradient(to bottom, #17c5f4 0%, #0ea7ef 100%);
  color: #1c1c1c;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 13px;
  font-weight: 700;
  height: 30px;
  padding: 0 35px;
  text-align: center;
  line-height: 28px;
  display: inline-block;
  border-radius: 2px;
  text-transform: uppercase;
  border-top: 2px solid #54d7f8;
  min-width: 140px;
  text-shadow: 1px 2px #5fccf6;
  margin-top: 10px;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #128DC6;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #16B0DA),color-stop(1, #128DC6));
  background-image: -o-linear-gradient(bottom, #16B0DA 0%, #128DC6 100%);
  background-image: -moz-linear-gradient(bottom, #16B0DA 0%, #128DC6 100%);
  background-image: -webkit-linear-gradient(bottom, #16B0DA 0%, #128DC6 100%);
  background-image: -ms-linear-gradient(bottom, #16B0DA 0%, #128DC6 100%);
  background-image: linear-gradient(to bottom, #16B0DA 0%, #128DC6 100%);
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 45px;
  margin-right: -50px;
}

@media screen and (max-width: 1200px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
    color: #fff;
  }
}

@media screen and (max-width: 860px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }
}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding-right: 20px;
  }

  .newspopup-up-form.newspopup-theme .hld-pop:before {
    width: 100%;
    position: absolute;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    content: "";
    left: 0;
    z-index: 1;
  }

  .newspopup-up-form.newspopup-theme .hld-pop .newspopup-left-col {
    width: 100%;
    position: relative;
    z-index: 100;
  }

  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    text-shadow: 1px 2px 3px #000;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    padding-right: 0;
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .cross {
    right: 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    margin-left: 0;
    padding-left: 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: 100%;
    padding-left: 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 28px;
    line-height: 26px;
  }
}
/*==================== end Theme 6 ====================*/
CSS;
        $rows[7]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="prnp-title"><span>{{text_title}}</span></div>

<div class="newspopup-logo">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div style="position:relative;">
    {{form_fields}}

    <div style="position:relative;">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
      <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank" class="privacy-policy">Privacy Policy</a>
    </div>
  </div>
</form>
HTML;
        $rows[7]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto);
@import url(https://fonts.googleapis.com/css?family=Bree+Serif);

.newspopup_up_bg .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup_up_bg {
  padding: 5%;
}

/*==================== Theme 7 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  width: 600px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  display: block;
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #fff;
  font-size: 18px;
  color: #4c5669;
  text-align: center;
  padding: 0 0 10px 0px;
  min-height: 245px;
}

.newspopup-up-form.newspopup-theme .hld-pop:before {
  background-color: #f74174;
  height: 70px;
  display: block;
  content: "";
  width: 100%;
  position: absolute;
  left: 0;
  z-index: 10;
}

.newspopup-up-form.newspopup-theme .hld-pop:after {
  content: "";
  width: 100%;
  position: absolute;
  bottom: 0;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme7_bg.png"}}");
  background-position: top right;
  height: 244px;
  background-repeat: no-repeat;
  left: 0;
  z-index: 10;
  -webkit-background-size: 346px;
  -moz-background-size: 346px;
  background-size: 346px;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  background-color: #f97197;
  color: #b93157;
  top: 6px;
  right: 6px;
  position: absolute;
  font-weight: 700;
  font-size: 13px;
  border-radius: 50%;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  width: 20px;
  height: 20px;
  line-height: 20px;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  background-color: #FD90AF;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 240px;
  display: block;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 32px;
  left: 15px;
  right: auto;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 32px;
  font-weight: 400;
  color: #FFFFFF;
  margin: 0;
  font-family: 'Bree Serif', Arial, sans-serif;
  padding: 17px 25px;
  text-align: left;
  text-transform: none;
  position: absolute;
  z-index: 500;
  line-height: 1.2;
}

.newspopup-up-form.newspopup-theme .prnp-title span {
  font-family: 'Bree Serif', serif;
}

.newspopup-up-form.newspopup-theme .small_text {
  line-height: 22px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 340px;
  height: 35px;
  line-height: 35px;
  font-size: 12px;
  color: #888888;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #dce1ec;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  color: #c10d39;
  text-align: left;
  padding-top: 90px;
  margin-left: 25px;
  width: 290px;
  font-size: 22px;
  line-height: 26px;
  font-family: 'Roboto', Arial, sans-serif;
  position: relative;
  z-index: 100;
}

.newspopup-up-form.newspopup-theme .newspopup-logo h2 {
  font-size: 41px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-weight: 700;
  color: #fff;
  margin: 0;
  padding: 0;
  line-height: 41px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 290px;
  margin-left: 25px;
  margin-top: 8px;
  position: relative;
  z-index: 500;
  display: block;
  text-align: right;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 35px;
  line-height: 35px;
  font-size: 12px;
  color: #9F9F9F;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #d6d6d6;
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #fff;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 35px;
  font-size: 12px;
  color: #9F9F9F;
  border: 1px solid #d6d6d6;
  outline: none;
  width: 100%;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #bdbdbd;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #bdbdbd;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #bdbdbd;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #bdbdbd;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #bdbdbd;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #bdbdbd;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 240px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .send {
  background: #2b97d8;
  background: -moz-linear-gradient(top,  #2b97d8 0%, #145eb9 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#2b97d8), color-stop(100%,#145eb9));
  background: -webkit-linear-gradient(top,  #2b97d8 0%,#145eb9 100%);
  background: -o-linear-gradient(top,  #2b97d8 0%,#145eb9 100%);
  background: -ms-linear-gradient(top,  #2b97d8 0%,#145eb9 100%);
  background: linear-gradient(to bottom,  #2b97d8 0%,#145eb9 100%);
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#2b97d8', endColorstr='#145eb9',GradientType=0 );
  -webkit-box-shadow: inset 0 2px 0 0 #50b1e3;
  -moz-box-shadow: inset 0 2px 0 0 #50b1e3;
  box-shadow: inset 0 2px 0 0 #50b1e3;
  color: #fff;
  font-size: 14px;
  height: 32px;
  width: 140px;
  text-align: center;
  line-height: 31px;
  display: block;
  border-radius: 5px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  text-transform: none;
  text-decoration: none;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
  text-shadow: 1px -1px #154d76;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  opacity: 0.8;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  color: #9e9e9e;
  font-size: 10px;
  text-decoration: underline;
  margin-top: 0;
  display: inline-block;
}

.newspopup-up-form.newspopup-theme .privacy-policy:hover {
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme ul + h3 {text-align: left;}

.newspopup-up-form.newspopup-theme div.mage-error {margin-bottom: 0;}

@media screen and (max-width: 890px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: red !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 680px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    text-align: left;
  }
}

@media screen and (max-width: 550px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 0 auto 5% auto;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 30px;
    line-height: 70px;
    padding: 0 25px;
  }
  .newspopup-up-form.newspopup-theme .prnp-title span {
    line-height: 27px;
    display: inline-block;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo {
    width: 200px;
  }

  .newspopup-up-form.newspopup-theme .ajax-loader {
    left: 50%;
    margin-left: -52px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    width: 50%;
    text-align: left;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    margin-left: 0;
    margin-top: 10px;
    padding: 0 25px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 50%;
  }

  .newspopup-up-form.newspopup-theme .send {
    margin: 0;
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .privacy-policy {
    margin-left: 0;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .hld-pop:after {
    display: none;
  }

  .newspopup-up-form.newspopup-theme .prnp-title span {
    vertical-align: middle;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo {
    background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme7_bg.png"}}");
    background-size: 170px;
    background-position: bottom right;
    background-repeat: no-repeat;
    width: 100%;
    padding: 80px 160px 0 20px;
    margin: 0;
    height: 215px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    padding: 0 30px 0 20px;
    font-size: 27px;
    line-height: 60px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    width: 100%;
    padding: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 100%;
  }
}

@media screen and (max-width: 410px) {
  .newspopup-up-form.newspopup-theme .newspopup-logo {
    background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme7_bg.png"}}");
    background-size: 170px;
    background-position: bottom right;
    background-repeat: no-repeat;
    width: 100%;
    padding: 80px 160px 0 20px;
    margin: 0;
    height: 215px;
    font-size: 17px;
    line-height: 20px;
  }
}
/*==================== end theme 7 ====================*/
CSS;
        $rows[8]['code'] = <<<HTML
<div class="prnp-title">{{text_title}}</div>
<div class="border-title">
  <span class="border-title-line"></span>
  <span class="border-title-circle"></span>
  <span class="border-title-line"></span>
</div>
<div class="newspopup-descr">
  {{text_description}}
</div>
<form class="newspopup_up_bg_form" method="POST">
  <div style="position:relative;">
    {{form_fields}}

    <div style="position:relative;">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme8_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </div>
</form>
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
HTML;
        $rows[8]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Sansita+One);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(255, 255, 255, 0.5) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color:#fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {background: #262626; cursor: pointer;}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 8 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  width: 355px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  padding: 20px 25px 25px 25px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #c1c1c1;
  text-align: center;
  background-color: #141313;
  -webkit-box-shadow: 0 5px 10px 0 #727272;
  -moz-box-shadow: 0 5px 10px 0 #727272;
  box-shadow: 0 5px 10px 0 #727272;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #515151;
  top: 7px;
  right: 10px;
  position: absolute;
  font-weight: 700;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #787878;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 44px;
  font-weight: 400;
  text-transform: none;
  margin: 0;
  font-family: 'Sansita One', cursive;
  text-align: left;
  color: #d23c3c;
  text-align: center;
  padding: 0;
  letter-spacing: -1px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  font-size: 14px;
  text-transform: uppercase;
  line-height: 18px;
  margin-top: 10px;
  margin-bottom: 15px;
}


.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 0 40px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 30px;
  line-height: 30px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 0px;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  background: none;
  background-color: #c1c1c1;
  filter: none;
  opacity: 1;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 30px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  border: 0px;
  background-color: #c1c1c1;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme input[placeholder]      {color:#504b4b;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder   {color:#504b4b; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder    {color:#504b4b; opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color:#504b4b;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder{color:#504b4b; opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color:#504b4b;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  clear: both;
  display: block;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
  position: static!important;
  background: none;
  color: #FF6868!important;
  font-weight: 400;
  box-shadow: none;
  padding: 0!important;
  width: 100%
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input:focus {
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 30px;
  padding: 0 20px;
  text-align: center;
  line-height: 30px;
  display: inline-block;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  text-transform: none;
  min-width: 110px;
  background: #d23c3c;
  margin-top: 15px;
  outline: none;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background: #B83737;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 50px;
  margin-right: -50px;
}


@media screen and (max-width: 480px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    padding: 0;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 38px;
    line-height: 38px;
  }
}

@media screen and (max-width: 400px) {
  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 34px;
  }
}
/*==================== end Theme 8 ====================*/
CSS;
        $rows[9]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
<div class="prnp-title">
  <div class="price-off"></div>
  <span>{{text_title}}</span>
</div>

<div class="newspopup-descr">{{text_description}}</div>

<form class="newspopup_up_bg_form" method="POST">
  <div class="newspopup-left-col">
    {{form_fields}}
  </div>
  <div class="newspopup-right-col">
    <div class="newspopup-right-col-wrapper">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme9_loader.gif"}}" style="display: none;" class="ajax-loader" />
    </div>
  </div>
  <div class="pl-clearfix"></div>
  {{mailchimp_fields}}
</form>
<a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank" class="privacy-policy">Privacy Policy</a>
HTML;
        $rows[9]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  line-height: 15px;
  position: absolute;
  z-index: 1200;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 9 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  width: 535px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  width: 535px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #726f69;
  text-align: left;
  padding: 25px 0 10px 0;
  background-color: #000;
  background-color: rgba(0,0,0,0.7);
  background-position: center right;
  background-repeat: no-repeat;
  min-height: 235px;
  -webkit-box-shadow: 0 0 30px 0 #292929;
  -moz-box-shadow: 0 0 30px 0 #292929;
  box-shadow: 0 0 30px 0 #292929;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #515151;
  top: 6px;
  right: 7px;
  position: absolute;
  font-weight: 700;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 11px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #7B7B7B;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col {
  float: right;
  width: 105px;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col-wrapper {
  position: relative;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 28px;
  font-weight: 400;
  text-transform: none;
  color: #ffe60f;
  margin: 0;
  font-family: 'Open Sans', Arial, sans-serif;
  padding: 0;
  text-align: left;
  line-height: 25px;
  background: rgba(0,0,0,0.2);
  position: relative;
  padding-left: 230px;
  height: 94px;
  border-right: 10px solid #FEE50E;
}

.newspopup-up-form.newspopup-theme .prnp-title span {
  padding-top: 8px;
  display: inline-block;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  color: #c1c1c1;
  text-align: left;
  font-size: 13px;
  line-height: 18px;
  padding: 0 35px;
  margin: 15px 0;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .price-off {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme9_price_off.jpg"}}");
  background-size: 167px 94px;
  width: 167px;
  height: 94px;
  position: absolute;
  left: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 0 35px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 28px;
  line-height: 28px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #a9a9a9;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  background: none;
  background-color: #ffffff;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
  padding-left: 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 28px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000;
  border: 0px;
  background-color: #fff;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 175px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .pl-clearfix + h3 {
  text-align: left;
  font-size: 15px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #818181;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #818181;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #818181;
  opacity: 1;
}

.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #818181;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #818181;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 350px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
  width: 50%;
  float: left;
  padding-right: 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:nth-child(2n+1) {
  clear: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
  position: static !important;
  background: none;
  color: red !important;
  font-weight: 400;
  box-shadow: none;
  padding: 0 !important;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob {
  float: left;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 28px;
  padding: 0 25px;
  text-align: center;
  line-height: 28px;
  display: inline-block;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  text-transform: none;
  border: 0px;
  max-width: 150px;
  background: #cc0d0d;
  text-shadow: 0 1px #ae0e0e;
  width: 100%;
  outline: none;
  -webkit-box-shadow: 0 0 2px 0 #671f1f, inset 0 2px 1px 0 #f74646;
  -moz-box-shadow: 0 0 2px 0 #671f1f, inset 0 2px 1px 0 #f74646;
  box-shadow: 0 0 2px 0 #671f1f, inset 0 2px 1px 0 #f74646;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background: #D52A2A;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 33px;
  margin-right: -50px;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  display: inline-block;
  color: #5e5e5e;
  font-size: 10px;
  text-decoration: underline;
  margin-left: 35px;
  vertical-align: top;
}

.newspopup-up-form.newspopup-theme .privacy-policy:hover {
  text-decoration: none;
}

@media screen and (max-width: 1200px) {
  .newspopup-up-form.newspopup-theme .cross {
    font-size: 19px;
  }
}

@media screen and (max-width: 600px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme,
  .newspopup-up-form.newspopup-theme .hld-pop {
    width: 100%;
    text-align: center;
  }

  .newspopup-up-form.newspopup-theme {
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 25px;
    border: 0px;
    height: auto;
    padding: 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    text-align: center;
    margin: 0 0 15px 0;
  }

  .newspopup-up-form.newspopup-theme .price-off {
    margin: 0 auto;
    position: static;
  }

  .newspopup-up-form.newspopup-theme .prnp-title span {
    padding-top: 10px;
    text-align: center;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col,
  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    margin-top: 10px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0px 20px 15px 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    width: 50%;
  }

  .newspopup-up-form.newspopup-theme .privacy-policy {
    margin: 3px 0 0 0;
    float: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 50%;
  }
}

@media screen and (max-width: 560px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 100%;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    margin-left: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: 100%;
    padding-left: 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 25px;
    line-height: 25px;
    padding: 10px;
  }
}
/*==================== end Theme 9 ====================*/
CSS;
        $rows[10]['code'] = <<<HTML
<div class="prnp-title">{{text_title}}</div>
<div class="border-title">
  <span class="border-title-line"></span>
  <span class="border-title-circle"></span>
  <span class="border-title-line"></span>
</div>
<div class="newspopup-descr">
  {{text_description}}
</div>
<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}

  <div>
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme10_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
</form>
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
HTML;
        $rows[10]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Quicksand);
@import url(https://fonts.googleapis.com/css?family=Roboto:400,700);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup_up_bg div.mage-error {
  bottom: 2px;
}

/*==================== Theme 10 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: relative;
  width: 420px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  padding: 40px 20px 60px 20px;
  background: none;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme10_bg.jpg"}}");
  font-family: 'Open Sans', Arial, sans-serif;
  border: 8px solid #fff;
  font-size: 14px;
  color: #c1c1c1;
  text-align: center;
  -webkit-box-shadow: 0 5px 18px 0 #23231F;
  -moz-box-shadow: 0 5px 18px 0 #23231F;
  box-shadow: 0 5px 18px 0 #23231F;
  background-position: bottom;
  background-repeat: no-repeat;
  background-color: #707991;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #fff;
  top: 7px;
  right: 10px;
  position: absolute;
  font-weight: 700;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #CECECE;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 42px;
  font-weight: 400;
  text-transform: none;
  color: #ffcc00;
  margin: 0;
  font-family: 'Quicksand', Arial, Helvetica, sans-serif;
  text-align: left;
  color: #ffffff;
  text-align: center;
  padding: 0;
  letter-spacing: -3px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  font-size: 16px;
  text-transform: uppercase;
  line-height: 18px;
  margin-top: 25px;
  margin-bottom: 37px;
  color: #4b3a4b;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3 {
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item {
  width: 205px;
  margin: auto;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 0 40px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 40px;
  line-height: 40px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #c6c6c6;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  background: none;
  background-color: #fff;
  filter: none;
  opacity: 1;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
  background-color: #FFE6E6;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 40px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  border: 0px;
  background-color: #fff;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #504b4b;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 205px;
  margin: auto;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 15px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  clear: both;
  display: block;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input:focus {
  padding-left: 0px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  position: relative;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 40px;
  line-height: 40px;
  padding: 0 20px;
  text-align: center;
  display: inline-block;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  text-transform: none;
  text-shadow: 0 2px #d04d05;
  width: 100%;
  max-width: 205px;
  background: #F65606;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #FC7506),color-stop(1, #F65606));
  background-image: -o-linear-gradient(bottom, #FC7506 0%, #F65606 100%);
  background-image: -moz-linear-gradient(bottom, #FC7506 0%, #F65606 100%);
  background-image: -webkit-linear-gradient(bottom, #FC7506 0%, #F65606 100%);
  background-image: -ms-linear-gradient(bottom, #FC7506 0%, #F65606 100%);
  background-image: linear-gradient(to bottom, #FC7506 0%, #F65606 100%);
  -webkit-box-shadow: inset 0 2px 1px 0 #fd953d, 0 1px 2px 0 #B25403;
  -moz-box-shadow: inset 0 2px 1px 0 #fd953d, 0 1px 2px 0 #B25403;
  box-shadow: inset 0 2px 1px 0 #fd953d, 0 1px 2px 0 #B25403;
  margin-top: 15px;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  -webkit-background-image: linear-gradient(to bottom, #F57207 0%, #E95409 100%);
  -moz-background-image: linear-gradient(to bottom, #F57207 0%, #E95409 100%);
  background-image: linear-gradient(to bottom, #F57207 0%, #E95409 100%);
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 60px;
  margin-right: -50px;
}

@media screen and (max-width: 550px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF3737 !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 480px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 5% auto;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    padding: 0;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 30px 20px 40px 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 34px;
    line-height: 38px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    margin-bottom: 15px;
    margin-bottom: 25px;
  }
}
/*==================== end Theme 10 ====================*/
CSS;
        $rows[11]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">{{text_cancel}}</div>
<div class="prnp-title">{{text_description}}</div>

<div class="newspopup-descr">
  {{text_title}}
  <div class="price-off"></div>
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div class="newspopup-left-col">
    {{form_fields}}
  </div>

  <div class="newspopup-right-col">
    <div class="newspopup-right-col-wrapper">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme11_loader.gif"}}" style="display: none;" class="ajax-loader" />
    </div>
  </div>
  <div class="pl-clearfix"></div>
    {{mailchimp_fields}}
  <div class="newspopup-bottom-col">
    <div class="newspopup-right-col-wrapper">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme11_loader.gif"}}" style="display: none;" class="ajax-loader" />
    </div>
  </div>
</form>
HTML;
        $rows[11]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Quicksand:300,400);
@import url(https://fonts.googleapis.com/css?family=Open+Sans:700,800,400);

.newspopup_up_bg div.mage-error {
  background: #BA0000;
  bottom: 5px;
  color: #FFF !important;
  font-size: 11px;
  font-weight: bold;
  line-height: 13px;
  min-height: 13px;
  padding: 10px !important;
  position: absolute !important;
  white-space: normal;
  left: -160px;
  width: 150px;
  border-radius: 5px;
  -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup_up_bg div.mage-error:after {
  position: absolute;
  right: -8px;
  bottom: 8px;
  content: " ";
  width: 0;
  height: 0;
  border-top: 8px solid rgba(0, 0, 0, 0);
  border-bottom: 8px solid rgba(0, 0, 0, 0);
  border-left: 8px solid #BA0000;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 24px;
  width: 24px;
  color: #fff;
  line-height: 23px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 11 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  width: 720px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #726f69;
  text-align: center;
  padding: 0px;
  background-color: #1d1d1d;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme11_bg.jpg"}}");
  background-position: center right;
  background-repeat: no-repeat;
  min-height: 380px;
  border: 1px solid #5b4b0b;
  -webkit-box-shadow: 0 0 30px 0 #292929;
  -moz-box-shadow: 0 0 30px 0 #292929;
  box-shadow: 0 0 30px 0 #292929;
  position: relative;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #898989;
  top: 10px;
  right: 30px;
  position: absolute;
  font-weight: 700;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 8px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: underline;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col {
  float: right;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup-bottom-col {
  display: none;
  position: relative;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 22px;
  font-weight: 800;
  text-transform: none;
  color: #ffcc00;
  margin: 0 0 20px 0;
  font-family: 'Open Sans', Arial, sans-serif;
  padding: 30px 0 0 40px;
  text-align: left;
  line-height: 25px;
  width: 50%;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  width: 270px;
  color: #f4f4f4;
  text-align: left;
  font-size: 14px;
  line-height: 18px;
  padding-left: 40px;
  margin-bottom: 30px;
  font-family: 'Quicksand', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup-descr .price-off {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme11_price_off.png"}}");
  -webkit-background-size: 179px 82px;
  -moz-background-size: 179px 82px;
  background-size: 179px 82px;
  width: 179px;
  height: 82px;
  margin-top: 25px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 35px 62px;
  background: rgba(0,0,0,.63);
  display: block;
  position: relative;
  z-index: 100;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 35px;
  line-height: 35px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #282828;
  padding: 0 15px;
  outline: none;
  border: 0px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  background: none;
  background-color: #f4f4f4;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 35px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #282828;
  border: 0px;
  background-color: #f4f4f4;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 3px 0 0 3px;
  -moz-border-radius: 3px 0 0 3px;
  border-radius: 3px 0 0 3px;
  padding-left: 15px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 215px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .pl-clearfix + h3 {
  text-align: left;
  font-size: 15px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #707070;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #707070;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #707070;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #707070;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #707070;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #707070;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 445px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
  width: 220px;
  float: left;
  padding-right: 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:nth-child(2n+1) {
  clear: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
  position: static !important;
  background: none;
  color: red !important;
  font-weight: 400;
  box-shadow: none;
  padding: 0 !important;
}

.newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob {
  float: left;
}

.newspopup-up-form.newspopup-theme .send {
  color: #080808;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 11px;
  font-weight: 700;
  height: 35px;
  padding: 0 35px;
  text-align: center;
  line-height: 33px;
  display: inline-block;
  border-radius: 2px;
  text-transform: uppercase;
  border-top: 2px solid #ffeb99;
  min-width: 145px;
  background: #ffcc00;
  -webkit-box-shadow: 0 0 2px 0 #40360c;
  -moz-box-shadow: 0 0 2px 0 #40360c;
  box-shadow: 0 0 2px 0 #40360c;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background: #EABB00;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 40px;
  margin-right: -50px;
}


.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item input[type="radio"] + label,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item input[type="checkbox"] + label {color:#fff;}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3 {text-align: left;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item {position: relative; z-index: 5;}



@media screen and (max-width: 860px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col,
  .newspopup-up-form.newspopup-theme .newspopup-right-col,
  .newspopup-up-form.newspopup-theme .newspopup-bottom-col {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    display: none;
  }
  .newspopup-up-form.newspopup-theme .newspopup-bottom-col {
    display: block;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    margin-top: 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    width: 50%;
  }

  .newspopup-up-form.newspopup-theme .hld-pop:before {
    position: absolute;
    background-color: rgba(0,0,0,0.5);
    content: "";
    width: 100%;
    height: 100%;
    left: 0;
    z-index: 0;
  }

  .newspopup-up-form.newspopup-theme .cross {
    z-index: 100;
  }

  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    position: relative;
    z-index: 100;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 50%;
  }
}

@media screen and (max-width: 560px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    width: 100%;
    padding-right: 0;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .cross {
    right: 20px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    margin-left: 0;
    padding: 25px 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: 100%;
    padding-left: 20px;
    padding-right: 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 21px;
    line-height: 24px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr .price-off {
    margin-bottom: 15px;
    margin-top: 15px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    margin-bottom: 15px;
    color: #ffffff;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    margin-top: 10px;
  }
}
/*==================== end Theme 11 ====================*/
CSS;
        $rows[12]['code'] = <<<HTML
<div class="prnp-title">{{text_title}}</div>
<div class="border-title">
  <span class="border-title-line"></span>
  <span class="border-title-circle"></span>
  <span class="border-title-line"></span>
</div>
<div class="newspopup-descr">
  {{text_description}}
</div>
<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}

  <div style="position: relative; margin-top: 10px;">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme12_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
</form>
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
HTML;
        $rows[12]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=PT+Serif:700);
@import url(https://fonts.googleapis.com/css?family=Roboto:400,700);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 12 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: relative;
  width: 345px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  padding: 43px 45px 65px 45px;
  background: #21355c url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme12_bg.png"}}");
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #53c3ec;
  text-align: center;
  -webkit-box-shadow: 0 5px 18px 0 #23231F;
  -moz-box-shadow: 0 5px 18px 0 #23231F;
  box-shadow: 0 5px 18px 0 #23231F;
  background-position: bottom center;
  background-repeat: no-repeat;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #fff;
  top: 15px;
  right: 15px;
  position: absolute;
  font-weight: 400;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #787878;
}

.newspopup_up_bg div.mage-error {
  bottom: 3px;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 48px;
  line-height: 42px;
  font-weight: 700;
  text-transform: uppercase;
  margin: 0;
  font-family: 'PT Serif', Arial, Helvetica, sans-serif;
  text-align: left;
  color: #ffffff;
  text-align: center;
  padding: 0;
  letter-spacing: -3px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  font-size: 14px;
  text-transform: none;
  line-height: 18px;
  margin: 25px auto 37px auto;
  width: 205px;
  color: #53c3ec;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 40px;
  line-height: 40px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #c6c6c6;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  background: none;
  background-color: #fff;
  filter: none;
  opacity: 1;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
  background-color: #FFE6E6;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 40px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  border: 0px;
  background-color: #fff;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 205px;
  display: block;
  margin: 0 auto;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3 {
  width: 205px;
  margin-left: auto;
  margin-right: auto;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #7A7A7A;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #7A7A7A;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #7A7A7A;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #7A7A7A;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #7A7A7A;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #7A7A7A;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 205px;
  margin: auto;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  clear: both;
  display: block;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input:focus {
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  position: relative;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 40px;
  line-height: 40px;
  padding: 0 20px;
  text-align: center;
  display: inline-block;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  text-transform: none;
  text-shadow: 0 2px #960c0c;
  width: 100%;
  max-width: 205px;
  background: #F42121;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #F42121),color-stop(1, #C70A0A));
  background-image: -o-linear-gradient(bottom, #F42121 0%, #C70A0A 100%);
  background-image: -moz-linear-gradient(bottom, #F42121 0%, #C70A0A 100%);
  background-image: -webkit-linear-gradient(bottom, #F42121 0%, #C70A0A 100%);
  background-image: -ms-linear-gradient(bottom, #F42121 0%, #C70A0A 100%);
  background-image: linear-gradient(to bottom, #F42121 0%, #C70A0A 100%);
  -webkit-box-shadow: inset 0 2px 1px 0 #f74646, 0 1px 2px 0 #5d3352;
  -moz-box-shadow: inset 0 2px 1px 0 #f74646, 0 1px 2px 0 #5d3352;
  box-shadow: inset 0 2px 1px 0 #f74646, 0 1px 2px 0 #5d3352;
  margin-top: 3px;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  -webkit-background-image: linear-gradient(to bottom, #E52020 0%, #B60D0D 100%);
  -moz-background-image: linear-gradient(to bottom, #E52020 0%, #B60D0D 100%);
  background-image: linear-gradient(to bottom, #E52020 0%, #B60D0D 100%);
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 50px;
  margin-right: -50px;
}

@media screen and (max-width: 550px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF3636 !important;
    font-weight: 700;
    box-shadow: none;
    padding: 0 !important;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 420px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 5% auto;
  }
}

@media screen and (max-width: 380px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
    padding: 0;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 38px;
    line-height: 38px;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 33px 25px 45px 25px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: auto;
    margin: 25px auto;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .send {
    max-width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + h3,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item {
    width: 100%;
  }
}
/*==================== end Theme 12 ====================*/
CSS;
        $rows[13]['code'] = <<<HTML
<div class="newspopup-up-form-wrapper">
  <div class="cross close" title="{{text_cancel}}">&#10005;</div>

  <div class="hld-pop-row">
    <div class="hld-pop-cell">
      <div class="prnp-title">{{text_title}}</div>
      <div class="newspopup-descr">
        <div class="img-wrapper"></div>
        {{text_description}}
      </div>
    </div>
    <div class="hld-pop-cell">
      <div class="newspopup-messages-holder"></div>
      <form class="newspopup_up_bg_form" method="POST">
        {{form_fields}}

        <div class="button-col">
          <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
            {{text_submit}}
          </a>
          <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
        </div>
      </form>
    </div>
  </div>
</div>
HTML;
        $rows[13]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto);
@import url(https://fonts.googleapis.com/css?family=Yeseva+One);

.newspopup_up_bg div.mage-error {
  font-size: 11px;
  line-height: 13px;
  min-height: 13px;
  white-space: normal;
  position: static !important;
  background: none;
  color: #FF0000 !important;
  font-weight: 400;
  box-shadow: none;
  padding: 0 !important;
  width: 100%;
}

.newspopup_up_bg div.mage-error:after {
  display: none;
}

.newspopup_up_bg .newspopup-messages-holder {
  position: absolute;
  top: -70px;
  max-width: 40%;
  right: 50%;
  margin-right: -20%;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  bottom: 0px;
  width: 100%;
  z-index: 1100;
  height: auto;
  text-align: left;
  overflow-y: visible;
  overflow-x: visible;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup-blur {
  -webkit-filter: none;
  -moz-filter: none;
  filter: none;
}

.newspopup_ov_hidden {
  overflow-y: auto;
}

/*==================== Theme 13 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: static;
  width: 100%;
  margin: 0;
  bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup-up-form-wrapper {
  max-width: 985px;
  width: 100%;
  margin: 0 auto;
  position: relative;
  display: table;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: static;
  padding: 20px;
  background-color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #303030;
  bottom: 0;
  width: 100%;
  border-top: 1px solid #a3a3a3;
  -webkit-box-shadow: 0 -2px 24px 0px rgba(39, 39, 39, 0.19);
  -moz-box-shadow: 0 -2px 24px 0px rgba(39, 39, 39, 0.19);
  box-shadow: 0 -2px 24px 0px rgba(39, 39, 39, 0.19);
}

.newspopup-up-form.newspopup-theme .hld-pop .hld-pop-row {
  display: table-row;
}

.newspopup-up-form.newspopup-theme .hld-pop .hld-pop-cell {
  display: table-cell;
  vertical-align: top;
}

.newspopup-up-form.newspopup-theme .img-wrapper {
  position: absolute;
  background: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme13_bg.png"}}");
  -webkit-background-size: 100%;
  -moz-background-size: 100%;
  background-size: 100%;
  background-repeat: no-repeat;
  width: 230px;
  height: 140px;
  position: absolute;
  bottom: -20px;
  left: 230px;
  background-position: bottom;
}

.newspopup-up-form.newspopup-theme .cross {
  background: #cecece;
  color: #fff;
  top: -10px;
  right: -10px;
  position: absolute;
  font-weight: 400;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
  width: 17px;
  height: 17px;
  line-height: 17px;
  text-align: center;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:active,
.newspopup-up-form.newspopup-theme .cross:hover {
  background: #ABABAB;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 35px;
  line-height: 42px;
  font-weight: 400;
  text-transform: uppercase;
  margin: 0;
  font-family: 'Yeseva One', Arial, Helvetica, sans-serif;
  text-align: left;
  color: #303030;
  text-align: left;
  padding: 0;
  width: 235px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  font-size: 14px;
  text-transform: none;
  line-height: 15px;
  margin: 0;
  color: #a3a3a3;
  text-align: left;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
  width: 235px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  max-width: 480px;
  padding: 0;
  margin: 20px 0 0 0;
  float: right;
}

.newspopup-up-form.newspopup-theme .hld-pop:before,
.newspopup-up-form.newspopup-theme .hld-pop:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.mage-error {
  margin-top: 0;
  margin-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 33px;
  line-height: 33px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #b7b7b7;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  background: none;
  background-color: #fff;
  filter: none;
  opacity: 1;
  text-align: left;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
  background-color: #FFE6E6;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 33px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  border: 1px solid #b7b7b7;
  background-color: #fff;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  padding-left: 10px;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 12px;
  vertical-align: middle;
}

.newspopup-up-form.newspopup-theme input[placeholder] {color: #504b4b;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color: #504b4b;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color: #504b4b;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
  float: left;
  width: 120px;
  margin: 0 auto;
  text-align: center;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 350px;
  margin: 0;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
  width: 50%;
  float: left;
  padding-right: 7px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:nth-child(2n+1) {
  clear: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  clear: both;
  display: block;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 33%;
  float: left;
  padding-right: 7px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 34%;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input:focus {
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 33px;
  line-height: 33px;
  padding: 0 20px;
  text-align: center;
  display: inline-block;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  text-transform: none;
  width: 100%;
  max-width: 120px;
  background: #303030;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background: #4D4D4D;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  position: static;
  margin: 0 auto;
}

@media screen and (max-width: 1024px) {
  .newspopup-up-form.newspopup-theme .newspopup-up-form-wrapper {
    max-width: 480px;
  }

  .newspopup-up-form.newspopup-theme .hld-pop .hld-pop-cell {
    display: block;
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    margin: 0;
    width: 50%;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    position: relative;
    margin-bottom: 10px;
  }

  .newspopup-up-form.newspopup-theme .img-wrapper {
    position: absolute;
    top: -85px;
    bottom: auto;
    left: 100%;
    width: 100%;
    background-position: bottom;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    float: none;
    margin: 0 auto;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    float: none;
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
    float: none;
    clear: left;
  }
  .newspopup-up-form.newspopup-theme .ajax-loader {
    position: absolute;
    margin-right: -57px;
  }
}

@media screen and (max-width: 770px) {
  .newspopup_up_bg .newspopup-messages-holder {
    top: -90px;
    max-width: 90%;
    right: 50%;
    margin-right: -45%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF0000 !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 380px) {
  .newspopup_up_bg .newspopup-messages-holder {
    top: -50px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .prnp-title,
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .img-wrapper {
    display: none;
  }
}
/*==================== end Theme 13 ====================*/
CSS;
        $rows[14]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="prnp-title">{{text_title}}</div>
<div class="newspopup-descr">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}

  <div class="button-col">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme14_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
  <div class="pl-clearfix"></div>
  {{mailchimp_fields}}

  <div class="button-col newspopup-bottom-col">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme14_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
</form>

<div class="privacy-policy">
  <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank">Privacy Policy</a>
</div>
HTML;
        $rows[14]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto);

.newspopup_up_bg {
  overflow: visible;
  display: block;
  left: 50%;
  position: fixed;
  bottom: 0px;
  width: 590px;
  z-index: 1100;
  padding: 0;
  color: #666666;
  text-align: left;
  margin-left: -295px;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup-blur {
  -webkit-filter: none;
  -moz-filter: none;
  filter: none;
}

.newspopup_ov_hidden {
  overflow: visible!important;
}

/*==================== Theme 14 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: static;
  display: block;
  width: 590px;
  margin: 0 auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #1c2029;
  font-size: 18px;
  color: #4c5669;
  text-align: left;
  padding: 30px 0 15px 0;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme14_bg.jpg"}}");
  -webkit-background-size: 310px;
  -moz-background-size: 310px;
  background-size: 310px;
  background-position: right -35px;
  background-repeat: no-repeat;
  border-left: 5px solid #cecece;
  border-right: 5px solid #cecece;
  border-top: 5px solid #cecece;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #fff;
  top: 0;
  right: 0;
  position: absolute;
  font-weight: 700;
  font-size: 11px;
  width: 35px;
  height: 35px;
  line-height: 35px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #e6a21f;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 30px;
  font-weight: 400;
  color: #fff;
  margin: 0 0 20px 0;
  font-family: Tahoma, Arial, sans-serif;
  padding: 0 50px;
  text-transform: none;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  color: #99a2b0;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 15px;
  padding: 0 50px;
  line-height: 18px;
  max-width: 320px;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 45px;
  margin-top: 25px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  color: #ffffff;
  font-size: 12px;
  vertical-align: middle;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 33px;
  line-height: 33px;
  font-size: 12px;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #aaa4a4;
  text-align: left;
  -webkit-border-radius: 0px;
  -moz-border-radius: 0px;
  border-radius: 0px;
  font-family: 'Open Sans', Arial, sans-serif;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

input.required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding: 0;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 33px;
  font-size: 12px;
  color: #888888;
  border: 1px solid #aaa4a4;
  outline: none;
  width: 100%;
  font-family: 'Open Sans', Arial, sans-serif;
  background-color: #fff;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 200px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .pl-clearfix + h3 {
  text-align: left;
  font-size: 15px;
  margin: 5px 0;
}

.newspopup-up-form.newspopup-theme input[placeholder] {color: #504b4b;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color: #504b4b;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color: #504b4b;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .newspopup-bottom-col {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
  float: left;
  width: 110px;
  margin: 0 auto;
  text-align: center;
  position: relative;
  height: 50px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  float: left;
  width: 200px;
  margin-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 5px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:after {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-full:before {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li div.mage-error {
  margin-bottom: 0;
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li.customer-dob div.mage-error {
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-size: 12px;
  height: 33px;
  line-height: 33px;
  width: 100%;
  text-align: center;
  display: inline-block;
  border-radius: 4px;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  background-color: #F87225;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #F87225),color-stop(1, #E5A31F));
  background-image: -o-linear-gradient(bottom, #F87225 0%, #E5A31F 100%);
  background-image: -moz-linear-gradient(bottom, #F87225 0%, #E5A31F 100%);
  background-image: -webkit-linear-gradient(bottom, #F87225 0%, #E5A31F 100%);
  background-image: -ms-linear-gradient(bottom, #F87225 0%, #E5A31F 100%);
  background-image: linear-gradient(to bottom, #E5A31F 0%, #F87225 100%);
  outline: none;
  font-family: 'Open Sans', Arial, sans-serif;
  -webkit-box-shadow: inset 0 1px 1px 0 #e8b642;
  -moz-box-shadow: inset 0 1px 1px 0 #e8b642;
  box-shadow: inset 0 1px 1px 0 #e8b642;
  text-shadow: 0 -1px #b7731a;
  -webkit-transition: all 0.3s;
  -moz-transition: all 0.3s;
  transition: all 0.3s;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  opacity: 0.9;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 40px;
  margin-right: -52px;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  text-align: center;
  height: 15px;
  line-height: 15px;
  margin: 0;
  padding: 0;

}

.newspopup-up-form.newspopup-theme .privacy-policy a {
  text-decoration: underline;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
  font-size: 12px;
  color: #555f71;
  display: inline;
}

.newspopup-up-form.newspopup-theme .privacy-policy a:hover {
  text-decoration: none;
}

@media screen and (max-width: 800px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF6A6A!important;
    font-weight: 400;
    box-shadow: none;
    padding: 0!important;
    text-align: left;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 600px) {
  .newspopup_up_bg {
    padding: 0;
    width: 100%;
    margin: 0;
    left: 0;
  }
  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 0;
    left: 0;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 50%;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px 0;
  }
  .newspopup-up-form.newspopup-theme .hld-pop:before {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    content: "";
    top: 0;
    z-index: 0;
  }
  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 25px;
    padding: 0 25px;
    margin: 0 0 5px 0;
    position: relative;
    z-index: 100;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    padding: 0 25px;
    position: relative;
    z-index: 100;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0 25px;
    margin-top: 10px;
    position: relative;
    z-index: 100;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 65%;
  }
}

@media screen and (max-width: 430px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
    margin-right: 0;
    float: none;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
    float: none;
    width: 100%;
    display: none;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col.newspopup-bottom-col {
    display: block;
  }
}
}
/*==================== end Theme 14 ====================*/
CSS;
        $rows[15]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="newspopup-left-col">
  <div class="prnp-title">{{text_title}}</div>
  <div class="newspopup-logo">
    {{text_description}}
  </div>
</div>

<div class="newspopup-right-col">
  <form class="newspopup_up_bg_form" method="POST">
    {{form_fields}}

    <div class="button-col">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </form>

  <div class="privacy-policy">
    <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}" target="_blank">Privacy Policy</a>
  </div>
</div>
HTML;
        $rows[15]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto:400,300);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -54px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 15 ====================*/
.newspopup-up-form.newspopup-theme {
  position: relative;
  display: table;
  width: 920px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme:before,
.newspopup-up-form.newspopup-theme:after {
  display: block;
  content: "";
  clear: both;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 14px;
  color: #4a4a4a;
  text-align: center;
  padding: 0px;
  display: block;
  position: relative;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col,
.newspopup-up-form.newspopup-theme .newspopup-right-col {
  display: table-cell;
  width: 50%;
  background-color: #f1f1f1;
}

.newspopup-up-form.newspopup-theme .newspopup-left-col {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme15_bg.jpg"}}");
  background-position: 0 0;
  width: 60%;
  padding: 50px 55px;
  -webkit-border-radius: 9px 0 0 9px;
  -moz-border-radius: 9px 0 0 9px;
  border-radius: 9px 0 0 9px;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  background-size: cover;
}

.newspopup-up-form.newspopup-theme .newspopup-right-col {
  padding: 10px 10px 30px 10px;
  width: 40%;
  -webkit-border-radius: 0 9px 9px 0;
  -moz-border-radius: 0 9px 9px 0;
  border-radius: 0 9px 9px 0;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #f1f1f1;
  top: 12px;
  right: 10px;
  position: absolute;
  font-weight: 700;
  font-size: 9px;
  border-radius: 50%;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  width: 18px;
  height: 18px;
  line-height: 18px;
  background-color: #b4b4b4;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #585858;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 50px;
  font-weight: 300;
  color: #3e3a3a;
  margin: 0 0 20px 0;
  font-family: 'Roboto', Arial, sans-serif;
  padding: 0;
  line-height: 50px;
  text-align: left;
  text-transform: uppercase;
}

.newspopup-up-form.newspopup-theme .newspopup-logo {
  font-size: 32px;
  font-family: 'Roboto', Arial, sans-serif;
  text-transform: none;
  font-weight: 300;
  color: #675734;
  margin: 0;
  padding: 0;
  line-height: 41px;
  line-height: 36px;
  text-align: left;
  padding-right: 120px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 25px 25px 25px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 50px;
  line-height: 50px;
  font-size: 14px;
  color: #a1adc6;
  padding: 0 10px 0 30px;
  outline: none;
  border: 1px solid #dadada;
  text-align: left;
  border-radius: 4px;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 14px;
  color: #a1adc6;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #CDCDCD;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 45px;
  font-size: 14px;
  color: #a1adc6;
  border: 1px solid #e2e2e0;
  outline: none;
  width: 100%;
  padding-left: 30px;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #a1adc6;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #a1adc6;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #a1adc6;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #a1adc6;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #a1adc6;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #a1adc6;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 17px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  display: block;
  content: "";
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-full:before {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li div.mage-error {
  margin-bottom: 6px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li.customer-dob div.mage-error {
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .button-col {
  position: relative;
  margin-top: 45px;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #146ad5;
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  font-family: 'Open Sans', Arial, sans-serif;
  height: 55px;
  line-height: 50px;
  width: 100%;
  text-align: center;
  display: inline-block;
  border-radius: 4px;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  -webkit-box-shadow: inset 0 -6px 0 0 #0d4385;
  -moz-box-shadow: inset 0 -6px 0 0 #0d4385;
  box-shadow: inset 0 -6px 0 0 #0d4385;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #1661BD;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 63px;
  right: 50%;
}

.newspopup-up-form.newspopup-theme .privacy-policy a {
  color: #4a4a4a;
  font-size: 12px;
  font-family: 'Roboto', Arial, sans-serif;
  text-decoration: underline;
}

.newspopup-up-form.newspopup-theme .privacy-policy a:hover {
  text-decoration: none;
}

@media screen and (max-width: 1024px) {
  .newspopup_up_bg {
    padding: 5%;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
    max-width: 500px;
  }

  .newspopup-up-form.newspopup-theme .cross {
    width: 25px;
    height: 25px;
    line-height: 25px;
    top: 10px;
    right: 10px;
    font-size: 13px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col,
  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    width: 100%;
    display: block;
  }

  .newspopup-up-form.newspopup-theme .newspopup-left-col {
    border-radius: 9px 9px 0 0;
    padding: 20px 25px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    border-radius: 0 0 9px 9px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 25px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 45px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo {
    font-size: 23px;
    padding-right: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo p {
    line-height: 27px;
  }
}

@media screen and (max-width: 780px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF0000!important;
    font-weight: 400;
    box-shadow: none;
    padding: 0!important;
    text-align: left;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 10px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 30px;
    line-height: 30px;
    margin: 15px 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo h2 {
    font-size: 20px;
    line-height: 22px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-right-col {
    padding: 10px 10px 20px 10px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
    height: 40px;
    line-height: 40px;
    padding: 0 10px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
    padding-right: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
    height: 40px;
    padding-left: 10px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
    margin-bottom: 10px;
  }

  .newspopup-up-form.newspopup-theme .button-col {
    margin-top: 15px;
  }

  .newspopup-up-form.newspopup-theme .send {
    height: 46px;
    line-height: 44px;
    font-size: 15px;
  }

  .newspopup-up-form.newspopup-theme .ajax-loader {
    top: 48px;
  }
}

@media screen and (max-width: 550px) {
  .newspopup-up-form.newspopup-theme {
    margin: 0px auto 5% auto;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .prnp-title {
    margin: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-logo p {
    font-size: 18px;
    line-height: 20px;
  }
}
/*==================== end Theme 15 ====================*/
CSS;
        $rows[16]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
<div class="title-wrap">
  <div class="prnp-title"></div>
</div>
<div class="newspopup-descr">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}

  <div class="button-col">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
  <div class="pl-clearfix"></div>
  {{mailchimp_fields}}
</form>

<script type="text/javascript">
  require(['jquery', 'domReady!'], function($) {
    var setPopupWindowParam = function()
    {
      var height = $('.newspopup_up_bg .hld-pop').height();
      var top = window.innerHeight / 2 - height / 2;
      var right = $('.newspopup_up_bg .title-wrap').width() - $('.newspopup_up_bg').width();
      $('.newspopup_up_bg').css('top', (top > 0? top : 0) +'px');
      $('.newspopup_up_bg').css('margin-top', '0');
      $('.newspopup_up_bg').css('right', right +'px');
      $('.newspopup_up_bg').removeClass('newspopup_up_bg_open');
    }

    var interval = setInterval(function() {
      if ($('.newspopup_up_bg .hld-pop').height() > 0) {
        setPopupWindowParam();
        clearInterval(interval);
      }
    }, 500);

    $('.newspopup_up_bg .newspopup-up-form.newspopup-theme .title-wrap').click(function() {
      if ($('.newspopup_up_bg').hasClass('newspopup_up_bg_open')) {
        $('.newspopup_up_bg').removeClass('newspopup_up_bg_open');
        var right = $('.newspopup_up_bg .title-wrap').width() - $('.newspopup_up_bg').width();
        $('.newspopup_up_bg').animate({
          right: right +'px'
        }, 100);
      } else {
        $('.newspopup_up_bg').addClass('newspopup_up_bg_open');
        $('.newspopup_up_bg').animate({
          right: 0
        }, 100);
      }
    });

    $(window).resize(setPopupWindowParam);
  });
</script>
HTML;
        $rows[16]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto:400,500);
@font-face {
  font-family: BebasNeue;
  src: url("{{view url="Plumrocket_Newsletterpopup::css/font/BebasNeue.otf"}}");
}

.newspopup_up_bg .newspopup-messages-holder,
.newspopup_up_bg div.mage-error {
  display: none;
}

.newspopup_up_bg div.mage-error {
  margin: 0;
}

.newspopup_up_bg.newspopup_up_bg_open .newspopup-messages-holder { display: table; }
.newspopup_up_bg.newspopup_up_bg_open div.mage-error { display: block; }

.newspopup_up_bg .newspopup-messages-holder {
  min-height: 0;
  height: auto;
  margin-bottom: 0;
}

.newspopup_up_bg .newspopup-messages-holder .error,
.newspopup_up_bg .newspopup-messages-holder .success  {
  min-height: 41px;
  height: 41px;
}

.newspopup_up_bg {
  min-height: 250px;
  display: block;
  right: -1000px;
  position: fixed;
  z-index: 1100;
  padding: 0;
  color: #666666;
  text-align: left;
  width: 100%;
  max-width: 660px;
  -webkit-transition: 0.5s ease-in;
  transition: 0.5s ease-in;
  top: 50%;
  margin-top: -110px;
}

.newspopup-up-form .cross {
  color: #2e2a29;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -15px;
  top: -15px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

.newspopup-blur {
  -webkit-filter: none;
  -moz-filter: none;
  filter: none;
}

.newspopup_ov_hidden {
  overflow-y: auto;
}

/*==================== Theme 16 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: static;
  overflow: visible;
  display: block;
  width: 100%;
  margin: 0 auto;
  background-color: #dbd5d1;
  background: #d9d3ce; /* Old browsers */
  background: -moz-linear-gradient(left,  #d9d3ce 0%, #f5f3f2 100%); /* FF3.6+ */
  background: -webkit-gradient(linear, left top, right top, color-stop(0%,#d9d3ce), color-stop(100%,#f5f3f2)); /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(left,  #d9d3ce 0%,#f5f3f2 100%); /* Chrome10+,Safari5.1+ */
  background: -o-linear-gradient(left,  #d9d3ce 0%,#f5f3f2 100%); /* Opera 11.10+ */
  background: -ms-linear-gradient(left,  #d9d3ce 0%,#f5f3f2 100%); /* IE10+ */
  background: linear-gradient(to right,  #d9d3ce 0%,#f5f3f2 100%); /* W3C */
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#d9d3ce', endColorstr='#f5f3f2',GradientType=1 ); /* IE6-9 */
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  min-height: 240px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #705e3d;
  text-align: left;
  padding: 0;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme16_bg.png"}}");
  background-size: 274px 227px;
  background-position: right bottom;
  background-repeat: no-repeat;
  min-height: 227px;
  overflow: visible;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #2e2a29;
  top: 10px;
  right: 10px;
  position: absolute;
  font-weight: 700;
  font-size: 11px;
  width: 20px;
  height: 20px;
  line-height: 20px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #5fa18f;
}

.newspopup-up-form.newspopup-theme .title-wrap {
  position: absolute;
  width: 45px;
  left: 0;
  top: 0;
  height: 100%;
  background-color: #705e3d;
}

.newspopup-up-form.newspopup-theme .title-wrap:hover {
  cursor: pointer;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  display: block;
  padding: 0;
  min-width: 45px;
  line-height: 0;
  white-space: nowrap;
  top: 0;
  position: relative;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme16_logo.png"}}");
  background-position: left center;
  background-repeat: no-repeat;
  font-family: 'BebasNeue', Arial, Helvetica, sans-serif;
  font-size: 13px;
  color: #fff;
  font-size: 32px;
  display: inline-block;
  line-height: 45px;
  height: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  color: #99a2b0;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 15px;
  padding: 30px 50px 0 80px;
  line-height: 18px;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
}

.newspopup-up-form.newspopup-theme .newspopup-descr .line-1 {
  font-family: 'BebasNeue', Arial, Helvetica, sans-serif;
  font-size: 69px;
  color: #705e3d;
  line-height: 60px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr .line-2 {
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
  color: #5fa18f;
  font-size: 30px;
  line-height: 30px;
  text-transform: uppercase;
  margin-top: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr .line-3 {
  color: #705e3d;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
  font-size: 16px;
  text-transform: uppercase;
  font-weight: 500;
  margin-top: 15px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  padding: 0 0 20px 80px;
  margin-top: 10px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 30px;
  line-height: 30px;
  font-size: 12px;
  color: #000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #9d938e;
  text-align: left;
  font-family: 'Open Sans', Arial, sans-serif;
  -webkit-border-radius: 0px;
  -moz-border-radius: 0px;
  border-radius: 0px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .required-entry.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #707070;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding: 0;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 12px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 30px;
  line-height: 30px;
  font-size: 12px;
  color: #000;
  border: 1px solid #9d938e;
  outline: none;
  width: 100%;
  font-family: 'Open Sans', Arial, sans-serif;
  padding-top: 0;
  padding-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  max-width: 200px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {color: #504b4b;}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {color: #504b4b;}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {color: #504b4b;opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {color: #504b4b;}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
  float: left;
  width: 110px;
  margin: 0 auto;
  text-align: center;
  position: relative;
  height: 50px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  float: left;
  width: 200px;
  margin-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-full:before {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li.customer-dob div.mage-error {
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .send {
  color: #fff;
  font-size: 13px;
  height: 30px;
  line-height: 30px;
  width: 100%;
  text-align: center;
  display: inline-block;
  border-radius: 0px;
  -moz-border-radius: 0px;
  -webkit-border-radius: 0px;
  background-color: #705e3d;
  outline: none;
  font-family: 'Open Sans', Arial, sans-serif;
  -webkit-transition: all 0.3s;
  -moz-transition: all 0.3s;
  transition: all 0.3s;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:active,
.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #5FA18F;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 35px;
  margin-right: -52px;
}

.newspopup-up-form.newspopup-theme .privacy-policy {
  text-align: center;
  height: 20px;
  line-height: 20px;
  margin: 0;
  padding: 0;

}

.newspopup-up-form.newspopup-theme .privacy-policy a {
  text-decoration: underline;
  font-family: 'Roboto', Arial, Helvetica, sans-serif;
  font-size: 12px;
  color: #555f71;
  display: inline;
}

.newspopup-up-form.newspopup-theme .privacy-policy a:hover {
  text-decoration: none;
}

@media screen and (max-width: 800px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF0000!important;
    font-weight: 400;
    box-shadow: none;
    padding: 0!important;
    text-align: left;
    width: 100%;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}


@media screen and (max-width: 600px) {
  .newspopup-up-form.newspopup-theme .hld-pop {
    background-size: 200px;
    padding: 20px 0;
  }
  .newspopup-up-form.newspopup-theme .title-wrap {
    width: 35px;
  }
  .newspopup-up-form.newspopup-theme .prnp-title {
    width: 35px;
    min-width: 35px;
    background-size: 35px auto;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    padding: 0px 20px 0 65px;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr .line-1 {
    font-size: 50px;
    line-height: 50px;
    padding-top: 12px;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr .line-2 {
    font-size: 25px;
    line-height: 25px;
    margin-top: 0;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0 20px 0 65px;
    margin-top: 10px;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 65%;
  }
}
@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 0;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    padding: 0px 15px 5px 50px;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 55%;
    margin-right: 5%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
    width: 40%;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr .line-3 {
    font-size: 10px;
  }
}
/*==================== end Theme 16 ====================*/
CSS;
        $rows[17]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="prnp-title">{{text_title}}</div>
<div class="newspopup-descr">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div class="input-block">
    {{form_fields}}

    <div class="button-col">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme17_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </div>
  <div class="interest-block">
    <p>please, select all areas that interest you:</p>
    {{mailchimp_fields}}
  </div>
  <div class="button-col newspopup-bottom-col">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme17_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>
</form>
HTML;
        $rows[17]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto);
@font-face {
  font-family: Monitorica;
  src: url("{{view url="Plumrocket_Newsletterpopup::css/font/Monitorica-Rg.otf"}}");
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -57px;
  z-index: 10;
  width: 104px;
  height: auto;
}

/*==================== Theme 17 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: static;
  display: block;
  width: 595px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 14px;
  color: #949cac;
  text-align: left;
  padding: 0 30px 30px 30px;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme17_bg.png"}}");
  background-size: 261px 275px;
  background-color: #1c2029;
  border: 5px solid #cfcfcf;
  background-position: right bottom;
  background-repeat: no-repeat;
  min-height: 290px;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #5e6167;
  top: 8px;
  right: 8px;
  position: absolute;
  font-weight: 700;
  font-size: 11px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #CECECE;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 35px;
  margin-right: -57px;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 60px;
  font-weight: 400;
  color: #ffffff;
  margin: 25px 0 8px 0;
  font-family: 'Monitorica', Arial, sans-serif;
  text-transform: none;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  line-height: 20px;
  color: #ed8c22;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 18px;
  max-width: 415px;
  margin-bottom: 20px;
}

.newspopup-up-form.newspopup-theme .input-block:before,
.newspopup-up-form.newspopup-theme .input-block:after,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 32px;
  line-height: 32px;
  font-size: 12px;
  color: #888888;
  padding: 0 0 0 10px;
  outline: none;
  border: 1px solid #aaa4a4;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  background-color: #fff;
  opacity: 1;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  border: 1px solid #EC8B21;
  -webkit-box-shadow: 0 0 10px 0 #EC8B21;
  -moz-box-shadow: 0 0 10px 0 #EC8B21;
  box-shadow: 0 0 10px 0 #EC8B21;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  margin-top: 0;
}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 12px;
  vertical-align: middle;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 32px;
  font-size: 12px;
  color: #888888;
  border: 1px solid #dce1ec;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 200px;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col.newspopup-bottom-col {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
  position: relative;
  float: left;
  width: 200px;
  margin-left: 10px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #504b4b;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  float: left;
  width: 200px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 10px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li div.mage-error {
  margin-top: 0;
  margin-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #ef8a22;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #E6A01F),color-stop(1, #F87225));
  background-image: -o-linear-gradient(bottom, #E6A01F 0%, #F87225 100%);
  background-image: -moz-linear-gradient(bottom, #E6A01F 0%, #F87225 100%);
  background-image: -webkit-linear-gradient(bottom, #E6A01F 0%, #F87225 100%);
  background-image: -ms-linear-gradient(bottom, #E6A01F 0%, #F87225 100%);
  background-image: linear-gradient(to bottom, #E6A01F 0%, #F87225 100%);
  color: #fff;
  font-size: 14px;
  height: 32px;
  width: 100%;
  text-align: center;
  line-height: 32px;
  display: inline-block;
  text-transform: none;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  text-transform: none;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  text-shadow: 0 -1px #b77419;
  -webkit-box-shadow: inset 0 2px 0 0 #e8b542;
  -moz-box-shadow: inset 0 2px 0 0 #e8b542;
  box-shadow: inset 0 2px 0 0 #e8b542;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #E48421;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #DA981D),color-stop(1, #D26222));
  background-image: -o-linear-gradient(bottom, #DA981D 0%, #D26222 100%);
  background-image: -moz-linear-gradient(bottom, #DA981D 0%, #D26222 100%);
  background-image: -webkit-linear-gradient(bottom, #DA981D 0%, #D26222 100%);
  background-image: -ms-linear-gradient(bottom, #DA981D 0%, #D26222 100%);
  background-image: linear-gradient(to bottom, #DA981D 0%, #D26222 100%);
}

.newspopup-up-form.newspopup-theme .interest-block {
  margin-top: 15px;
}

.newspopup-up-form.newspopup-theme .interest-block h3 {
  display: none;
}

.newspopup-up-form.newspopup-theme .interest-block p {
  margin-bottom: 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .mailchimp_item {
  float: left;
}

.newspopup-up-form.newspopup-theme .interest-block label {
  display: block;
  font-family: Arial, Helvetica, sans-serif;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 0;
  margin: -1px 20px 0 6px;
}

.newspopup-up-form.newspopup-theme .interest-block p {
  padding: 0;
  margin: 0 0 8px 0;
  text-shadow: 1px 1px 1px #1B1F28;
}

@media screen and (max-width: 864px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF5656 !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
  }

  .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px 30px 30px 30px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    margin: 0 0 10px 0;
    font-size: 42px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 50%;
    float: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    float: none;
    margin-left: 0;
    width: 50%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col.newspopup-bottom-col {
    display: block;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
    float: none;
    margin-left: 0;
    width: 50%;
    display: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 50%;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    font-size: 16px;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px;
    background-size: 140px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 38px;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    line-height: 19px;
    font-size: 16px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .button-col {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .interest-block input {
    clear: left;
    margin-bottom: 10px;
  }
}
/*==================== end Theme 17 ====================*/
CSS;
        $rows[18]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>

<div class="prnp-title">{{text_title}}</div>
<div class="newspopup-descr">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div class="input-block">
    {{form_fields}}
    <div>
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme18_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </div>
  <div class="interest-block">
    <p>please, select all areas that interest you:</p>
    {{mailchimp_fields}}
  </div>
</form>
HTML;
        $rows[18]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans);
@import url(https://fonts.googleapis.com/css?family=Roboto);
@import url(https://fonts.googleapis.com/css?family=Bree+Serif);

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 5000;
  height: 100%;
  padding: 0 5%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 0;
  margin-right: -57px;
  z-index: 10;
  width: 25px;
  height: auto;
}

/*==================== Theme 18 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: static;
  display: block;
  width: 445px;
  margin: 10% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 14px;
  color: #949cac;
  text-align: left;
  padding: 0 40px 30px 40px;
  background-color: #ffffff;
  background-repeat: no-repeat;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #868686;
  top: 8px;
  right: 8px;
  position: absolute;
  font-weight: 700;
  font-size: 11px;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #353B3E;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  top: 5px;
  margin-right: -38px;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 30px;
  line-height: 32px;
  font-weight: 400;
  color: #000000;
  margin: 0;
  padding: 40px 140px 40px 0;
  font-family: 'Bree Serif', Arial, sans-serif;
  text-transform: none;
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme18_bg.png"}}");
  background-size: 193px 133px;
  background-position: right bottom;
  background-repeat: no-repeat;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  line-height: 20px;
  color: #000;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 18px;
  max-width: 415px;
  margin: 0;
}

.newspopup-up-form.newspopup-theme .input-block:before,
.newspopup-up-form.newspopup-theme .input-block:after,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 35px;
  line-height: 35px;
  font-size: 14px;
  color: #888888;
  padding: 0 0 0 7px;
  outline: none;
  background: none;
  border: 1px solid #F3F3F3;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  margin: 0 0 0 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 14px;
  color: #888888;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input.mage-error {
  border: 1px solid red!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.mage-error {
  margin-top: 0;
  margin-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select:focus {
  border: 1px solid #D5D5D5;
  -webkit-box-shadow: 0 0 10px 0 #DBD9D9;
  -moz-box-shadow: 0 0 10px 0 #DBD9D9;
  box-shadow: 0 0 10px 0 #DBD9D9;
  background-color: #fff;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-block select {
  background-color: #f3f3f3;
}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-block select:focus {
  background-color: #fff;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 35px;
  font-size: 14px;
  color: #888888;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  border: 1px solid #F3F3F3;
}

.newspopup-up-form.newspopup-theme input[placeholder] { color: #504b4b; }
.newspopup-up-form.newspopup-theme input::-moz-placeholder { color: #646464; opacity: 1; }
.newspopup-up-form.newspopup-theme input:-moz-placeholder { color: #646464; opacity: 1; }
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder { color: #646464; }
.newspopup-up-form.newspopup-theme:-ms-input-placeholder { color: #646464;opacity: 1;}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder { color: #646464; }

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-block {
  background-color: #f4f4f4;
  border: 1px solid #dcdada;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  padding: 7px 7px 0 7px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  float: left;
  width: 250px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob {
  position: relative;
}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:after {
  content: "";
  display: block;
  clear: both;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob .mage-error {
  margin-bottom: 5px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
  margin-bottom: 4px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  float: right;
  width: 90px;
  position: relative;
}

.newspopup-up-form.newspopup-theme .send {
  background-color: #d23c3c;
  color: #fff;
  font-size: 14px;
  height: 35px;
  width: 100%;
  text-align: center;
  line-height: 35px;
  display: inline-block;
  text-transform: none;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  text-transform: none;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  text-shadow: 0 1px #a42f2f;
  -webkit-box-shadow: inset 0 2px 0 0 #df7272;
  -moz-box-shadow: inset 0 2px 0 0 #df7272;
  box-shadow: inset 0 2px 0 0 #df7272;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
  text-decoration: none;
  outline: none;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #ED4949;
}

.newspopup-up-form.newspopup-theme .interest-block {
  margin-top: 17px;
  text-align: center;
}

.newspopup-up-form.newspopup-theme .interest-block h3 {
  display: none;
}

.newspopup-up-form.newspopup-theme .interest-block p {
  margin-bottom: 5px;
}

.newspopup-up-form.newspopup-theme .interest-block .mailchimp_item {
  display: inline-block;
}

.newspopup-up-form.newspopup-theme .interest-block label {
  display: block;
  font-family: Arial, Helvetica, sans-serif;
  color: #595959;
  font-size: 11px;
  font-weight: 700;
  padding: 0;
  margin: -1px 15px 0 6px;
}

.newspopup-up-form.newspopup-theme .interest-block p {
  padding: 0;
  margin: 0 0 12px 0;
  color: #b9b9b9;
  text-align: center;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 12px;
}

@media screen and (max-width: 680px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: red !important;
    font-weight: 400;
    box-shadow: none;
    padding: 0 !important;
  }

  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
}

@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    width: 100%;
    font-size: 16px;
  }

  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    padding: 0px 80px 40px 0;
    min-height: 127px;
    font-size: 25px;
    line-height: 26px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form .input-block {
    padding: 7px;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
    width: 100%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 100%;
    float: none;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    float: none;
    margin-left: 0;
  }

  .newspopup-up-form.newspopup-theme .newspopup-descr {
    line-height: 19px;
    font-size: 16px;
  }

  .newspopup-up-form.newspopup-theme .interest-block input {
    clear: left;
    margin-bottom: 10px;
  }

  .newspopup-up-form.newspopup-theme .interest-block label {
    margin-bottom: 10px;
  }
}
/*==================== end Theme 18 ====================*/
CSS;
        $rows[19]['code'] = <<<HTML
<div class="img-wrapper"></div>
<div class="prnp-title">{{text_title}}</div>
<div class="newspopup-descr">
  {{text_description}}
</div>
<form class="newspopup_up_bg_form" method="POST">
  {{form_fields}}
  <div style="position: relative">
    <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
      {{text_submit}}
    </a>
    <img src="{{view url="Plumrocket_Newsletterpopup::images/templates/theme19_loader.gif"}}" title="" style="display: none;" class="ajax-loader" />
  </div>

  <div class="pl-clearfix"></div>
  {{mailchimp_fields}}
</form>

<div class="div-or">
  <span class="div-or-text">or</span>
</div>

<a class="newspopup-solial-button" rel="nofollow" href="#" onclick="window.psLogin('{{store direct_url="pslogin/account/douse/type/facebook/call/prnewsletterpopup.index.pslogin"}}');">
  <span class="newspopup-solial-button-text">Join with Facebook</span>
  <span class="newspopup-solial-button-icon"></span>
</a>

<div class="cross close" title="{{text_cancel}}">&#10005;</div>
HTML;
        $rows[19]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);
@import url(https://fonts.googleapis.com/css?family=Source+Serif+Pro:400,600,700);

.newspopup_up_bg .newspopup-messages-holder {
  margin-bottom: 10px;
}

.newspopup_up_bg {
  display: block;
  left: 0px;
  position: fixed;
  top: 0px;
  width: 100%;
  z-index: 1100;
  height: 100%;
  background: rgba(0, 0, 0, 0.65) repeat left top;
  color: #666666;
  text-align: left;
  overflow-y: auto;
  overflow-x: hidden;
}

.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 15px;
  width: 15px;
  color: #fff;
  line-height: 15px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {
  background: #262626;
  cursor: pointer;
}

.newspopup-up-form .ajax-loader {
  position: absolute;
  top: 37px;
  right: 50%;
  margin-right: -10px;
  z-index: 10;
  width: 20px;
  height: auto;
}

/*==================== Theme 19 ====================*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme {
  position: relative;
  width: 560px;
  margin: 12% auto 5% auto;
}

.newspopup-up-form.newspopup-theme .hld-pop {
  position: relative;
  padding: 30px 40px 30px 240px;
  background-color: #453434;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  color: #000000;
  text-align: center;
  -webkit-box-shadow: 0 5px 18px 0 #23231F;
  -moz-box-shadow: 0 5px 18px 0 #23231F;
  box-shadow: 0 5px 18px 0 #23231F;
  background-position: bottom center;
  background-repeat: no-repeat;
}

.newspopup-up-form.newspopup-theme .hld-pop .img-wrapper {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme19_bg.png"}}");
  background-size: 360px 415px;
  width: 360px;
  height: 415px;
  position: absolute;
  bottom: 0;
  left: -120px;
  background-position: left bottom;
  background-repeat: no-repeat;
}

.newspopup-up-form.newspopup-theme .cross {
  background: none;
  color: #fff;
  top: 15px;
  right: 15px;
  position: absolute;
  font-weight: 400;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
  width: auto;
  height: auto;
  text-transform: uppercase;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .cross:hover {
  color: #787878;
}

.newspopup-up-form.newspopup-theme .prnp-title {
  font-size: 25px;
  line-height: 27px;
  font-weight: 400;
  text-transform: none;
  margin: 0 0 10px 0;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  text-align: left;
  color: #ffffff;
  text-align: center;
  text-shadow: 1px 1px #191313;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup-descr {
  text-transform: none;
  line-height: 18px;
  margin: 0 auto 10px auto;
  width: 182px;
  min-height: 113px;
  color: #fff;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  font-size: 24px;
  text-align: center;
  /* show background image */
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/theme19_subtitle.png"}}");
  background-size: 182px 113px;
  background-repeat: no-repeat;
  background-position: center;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p {
  text-align: center;
  text-shadow: 1px 1px #191313;
  /* hide content */
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child span.sup {
  font-size: 50px;
  line-height: 40px;
  font-family: 'Source Serif Pro', serif;
  font-weight: 700;
  float: right;
  margin-right: 10px;
  display: inline-block;
  height: auto;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child span.sub {
  font-size: 30px;
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
  font-weight: 400;
  float: right;
  height: auto;
  line-height: 30px;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child {
  color: #f5be15;
  width: 167px;
  margin: 0 auto 7px auto;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child:before,
.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child:after {
  clear: both;
  content: "";
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup-descr p:first-child span {
  font-size: 100px;
  line-height: 75px;
  font-family: 'Source Serif Pro', serif;
  font-weight: 700;
  padding: 0;
  margin: 0;
  height: 75px;
  display: inline-block;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
  width: 100%;
  padding: 7px;
  background-color: #f4f4f4;
  position: relative;
  z-index: 100;
  width: 100%;
  display: block;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form:after {
  display: block;
  clear: both;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"],
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  width: 100%;
  height: 30px;
  line-height: 30px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  padding: 0 10px;
  outline: none;
  border: 1px solid #F3F3F3;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  background: none;
  background-color: #F3F3F3;
  filter: none;
  opacity: 1;
  text-align: left;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"] {
  padding-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"]:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"]:focus {
  background-color: #DDDDDD;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="text"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="email"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="password"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="date"].required-entry.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="number"].required-entry.mage-error {
  border: 1px solid red!important;
  background-color: #FFE6E6;
}

.newspopup-up-form.newspopup-theme div.mage-error {
  margin-top: 0;
  margin-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] {
  margin: 0 0 0 10px;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form input[type="radio"] + label {
  font-size: 12px;
  color: #666161;
  margin-right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select {
  height: 30px;
  font-size: 12px;
  font-family: 'Open Sans', Arial, sans-serif;
  color: #000000;
  border: 0px;
  box-shadow: none;
  outline: none;
  width: 100%;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  padding-left: 10px;
  background-color: #f3f3f3;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
  width: 225px;
  display: block;
  margin: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form .pl-clearfix + h3 {
  text-align: left;
  font-size: 15px;
  margin: 5px 0 5px 7px;
}

.newspopup-up-form.newspopup-theme input[placeholder] {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme input::-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-moz-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input:-ms-input-placeholder {
  color: #504b4b;
}
.newspopup-up-form.newspopup-theme:-ms-input-placeholder {
  color: #504b4b;
  opacity: 1;
}
.newspopup-up-form.newspopup-theme input::-webkit-input-placeholder {
  color: #504b4b;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  width: 225px;
  margin: auto;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
  float: right;
  height: 30px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  margin-bottom: 7px;
  position: relative;
  text-align: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:last-child {
  margin-bottom: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li:after {
  clear: both;
  display: block;
  content: "";
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob {
  position: relative;
}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:before,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .customer-dob:after {
  content: "";
  display: block;
  clear: both;
}


.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day {
  width: 30%;
  float: left;
  padding-right: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year {
  width: 40%;
  float: left;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day div.mage-error,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year div.mage-error {
  display: none!important;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input {
  width: 100%;
  padding: 0 0 0 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-month input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-day input:focus,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .dob-year input:focus {
  padding-left: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li .input-box {
  padding: 0;
}

.newspopup-up-form.newspopup-theme .send {
  color: #736565;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  height: 30px;
  padding: 0;
  text-align: center;
  display: inline-block;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  text-transform: none;
  width: 30px;
  text-decoration: none;
}

.newspopup-up-form.newspopup-theme .send:before {
  display: block;
  content: "";
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 5px 0 5px 5px;
  border-color: transparent transparent transparent #73656f;
  margin: 9px auto 0 auto;
}

.newspopup-up-form.newspopup-theme .send:hover {
  text-decoration: none;
  background-color: #dcdcdc;
}

.newspopup-up-form.newspopup-theme .div-or {
  border-top: 1px solid #f4f4f4;
  text-align: center;
  margin: 25px auto -7px auto;
  width: 190px;
}

.newspopup-up-form.newspopup-theme .div-or-text {
  background-color: #453434;
  color: #f4f4f4;
  position: relative;
  display: inline-block;
  top: -13px;
  padding: 0 5px;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 13px;
}

.newspopup-up-form.newspopup-theme .newspopup-solial-button {
  height: 40px;
  line-height: 40px;
  max-width: 205px;
  position: relative;
  color: #fff;
  display: inline-block;
  background-color: #547FCE;
  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #547FCE),color-stop(1, #315AB1));
  background-image: -o-linear-gradient(bottom, #547FCE 0%, #315AB1 100%);
  background-image: -moz-linear-gradient(bottom, #547FCE 0%, #315AB1 100%);
  background-image: -webkit-linear-gradient(bottom, #547FCE 0%, #315AB1 100%);
  background-image: -ms-linear-gradient(bottom, #547FCE 0%, #315AB1 100%);
  background-image: linear-gradient(to bottom, #547FCE 0%, #315AB1 100%);
  width: 100%;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  text-shadow: 1px 0 #7a8fc7;
  border: 1px solid #3259a6;
  -webkit-box-shadow: inset 1px 1px 1px 0 #729ee1, inset -1px -1px 0 #3259a6, inset -2px -2px 0 #2a4dac;
  -moz-box-shadow: inset 1px 1px 1px 0 #729ee1, inset -1px -1px 0 #3259a6, inset -2px -2px 0 #2a4dac;
  box-shadow: inset 1px 1px 1px 0 #729ee1, inset -1px -1px 0 #3259a6, inset -2px -2px 0 #2a4dac;
  font-family: 'Open Sans', Arial, sans-serif;
  font-size: 13px;
  text-align: center;
  -webkit-transform: all 0.3s;
  -moz-transform: all 0.3s;
  transform: all 0.3s;
}

.newspopup-up-form.newspopup-theme .newspopup-solial-button .newspopup-solial-button-text {
  display: inline-block;
  text-align: center;
  margin-left: -28px;
}

.newspopup-up-form.newspopup-theme .newspopup-solial-button .newspopup-solial-button-icon {
  background-image: url("{{view url="Plumrocket_Newsletterpopup::images/templates/facebook.png"}}");
  background-size: 18px 20px;
  display: inline-block;
  height: 34px;
  border-left: 1px solid #2e54a6;
  width: 40px;
  float: right;
  background-repeat: no-repeat;
  background-position: center;
  margin-top: 2px;
  box-shadow: inset 1px 0px 0 0 #527dcc;
  position: absolute;
  right: 0;
}

.newspopup-up-form.newspopup-theme .newspopup-solial-button:hover {
  opacity: 0.8;
}

@media screen and (max-width: 600px) {
  .newspopup_up_bg {
    padding: 0 5% 5% 5%;
  }
  .newspopup-up-form.newspopup-theme {
    width: 100%;
    margin: 5% auto;
  }
  .newspopup_up_bg .error,
  .newspopup_up_bg .success {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px 20px 20px 155px;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
    width: 75%;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul + div {
    width: 20%;
  }
  .newspopup-up-form.newspopup-theme .send {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .hld-pop .img-wrapper {
    background-size: 260px;
    left: -65px;
  }
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form select[name="mailchimp_list"] {
    width: 75%;
  }
}

@media screen and (max-width: 550px) {
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error {
    position: static !important;
    background: none;
    color: #FF0000 !important;
    box-shadow: none;
    padding: 0 !important;
    width: 100%;
  }
  .newspopup_up_bg .newspopup-up-form.newspopup-theme div.mage-error:after {
    display: none;
  }
  .newspopup_up_bg .newspopup-messages-holder {
    margin-bottom: 8px;
  }
}

@media screen and (max-width: 450px) {
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form {
    width: 100%;
  }
  .newspopup-up-form.newspopup-theme .hld-pop {
    padding: 20px;
  }
  .newspopup-up-form.newspopup-theme .newspopup-descr {
    width: auto;
    margin: 0;
  }
  .newspopup-up-form.newspopup-theme .hld-pop .img-wrapper {
    display: none;
  }
}
/*==================== end Theme 19 ====================*/
CSS;
        $rows[20]['code'] = <<<HTML
<div class="cross close" title="{{text_cancel}}">&#10005;</div>
<div class="prnp-title">{{text_title}}</div>
<div class="small_text">
  {{text_description}}
</div>

<form class="newspopup_up_bg_form" method="POST">
  <div style="position:relative;">
    {{form_fields}}

    <div style="position:relative;">
      <a href="#" title="{{text_submit}}" class="send-btn2 gradient send">
        {{text_submit}}
      </a>
      <img src="{{view url="Plumrocket_Newsletterpopup::images/loader-onwhite.gif"}}" title="" style="display: none;" class="ajax-loader" />
    </div>
  </div>
</form>
HTML;
        $rows[20]['style'] = <<<CSS
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,600);

.newspopup_up_bg div.mage-error{
  background: #8D0000;
  bottom: 5px;
  color: #FFF!important;
  font-size: 11px;
  font-weight: bold;
  line-height: 13px;
  min-height: 13px;
  padding: 10px!important;
  position: absolute;
  white-space: normal;
  left: -160px;
  width: 150px;
  border-radius: 5px;
  -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  margin: 0;
}

.newspopup_up_bg div.mage-error:after {
  position: absolute;
  right: -8px;
  bottom: 8px;
  content: " ";
  width: 0;
  height: 0;
  border-top: 8px solid rgba(0, 0, 0, 0);
  border-bottom: 8px solid rgba(0, 0, 0, 0);
  border-left: 8px solid #8D0000;
}

.newspopup_up_bg .newspopup-messages-holder {
  margin-bottom: 15px;
}

.newspopup_up_bg {
  display:block;
  left: 0px;
  position:fixed;
  top: 0px;
  width: 100%;
  z-index: 1100;
  height:100%;
  background: rgba(0, 0, 0, 0.5) repeat left top;
  color:#666666;
  text-align:left;
  overflow-y: auto;
  overflow-x: hidden;
}


.newspopup-up-form .cross {
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  background: #000;
  height: 24px;
  width: 24px;
  color:#fff;
  line-height: 24px;
  font-weight: 700;
  position: absolute;
  z-index: 1200;
  right: -12px;
  top: -12px;
  font-size: 14px;
  text-align: center;
}

.newspopup-up-form .cross:hover {background: #262626; cursor: pointer;}

.newspopup-up-form .ajax-loader {
  position: absolute;
  right: -38px;
  top: 14px;
  z-index: 10;
}



/*Default Form*/
.newspopup-up-form.newspopup-theme * {
  box-sizing: border-box;
}

.newspopup-up-form.newspopup-theme  {
  display: block;
  width: 560px;
  margin: 10% auto 5% auto;
  font-family: 'Open Sans', Arial, sans-serif;
}

.newspopup-up-form.newspopup-theme .ajax-loader {
  right: 50%;
  top: 41px;
  margin-right: -57px;
  z-index: 10;
  width: 50%;
}

.newspopup-up-form.newspopup-theme .required-entry.mage-error {
  border: 1px solid #eb340a !important;
  background: #faebe7 !important;
}

.newspopup-up-form.newspopup-theme>div.hld-pop {
  position: relative;
  padding: 35px 25px 40px 25px;
  text-align:center;
  background: #f7f7f7;
  background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2Y3ZjdmNyIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjU5JSIgc3RvcC1jb2xvcj0iI2U1ZTVlNSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNkNGQ0ZDQiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
  background: -moz-linear-gradient(top, #f7f7f7 0%, #e5e5e5 59%, #d4d4d4 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f7f7f7), color-stop(59%,#e5e5e5), color-stop(100%,#d4d4d4));
  background: -webkit-linear-gradient(top, #f7f7f7 0%,#e5e5e5 59%,#d4d4d4 100%);
  background: -o-linear-gradient(top, #f7f7f7 0%,#e5e5e5 59%,#d4d4d4 100%);
  background: -ms-linear-gradient(top, #f7f7f7 0%,#e5e5e5 59%,#d4d4d4 100%);
  background: linear-gradient(to bottom, #f7f7f7 0%,#e5e5e5 59%,#d4d4d4 100%);
  -webkit-box-shadow: 0 0 20px 0 rgba(0,0,0,0.4);
  box-shadow: 0 0 20px 0 rgba(0,0,0,0.4);
}
.newspopup-up-form.newspopup-theme .prnp-title {
  width: 100%;
  font-size: 30px;
  font-family: 'Open Sans', Arial, sans-serif;
  text-transform: uppercase;
  font-weight: 600;
  color:#063454;
  text-shadow:0 2px 0 #fff;
  padding: 0;
  margin: 0 0 5px;
  line-height: 1.35;
}

.newspopup-up-form.newspopup-theme .small_text{
  color:#494949;
  font-size:14px;
}

.newspopup-up-form.newspopup-theme .small_text p{
  margin-bottom: 15px;
  min-height: 36px;
  line-height: 1.55;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div {
  width: 45%;
  margin: 0 auto;
  text-align: left;
  line-height: 25px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>ul li {
  width: 50%;
  margin: 0 auto;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div ul li label {
  display: none;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div ul li div {
  text-align: left;
}


.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div ul li .customer-dob {white-space: nowrap;}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div ul li .dob-day,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div ul li .dob-month {
  width: 25%;
  display: inline-block;
  padding-right: 2%;
}

.newspopup-up-form.newspopup-theme  .newspopup_up_bg_form>div ul li .dob-year {
  width: 50%;
  display: inline-block;
}

.newspopup-up-form.newspopup-theme input[type="password"],
.newspopup-up-form.newspopup-theme input[type="email"],
.newspopup-up-form.newspopup-theme input[type="date"],
.newspopup-up-form.newspopup-theme input[type="number"],
.newspopup-up-form.newspopup-theme input[type="text"] {
  display:inline-block;
  padding:0 10px;
  width:100%;
  height:34px;
  line-height:34px;
  margin: 5px auto;
  background:#fff;
  font-size:14px;
  color:#494949;
  border: 1px solid #9e9e9e;
  -webkit-box-shadow: inset 0px 1px 0px #A4EE9A;
  box-shadow: inset 0px 1px 0px #ececec;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
}

.newspopup-up-form.newspopup-theme input[type="number"] {padding-right: 0; padding-left: 9px;}

.newspopup-up-form.newspopup-theme input[type="radio"] {
  margin-top: 0;
}

.newspopup-up-form.newspopup-theme select {
  display:inline-block;
  height: 34px;
  border: 1px solid #9e9e9e;
  color: #494949;
  margin: 5px auto;
  font-size: 14px;
  padding: 0 0 0 5px;
  background-color: white;
  width: 100%;
}

.newspopup-up-form.newspopup-theme select option[disabled] {
  color: #9e9e9e;
}

.newspopup-up-form.newspopup-theme select:focus{
  background-color: white;
}

.newspopup-up-form.newspopup-theme .send-btn2{
  display:block;
  margin: 20px auto 0 auto;
  padding:0 10px;
  text-align: center;
  height:34px;
  line-height:32px;
  font-size:14px;
  text-decoration:none;
  color:#fff;
  border: 1px solid #d03200;
  text-shadow:0 1px 0 rgba(0, 0, 0, 0.22);
  -webkit-box-shadow: inset 0px 1px 0px #ffd382;
  box-shadow: inset 0px 1px 0px #ffd382;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  background: #fb780e;
  font-weight: 600;
  background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZkOWEyNiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmMTgyMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
  background: -moz-linear-gradient(top, #fb780e 0%, #eb5f00 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fb780e), color-stop(100%,#eb5f00));
  background: -webkit-linear-gradient(top, #fb780e 0%,#eb5f00 100%);
  background: -o-linear-gradient(top, #fb780e 0%,#eb5f00 100%);
  background: -ms-linear-gradient(top, #fb780e 0%,#eb5f00 100%);
  background: linear-gradient(to bottom, #fb780e 0%,#eb5f00 100%);

}
.newspopup-up-form.newspopup-theme .send-btn2:hover{
  background: #fca644;
  background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZjYTY0NCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlZjhlMWYiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
  background: -moz-linear-gradient(top, #fca644 0%, #ef8e1f 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fca644), color-stop(100%,#ef8e1f));
  background: -webkit-linear-gradient(top, #fca644 0%,#ef8e1f 100%);
  background: -o-linear-gradient(top, #fca644 0%,#ef8e1f 100%);
  background: -ms-linear-gradient(top, #fca644 0%,#ef8e1f 100%);
  background: linear-gradient(to bottom, #fca644 0%,#ef8e1f 100%);
}



/*end*/

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form h3 {
  font-size: 14px;
  margin-bottom: 4px;
  margin-top: 10px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.mailchimp_item {
  font-size: 14px;
  margin: 0 0 6px 0;
  line-height: 16px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.mailchimp_item input {
  float: left;
  margin-top: 2px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.mailchimp_item label {
  display: block;
  margin-left: 20px;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul {
  margin: 0;
  padding: 0;
}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form ul li {
  list-style: none;
  margin: 0;
  position: relative;
}

/*Enterprise*/
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.validation-error {background-position: 100% 15px;}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.validation-passed {background-position: 100% 15px;}
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form div.validation-passed  {}

.newspopup-up-form.newspopup-theme .newspopup_up_bg_form #advice-validate-custom-nl_day,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form #advice-validate-custom-nl_year,
.newspopup-up-form.newspopup-theme .newspopup_up_bg_form #advice-validate-custom-nl_month { display: none; }


input[placeholder]      {color:#9e9e9e;}
input::-moz-placeholder   {color:#9e9e9e; opacity: 1;}
input:-moz-placeholder    {color:#9e9e9e; opacity: 1;}
input:-ms-input-placeholder {color:#9e9e9e;}
:-ms-input-placeholder{color:#9e9e9e; opacity: 1;}
input::-webkit-input-placeholder {color:#9e9e9e;}

@media screen and (max-width: 640px) {
  .newspopup-up-form.newspopup-theme * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  .newspopup-up-form.newspopup-theme {
    width: 100%;
    padding: 0 10%;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  .newspopup-up-form.newspopup-theme * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div {
    width: 65%;
  }

  .newspopup-up-form.newspopup-theme .prnp-title {
    font-size: 25px;
  }

  .newspopup-up-form.newspopup-theme .small_text p {
    font-size: 14px;
  }

  .newspopup_up_bg div.mage-error {
    position: static!important;;
    background: none;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;
    color: red!important;
    padding: 0!important;
    font-weight: 400;
    margin: 0;
  }

  .newspopup_up_bg div.mage-error:after {
    display: none;
  }
}


@media screen and (max-width: 480px) {
  .newspopup-up-form.newspopup-theme {
    padding: 0 5%;
  }

  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form>div,
  .newspopup-up-form.newspopup-theme .newspopup_up_bg_form>ul li {
    width: 90%;
  }
}
CSS;


        if ($templateId) {
            return isset($rows[$templateId]) ? $rows[$templateId] : null;
        }

        return $rows;
    }
}
