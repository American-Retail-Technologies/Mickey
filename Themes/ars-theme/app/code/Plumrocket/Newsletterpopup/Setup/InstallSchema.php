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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'plumrocket_newsletterpopup_popups'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_newsletterpopup_popups'))
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Entity Id')
            /* 2.0.0 */->addColumn('template_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                'default'     => 0,
                ], 'Template Id')
            ->addColumn('name', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Name')
            ->addColumn('status', Table::TYPE_BOOLEAN, null, [
                'nullable'  => false,
                'default'     => 0,
                ], 'Status')
            ->addColumn('coupon_code', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                'default'     => 0,
                ], 'Coupon Code')
            ->addColumn('start_date', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
                ], 'Start Date')
            ->addColumn('end_date', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
                ], 'End Date')
            ->addColumn('success_page', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                'default'    => '__stay__',
                ], 'Success Page')
            ->addColumn('custom_success_page', Table::TYPE_TEXT, 128, [
                'nullable'  => false,
                ], 'Custom Success Page')
            ->addColumn('send_email', Table::TYPE_BOOLEAN, null, [
                'nullable'  => false,
                'default'     => true, /* in 1.1.1, prev value was false */
                ], 'Send Email')
            /* 1.2.0 */->addColumn('email_template', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                'default'     => 'prnewsletterpopup_general_email_template',
                ], 'Email Template')
            /* 1.2.0 */->addColumn('signup_method', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'     => 'signup_only',
                ], 'Signup Method')
            /* 1.2.0 */->addColumn('subscription_mode', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'     => 'all',
                ], 'Subscription Mode')
            /* DROP in 2.0.0 ->addColumn('template_name', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Template Name')*/
            ->addColumn('display_popup', Table::TYPE_TEXT, 30, [
                'nullable'  => false,
                'default'     => 'after_time_delay',
                ], 'Display Popup')
            ->addColumn('delay_time', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                'default'     => 5,
                ], 'Delay Time')
            /* 2.0.0 */->addColumn('page_scroll', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                'default'     => 30,
                ], 'Page Scroll')
            /* 2.0.3.2 */->addColumn('css_selector', Table::TYPE_TEXT, 100, [
                'nullable'  => false,
                ], 'CSS Selector')
            ->addColumn('cookie_time_frame', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                'default'     => 30, /* in 1.1.1, prev value was 7 */
                ], 'Cookie Time Frame')
            /* 1.2.0 */->addColumn('animation', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                'default'    => 'fadeInDownBig',
                ], 'Animation')
            ->addColumn('store_id', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                'default'     => 0,
                ], 'Store Id')
            /* 2.0.0 */->addColumn('conditions_serialized', Table::TYPE_TEXT, 5000, [
                'nullable'  => false,
                ], 'Conditions Serialized')
            /* DROP in 2.0.0 ->addColumn('show_on', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'     => 'all',
                ], 'Show On')*/
            /* DROP in 2.0.0 ->addColumn('devices', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'     => 'all',
                ], 'Devices')*/
            /* DROP in 2.0.0 ->addColumn('customers_groups', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                'default'     => 0,
                ], 'Customers Groups')*/
            ->addColumn('text_title', Table::TYPE_TEXT, 250 /* in 2.0.0, prev value was 64 */, [
                'nullable'  => false,
                ], 'Text Title')
            ->addColumn('text_description', Table::TYPE_TEXT, null, [
                // 'nullable'  => false,
                ], 'Text Description')
            /* DROP in 1.2.0 ->addColumn('text_note', Table::TYPE_TEXT, null, [
                // 'nullable'  => false,
                ], 'Text Note')*/
            ->addColumn('text_success', Table::TYPE_TEXT, null, [
                // 'nullable'  => false,
                ], 'Text Success')
            ->addColumn('text_submit', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Text Submit')
            ->addColumn('text_cancel', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Text Cancel')
            ->addColumn('code_length', Table::TYPE_INTEGER, 8, [
                'nullable'  => false,
                'default'     => 12,
                ], 'Code Length')
            ->addColumn('code_format', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'     => 'alphanum',
                ], 'Code Format')
            ->addColumn('code_prefix', Table::TYPE_TEXT, 16, [
                'nullable'  => false,
                ], 'Code Prefix')
            ->addColumn('code_suffix', Table::TYPE_TEXT, 16, [
                'nullable'  => false,
                ], 'Code Suffix')
            ->addColumn('code_dash', Table::TYPE_INTEGER, 8, [
                'nullable'  => false,
                'default'     => 0,
                ], 'Code Dash')
            /* 1.1.0 */->addColumn('views_count', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Views Count')
            /* 1.1.0 */->addColumn('subscribers_count', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Subscribers Count')
            /* 1.1.0 */->addColumn('orders_count', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Orders Count')
            /* 1.1.0 */->addColumn('total_revenue', Table::TYPE_DECIMAL, [12,4], [
                'nullable'  => false,
                ], 'Total Revenue')
            ->setComment('Newsletterpopup Popups');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_history'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_newsletterpopup_history'))
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Entity Id')
            ->addColumn('popup_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                ], 'Popup id')
            ->addColumn('customer_id', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Customer Id')
            ->addColumn('customer_group', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Customer Group')
            ->addColumn('customer_ip', Table::TYPE_TEXT, 16, [
                'nullable'  => false,
                ], 'Customer Ip')
               ->addColumn('customer_email', Table::TYPE_TEXT, 128, [
                'nullable'  => false,
                ], 'Customer Email')
               ->addColumn('coupon_code', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Coupon Code')
               ->addColumn('landing_page', Table::TYPE_TEXT, 255, [
                'nullable'  => false,
                ], 'Landing Page')
            ->addColumn('store_id', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Store Id')
            ->addColumn('action', Table::TYPE_TEXT, 20, [
                'nullable'  => false,
                'default'    => 'cancel',
                ], 'Action')
            /* 2.0.3.2 */->addColumn('action_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                'default'     => 0,
                ], 'Action Id')
            ->addColumn('date_created', Table::TYPE_DATETIME, null, [
                'nullable'  => false,
                ], 'Date Created')
            /* 1.1.0 */->addColumn('increment_id', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Increment Id')
            /* 1.1.0 */->addColumn('order_id', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Order Id')
            /* 1.1.0 */->addColumn('grand_total', Table::TYPE_DECIMAL, [12,4], [
                'nullable'  => false,
                ], 'Grand Total')
            ->addIndex(
                $installer->getIdxName('plumrocket_newsletterpopup_history', ['popup_id']),
                ['popup_id']
            )
            ->setComment('Newsletterpopup History');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_mailchimp_list'
         */
        $table = $installer->getConnection()
            /* 1.2.0 */->newTable($installer->getTable('plumrocket_newsletterpopup_mailchimp_list'))
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Entity Id')
            ->addColumn('popup_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                ], 'Popup id')
            ->addColumn('name', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                ], 'Name')
            ->addColumn('label', Table::TYPE_TEXT, 255, [
                'nullable'  => false,
                ], 'Label')
               ->addColumn('enable', Table::TYPE_BOOLEAN, null, [
                'nullable'  => false,
                'default'    => 0,
                ], 'Enable')
               ->addColumn('sort_order', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Sort Order')
            ->addIndex(
                $installer->getIdxName('plumrocket_newsletterpopup_mailchimp_list', ['popup_id']),
                ['popup_id']
            )
            ->setComment('Newsletterpopup Mailchimp List');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_form_fields'
         */
        $table = $installer->getConnection()
            /* 1.2.0 */->newTable($installer->getTable('plumrocket_newsletterpopup_form_fields'))
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Entity Id')
            ->addColumn('popup_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                ], 'Popup id')
            ->addColumn('name', Table::TYPE_TEXT, 32, [
                'nullable'  => false,
                ], 'Name')
            ->addColumn('label', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Label')
               ->addColumn('enable', Table::TYPE_BOOLEAN, null, [
                'nullable'  => false,
                'default'    => 0,
                ], 'Enable')
               ->addColumn('sort_order', Table::TYPE_INTEGER, null, [
                'nullable'  => false,
                ], 'Sort Order')
            ->addIndex(
                $installer->getIdxName('plumrocket_newsletterpopup_form_fields', ['popup_id']),
                ['popup_id']
            )
            ->setComment('Newsletterpopup Form Fields');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_hold'
         */
        $table = $installer->getConnection()
            /* 1.2.0 */->newTable($installer->getTable('plumrocket_newsletterpopup_hold'))
            ->addColumn('email', Table::TYPE_TEXT, 255, [
                'nullable'  => false,
                'primary'   => true,
                ], 'Email')
            ->addColumn('popup_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => true,
                'nullable'  => false,
                ], 'Popup id')
            ->addColumn('lists', Table::TYPE_TEXT, 1024, [
                'nullable'  => false,
                ], 'Lists')
            ->setComment('Newsletterpopup Hold');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_templates'
         */
        $table = $installer->getConnection()
            /* 2.0.0 */->newTable($installer->getTable('plumrocket_newsletterpopup_templates'))
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Entity Id')
            ->addColumn('base_template_id', Table::TYPE_INTEGER, null, [
                'unsigned'  => false,
                'nullable'  => false,
                ], 'Base Template Id')
            ->addColumn('name', Table::TYPE_TEXT, 64, [
                'nullable'  => false,
                ], 'Name')
            ->addColumn('code', Table::TYPE_TEXT, null, [
                'nullable'  => false,
                ], 'Code')
            ->addColumn('style', Table::TYPE_TEXT, null, [
                'nullable'  => false,
                ], 'Style')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
                ], 'Created At')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable'  => true,
                ], 'Updated At')
            ->addColumn('default_values', Table::TYPE_TEXT, null, [
                'nullable'  => false,
                ], 'Default Values')
            ->setComment('Newsletterpopup Templates');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_newsletterpopup_history_action'
         */
        $table = $installer->getConnection()
            /* 2.0.3.2 */->newTable($installer->getTable('plumrocket_newsletterpopup_history_action'))
            ->addColumn('id', Table::TYPE_INTEGER, null, [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ], 'Id')
            ->addColumn('text', Table::TYPE_TEXT, 200, [
                'nullable'  => false,
                ], 'Text')
            ->addIndex(
                $installer->getIdxName('plumrocket_newsletterpopup_history_action', ['text']),
                ['text'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Newsletterpopup History Action');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
