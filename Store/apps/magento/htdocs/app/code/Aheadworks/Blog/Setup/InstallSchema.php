<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Setup;

use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Aheadworks\Blog\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'aw_blog_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_post'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Post Title'
            )->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'short_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Post Short Content'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Post Content'
            )->addColumn(
                'author_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Author Name'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => PostStatus::DRAFT],
                'Status'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'publish_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Publish Date'
            )->addColumn(
                'is_allow_comments',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Allowed Comments'
            )->addColumn(
                'meta_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )->addColumn(
                'product_condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Condition'
            )->addIndex(
                $installer->getIdxName('aw_blog_post', ['status', 'publish_date']),
                ['status', 'publish_date']
            )->addIndex(
                $installer->getIdxName('aw_blog_post', ['url_key']),
                ['url_key']
            )->setComment('Blog Post');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_category'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Category Name'
            )->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Sort Order'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'meta_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )->addIndex(
                $installer->getIdxName('aw_blog_category', ['status']),
                ['status']
            )->addIndex(
                $installer->getIdxName('aw_blog_category', ['url_key']),
                ['url_key']
            )->setComment('Blog Category');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_tag'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_tag'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('aw_blog_tag', ['name']),
                ['name']
            )->setComment('Blog Tag');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_category_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_category_store'))
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_category_store', ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_category_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('aw_blog_category_store', 'category_id', 'aw_blog_category', 'id'),
                'category_id',
                $installer->getTable('aw_blog_category'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_category_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Category To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_post_store'))
            ->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_post_store', ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_post_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_store', 'post_id', 'aw_blog_post', 'id'),
                'post_id',
                $installer->getTable('aw_blog_post'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_post_category'))
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_post_category', ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_post_category', ['post_id']),
                ['post_id']
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_category', 'category_id', 'aw_blog_category', 'id'),
                'category_id',
                $installer->getTable('aw_blog_category'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_category', 'post_id', 'aw_blog_post', 'id'),
                'post_id',
                $installer->getTable('aw_blog_post'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Category Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_tag'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_post_tag'))
            ->addColumn(
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_post_tag', ['tag_id']),
                ['tag_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_post_tag', ['post_id']),
                ['post_id']
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_tag', 'tag_id', 'aw_blog_tag', 'id'),
                'tag_id',
                $installer->getTable('aw_blog_tag'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_tag', 'post_id', 'aw_blog_post', 'id'),
                'post_id',
                $installer->getTable('aw_blog_post'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Tag Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_product_post'))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post', ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('aw_blog_product_post', 'post_id', 'aw_blog_post', 'id'),
                'post_id',
                $installer->getTable('aw_blog_post'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_product_post', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Product Post');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_product_post_idx'))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_idx', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_idx', ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_idx', ['store_id']),
                ['store_id']
            )->setComment('Blog Product Post Indexer Idx');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_blog_product_post_tmp'))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_tmp', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_tmp', ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName('aw_blog_product_post_tmp', ['store_id']),
                ['store_id']
            )->setComment('Blog Product Post Indexer Tmp');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
