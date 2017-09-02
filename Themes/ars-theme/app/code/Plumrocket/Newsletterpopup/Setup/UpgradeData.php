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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\TemplateFactory
     */
    private $templateFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\TemplateFactory
     */
    private $helperTemplateFactory;

    /**
     * UpgradeData constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Model\TemplateFactory  $templateFactory
     * @param \Plumrocket\Newsletterpopup\Helper\TemplateFactory $helperTemplateFactory
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\TemplateFactory $templateFactory,
        \Plumrocket\Newsletterpopup\Helper\TemplateFactory $helperTemplateFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->helperTemplateFactory = $helperTemplateFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '3.0.3', '<')) {
            // Update all templates (cleared from h-tags)
            $rows = $this->helperTemplateFactory->create()->getAllData();

            foreach ($rows as $row) {
                $this->templateFactory
                    ->create()
                    ->setData($row)
                    ->setCanSaveBaseTemplates(true)
                    ->save();
            }
        }

        $setup->endSetup();
    }
}
