<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Setup;

use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Blog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Table postfix for AW Blog 1.0.0
     *
     * @var string
     */
    const OLD_TABLE_SUFFIX = '_old';

    /**
     * List of tables for AW Blog 1.0.0
     *
     * @var array
     */
    private $oldTables = [
        'aw_blog_cat',
        'aw_blog_cat_store',
        'aw_blog_post',
        'aw_blog_post_cat',
        'aw_blog_post_store',
        'aw_blog_post_tag',
        'aw_blog_tag'
    ];

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->renameOldTables($setup);
            $this->addNewTables($setup);
            $this->migrateData($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->updatePostStatus($setup);
        }
    }

    /**
     * Rename old tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function renameOldTables(SchemaSetupInterface $installer)
    {
        $tablePairs = [];
        foreach ($this->oldTables as $tableName) {
            $newTableName = $installer->getTable($tableName . self::OLD_TABLE_SUFFIX);
            if (!$installer->getConnection()->isTableExists($newTableName)) {
                $tablePairs[] = ['oldName' => $installer->getTable($tableName), 'newName' => $newTableName];
            }
        }
        if (count($tablePairs)) {
            $installer->getConnection()->renameTablesBatch($tablePairs);
        }

        return $this;
    }

    /**
     * Add new tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addNewTables(SchemaSetupInterface $installer)
    {
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
                $installer->getFkName('aw_blog_post_store_new', 'store_id', 'store', 'store_id'),
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
                $installer->getFkName('aw_blog_post_tag_new', 'tag_id', 'aw_blog_tag', 'id'),
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

        return $this;
    }

    /**
     * Migrate data from old table to new table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function migrateData(SchemaSetupInterface $installer)
    {
        // Migrate category data
        $oldNewCategoryId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable('aw_blog_cat' . self::OLD_TABLE_SUFFIX));
        $categoryData = $connection->fetchAssoc($select);
        foreach ($categoryData as $category) {
            $toInsertCategory = [
                'name'             => $category['name'],
                'url_key'          => $category['url_key'],
                'status'           => $category['status'],
                'sort_order'       => $category['sort_order'],
                'meta_title'       => $category['meta_title'],
                'meta_description' => $category['meta_description']
            ];
            $connection->insert(
                $installer->getTable('aw_blog_category'),
                $toInsertCategory
            );
            $newCategoryId = $connection->lastInsertId();
            $oldNewCategoryId[$category['cat_id']] = $newCategoryId;

            $select = $connection->select()
                ->from($installer->getTable('aw_blog_cat_store' . self::OLD_TABLE_SUFFIX))
                ->where('cat_id = :id');
            $categoryStoreData = $connection->fetchAll($select, ['id' => $category['cat_id']]);
            $toInsertCategoryStore = [];
            foreach ($categoryStoreData as $categoryStore) {
                $toInsertCategoryStore[] = [
                    'category_id' => $newCategoryId,
                    'store_id'    => $categoryStore['store_id']
                ];
            }
            if (count($toInsertCategoryStore)) {
                $connection->insertMultiple(
                    $installer->getTable('aw_blog_category_store'),
                    $toInsertCategoryStore
                );
            }
        }

        // Migrate post data
        $oldNewPostId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable('aw_blog_post' . self::OLD_TABLE_SUFFIX));
        $postData = $connection->fetchAssoc($select);
        foreach ($postData as $post) {
            $toInsertPost = [
                'title'             => $post['title'],
                'url_key'           => $post['url_key'],
                'short_content'     => $post['short_content'],
                'content'           => $post['content'],
                'author_name'       => $post['author_name'],
                'status'            => $post['status'],
                'created_at'        => $post['created_at'],
                'updated_at'        => $post['updated_at'],
                'publish_date'      => $post['publish_date'],
                'is_allow_comments' => $post['is_allow_comments'],
                'meta_title'        => $post['meta_title'],
                'meta_description'  => $post['meta_description']
            ];
            $connection->insert(
                $installer->getTable('aw_blog_post'),
                $toInsertPost
            );
            $newPostId = $connection->lastInsertId();
            $oldNewPostId[$post['post_id']] = $newPostId;

            // Migrate Post-Store data
            $select = $connection->select()
                ->from($installer->getTable('aw_blog_post_store' . self::OLD_TABLE_SUFFIX))
                ->where('post_id = :id');
            $postStoreData = $connection->fetchAll($select, ['id' => $post['post_id']]);
            $toInsertPostStore = [];
            foreach ($postStoreData as $postStore) {
                $toInsertPostStore[] = [
                    'post_id'  => $newPostId,
                    'store_id' => $postStore['store_id']
                ];
            }
            if (count($toInsertPostStore)) {
                $connection->insertMultiple(
                    $installer->getTable('aw_blog_post_store'),
                    $toInsertPostStore
                );
            }
        }

        // Migrate tag data
        $oldNewTagId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable('aw_blog_tag' . self::OLD_TABLE_SUFFIX));
        $tagData = $connection->fetchAssoc($select);
        foreach ($tagData as $tag) {
            $toInsertTag = [
                'name' => $tag['name']
            ];
            $connection->insert(
                $installer->getTable('aw_blog_tag'),
                $toInsertTag
            );
            $newTagId =$connection->lastInsertId();
            $oldNewTagId[$tag['id']] = $newTagId;
        }

        // Migrate Post-Tag data
        foreach ($oldNewTagId as $oldTagId => $newTagId) {
            $select = $connection->select()
                ->from($installer->getTable('aw_blog_post_tag' . self::OLD_TABLE_SUFFIX))
                ->where('tag_id = :id');
            $postTagData = $connection->fetchAll($select, ['id' => $oldTagId]);
            $toInsertPostTag = [];
            foreach ($postTagData as $postTag) {
                $toInsertPostTag[] = [
                    'post_id' => $oldNewPostId[$postTag['post_id']],
                    'tag_id' => $newTagId
                ];
            }
            if (count($toInsertPostTag)) {
                $connection->insertMultiple(
                    $installer->getTable('aw_blog_post_tag'),
                    $toInsertPostTag
                );
            }
        }

        // Migrate Post-Category data
        foreach ($oldNewCategoryId as $oldCategoryId => $newCategoryId) {
            $select = $connection->select()
                ->from($installer->getTable('aw_blog_post_cat' . self::OLD_TABLE_SUFFIX))
                ->where('cat_id = :id');
            $postCategoryData = $connection->fetchAll($select, ['id' => $oldCategoryId]);
            $toInsertPostCategory = [];
            foreach ($postCategoryData as $postCategory) {
                $toInsertPostCategory[] = [
                    'post_id' => $oldNewPostId[$postCategory['post_id']],
                    'category_id' => $newCategoryId
                ];
            }
            if (count($toInsertPostCategory)) {
                $connection->insertMultiple(
                    $installer->getTable('aw_blog_post_category'),
                    $toInsertPostCategory
                );
            }
        }
        return $this;
    }

    /**
     * Update post status
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function updatePostStatus(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $select = $connection->select()
            ->from($installer->getTable('aw_blog_post'), ['id'])
            ->where('publish_date > ?', $now);
        $postIds = $connection->fetchCol($select);

        if (count($postIds)) {
            $connection->update(
                $installer->getTable('aw_blog_post'),
                ['status' => Status::SCHEDULED],
                'id IN(' . implode(',', array_values($postIds)) . ')'
            );
        }
    }
}
