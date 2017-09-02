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

namespace Plumrocket\Newsletterpopup\Setup;

// use Magento\Eav\Model\Entity\Attribute\Set;
// use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\Newsletterpopup\Helper\Data;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\AdminhtmlFactory
     */
    protected $adminhtmlHelperFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\TemplateFactory
     */
    protected $templateHelperFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    protected $popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\FormFieldFactory
     */
    protected $formFieldFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    // protected $_eavSetupFactory;
    // protected $_eavSetup;
    // protected $_attributeSet;

    /**
     * InstallData constructor.
     *
     * @param \Magento\Config\Model\ConfigFactory                 $configFactory
     * @param \Plumrocket\Newsletterpopup\Helper\AdminhtmlFactory $adminhtmlHelperFactory
     * @param \Plumrocket\Newsletterpopup\Helper\TemplateFactory  $templateHelperFactory
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory      $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\FormFieldFactory  $formFieldFactory
     * @param \Plumrocket\Newsletterpopup\Model\TemplateFactory   $templateFactory
     * @param DateTime                                            $dateTime
     * @param State                                               $state
     */
    public function __construct(
        \Magento\Config\Model\ConfigFactory                 $configFactory,
        \Plumrocket\Newsletterpopup\Helper\AdminhtmlFactory $adminhtmlHelperFactory,
        \Plumrocket\Newsletterpopup\Helper\TemplateFactory  $templateHelperFactory,
        \Plumrocket\Newsletterpopup\Model\PopupFactory      $popupFactory,
        \Plumrocket\Newsletterpopup\Model\FormFieldFactory  $formFieldFactory,
        \Plumrocket\Newsletterpopup\Model\TemplateFactory   $templateFactory,
        DateTime $dateTime,
        State $state
        // EavSetupFactory $eavSetupFactory,
        // Set $attributeSet
    ) {
        $this->adminhtmlHelperFactory   = $adminhtmlHelperFactory;
        $this->templateHelperFactory    = $templateHelperFactory;
        $this->popupFactory             = $popupFactory;
        $this->formFieldFactory         = $formFieldFactory;
        $this->templateFactory          = $templateFactory;
        $this->_dateTime                = $dateTime;
        // $this->_eavSetupFactory = $eavSetupFactory;
        // $this->_attributeSet = $attributeSet;

        $state->setAreaCode('adminhtml');
        // after serAreaCode
        $this->config = $configFactory->create();
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Add config data.
        $this->config->setDataByPath(Data::SECTION_ID . '/mailchimp/fields_tag', json_encode([]));
        $this->config->save();

        /* 1.0.0 */
        // INTO plumrocket_newsletterpopup_popups
        $data = [
            'template_id'           => 20, /* 2.0.0 */
            'name'                  => 'Newsletter Popup $10 - Default Template',
            'status'                => 1,
            'coupon_code'           => 0,
            'start_date'            => null,
            'end_date'              => null,
            'success_page'          => '__stay__',
            'custom_success_page'   => '',
            'send_email'            => 1,
            'template_name'         => 'prnewsletterpopup-default',
            'display_popup'         => 'after_time_delay',
            'delay_time'            => 5,
            'cookie_time_frame'     => 30,
            'store_id'              => '0',
            'show_on'               => 'all',
            'devices'               => 'all',
            'customers_groups'      => '0',
            'text_title'            => 'GET $10 OFF YOUR FIRST ORDER',
            'text_description'      => '<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>',
            'text_note'             => '<p>Enter your email</p>',
            'text_success'          => '<p>Thank you for your subscription.</p>',
            'text_submit'           => 'Sign Up Now',
            'text_cancel'           => 'Hide',
            'code_length'           => 12,
            'code_format'           => 'alphanum',
            'code_prefix'           => '',
            'code_suffix'           => '',
            'code_dash'             => 0,
            'conditions_serialized' => $this->adminhtmlHelperFactory->create()->getDefaultRule(true),
        ];

        $demoPopup = $this->popupFactory->create()->setData($data)->save();

        /* 1.2.0 */
        // INTO plumrocket_newsletterpopup_form_fields
        $rows = [
            ['email', 'Email', 1, 10, 0],
            ['confirm_email', 'Confirm Email', 0, 20, 0],
            ['firstname', 'First Name', 0, 30, 0],
            ['middlename', 'Middle Name', 0, 40, 0],
            ['lastname', 'Last Name', 0, 50, 0],
            ['suffix', 'Suffix', 0, 60, 0],
            ['dob', 'Date of Birth', 0, 70, 0],
            ['gender', 'Gender', 0, 80, 0],
            ['taxvat', 'Tax/VAT Number', 0, 90, 0],
            ['password', 'Password', 0, 100, 0],
            ['confirm_password', 'Confirm Password', 0, 110, 0],

            /* 2.0.0 */
            ['prefix', 'Prefix', 0, 120, 0],
            ['telephone', 'Telephone', 0, 130, 0],
            ['fax', 'Fax', 0, 140, 0],
            ['company', 'Company', 0, 150, 0],
            ['street', 'Street Address', 0, 160, 0],
            ['city', 'City', 0, 170, 0],
            ['country_id', 'Country', 0, 180, 0],
            ['region', 'State/Province', 0, 190, 0],
            ['postcode', 'Zip/Postal Code', 0, 200, 0],
        ];

        foreach ($rows as $row) {
            $row = array_combine([
                'name',
                'label',
                'enable',
                'sort_order',
                'popup_id',
            ], $row);

            if ($row['name'] == 'email') {
                $emailField = $row;
            }

            // $this->_formFieldFactory->create()->setData($row)->save();
            $this->formFieldFactory->create()->setData($row)->save();
        }

        if (!empty($emailField) && $demoPopup->getId()) {
            // Add email field for first demo popup.
            $emailField['popup_id'] = $demoPopup->getId();
            $this->formFieldFactory->create()->setData($emailField)->save();
        }

        /* 2.0.0 */
        // INTO plumrocket_newsletterpopup_templates
        $rows = $this->templateHelperFactory->create()->getAllData();

        foreach ($rows as $row) {
            $this->templateFactory
                ->create()
                ->setData($row)
                ->setIsObjectNew(true)
                ->save();
        }
    }
}
