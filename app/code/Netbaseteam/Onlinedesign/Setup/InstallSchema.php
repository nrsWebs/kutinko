<?php


namespace Netbaseteam\Onlinedesign\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
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
		 * Creating table nb_onlinedesign
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_onlinedesign')
		)->addColumn(
			'onlinedesign_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'dpi',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'DPI'
		)->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Product ID'
        )->addColumn(
            'design_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Design Image'
        )->addColumn(
            'status_upload_design',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Status Upload Design'
        )->addColumn(
            'use_visual_layout',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Use Visual Layout'
        )->addColumn(
			'content_design',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true],
			'Content Design'
		)->addColumn(
			'nbdesigner_option',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'nbdesigner_option'
		)->addColumn(
			'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Status'
		)->setComment(
			'Onlinedesign item'
		);
		$installer->getConnection()->createTable($table);
		
		
		/**
		 * Creating table nb_catart
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_catart')
		)->addColumn(
			'cat_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Category Art'
		)->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                255,
                ['nullable' => true, 'unsigned' => true],
                'Status Category Art'
            )->setComment(
			'Category Art Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_art
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_art')
		)->addColumn(
			'art_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'filename',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'filename'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'title'
		)->addColumn(
			'category',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'category'
		)->addColumn(
			'price',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'price'
		)->addColumn(
			'description',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'1M',
			['nullable' => true],
			'filename'
		)->setComment(
			'Art Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_catfont
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_catfont')
		)->addColumn(
			'cat_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Category Font'
		)->setComment(
			'Category Font Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_font
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_font')
		)->addColumn(
			'font_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Category Font'
		)->addColumn(
			'category',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Category Font'
		)->addColumn(
			'alias',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Alias'
		)->setComment(
			'Font Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_color
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_color')
		)->addColumn(
			'color_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'color_name',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'color_name'
		)->addColumn(
			'hex',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'hex'
		)->setComment(
			'Color Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_onreject
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_onreject')
		)->addColumn(
			'id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'oid',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'oid'
		)->addColumn(
			'pid',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'pid'
		)->addColumn(
			'action',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'action'
		)->setComment(
			'Onreject Item'
		);
		$installer->getConnection()->createTable($table);
		
		/**
		 * Creating table nb_templates
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('nb_templates')
		)->addColumn(
			'id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'product_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'product_id'
		)->addColumn(
			'folder',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'folder'
		)->addColumn(
			'user_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'user_id'
		)->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false],
			'Created At'
		)->addColumn(
			'publish',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'publish'
		)->addColumn(
			'private',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'private'
		)->addColumn(
			'priority',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'priority'
		)->addColumn(
			'hit',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'hit'
		)->setComment(
			'Onreject Item'
		);
		$installer->getConnection()->createTable($table);
		
		$installer->endSetup();
	}
}