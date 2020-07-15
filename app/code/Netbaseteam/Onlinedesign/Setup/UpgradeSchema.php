<?php

namespace Netbaseteam\Onlinedesign\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
	public function upgrade(SchemaSetupInterface $setup,
		ModuleContextInterface $context) {
		$setup->startSetup();
		if (version_compare($context->getVersion(), '4.5.0') < 0) {

			// Get module table
			$tableName = $setup->getTable('sales_order_item');

			// Check if the table already exists
			if ($setup->getConnection()->isTableExists($tableName) == true) {
				// Declare data
				$columns = [
					'nbdesigner_json' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_json',
					],
					'nbdesigner_pid' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_pid',
					],
					'nbdesigner_session' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_session',
					],
					'nbdesigner_src' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_src',
					],
					'nbdesigner_sku' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_sku',
					],
				];

				$connection = $setup->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn($tableName, $name, $definition);
				}
			}

			// Get module table
			$tableName = $setup->getTable('quote_item');

			// Check if the table already exists
			if ($setup->getConnection()->isTableExists($tableName) == true) {
				// Declare data
				$columns = [
					'nbdesigner_json' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_json',
					],
					'nbdesigner_pid' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_pid',
					],
					'nbdesigner_session' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_session',
					],
					'nbdesigner_src' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_src',
					],
					'nbdesigner_sku' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => false,
						'comment' => 'nbdesigner_sku',
					],
				];

				$connection2 = $setup->getConnection();
				foreach ($columns as $name => $definition) {
					$connection2->addColumn($tableName, $name, $definition);
				}
			}
		}
        if (version_compare($context->getVersion(), '4.5.0') < 0) {
            /**
             * Create table 'nb_template_mapping'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('nb_template_mapping'))
                ->addColumn(
                    'template_mapping_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Template Mapping Id'
                )
                ->addColumn(
                    'field_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Field Name'
                )
                ->addColumn(
                    'connect_field',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store Id'
                )
                ->setComment(
                    'Template Mapping Item'
                );

            $setup->getConnection()->createTable($table);
        }


		if (version_compare($context->getVersion(), '4.5.0') < 0) {
			/**
			 * Create table 'nb_onlinedesign_store'
			 */
			$table = $setup->getConnection()
				->newTable($setup->getTable('nb_onlinedesign_store'))
				->addColumn(
					'onlinedesign_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false, 'primary' => true],
					'Entity Id'
				)
				->addColumn(
					'product_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					null,
					['unsigned' => true, 'nullable' => false],
					'Product Id'
				)
				->addColumn(
					'store_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					null,
					['unsigned' => true, 'nullable' => false],
					'Store Id'
				)
				->addColumn(
					'design_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					['unsigned' => true, 'nullable' => false],
					'Design Image'
				)
				->setComment('Blocks To Stores Relations');

			$setup->getConnection()->createTable($table);
		}


		if (version_compare($context->getVersion(), '4.5.0', '<')) {
			$nbCatArtTable = $setup->getTable('nb_catart');
			if ($setup->getConnection()->isTableExists($nbCatArtTable) == true) {
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$sql = "ALTER TABLE " . $nbCatArtTable . " AUTO_INCREMENT = 12";
				$setup->getConnection()->query($sql);
			}
		}

		if (version_compare($context->getVersion(), '4.5.0', '<')) {
			/**
             * Create table 'nbdesigner_mydesigns'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('nbdesigner_mydesigns')
	            )->addColumn(
	                'id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	                'Id'
	            )->addColumn(
	                'user_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'User Id'
	            )->addColumn(
	                'folder',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                ['unsigned' => true, 'nullable' => true],
	                'Folder'
	            )->addColumn(
	                'product_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Product Id'
	            )->addColumn(
	                'variation_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Variation Id'
	            )->addColumn(
	                'price',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                ['unsigned' => true, 'nullable' => true],
	                'Price'
	            )->addColumn(
                	'selling',
	                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
	                null,
	                ['nullable' => false, 'default' => '0'],
	                'Selling'
	            )
	            ->addColumn(
	                'vote',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Vote'
	            )
	            ->addColumn(
	                'publish',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Publish'
	            )
	            ->addColumn(
                'created_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
	                null,
	                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
	                'Created Date'
	            )
	            ->addColumn(
	                'hit',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Hit'
	            )
	            ->addColumn(
	                'sales',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Sales'
	            );
            $setup->getConnection()->createTable($table);
		}

        if (version_compare($context->getVersion(), '4.5.0') < 0) {
            // Get module table
            $tableName = $setup->getTable('quote_item');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('quote_item'),
                    'session_file',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Session File Design Upload',
                    ]
                );
            }
            $tableNameSales = $setup->getTable('sales_order_item');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableNameSales) == true) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_item'),
                    'session_file',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Session File Design Upload',
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '4.5.0', '<')) {
			/**
             * Create table 'nbdesigner_templates'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('nbdesigner_templates')
	            )->addColumn(
	                'id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	                'Id'
	            )->addColumn(
	                'user_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'User Id'
	            )->addColumn(
	                'folder',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                ['unsigned' => true, 'nullable' => true],
	                'Folder'
	            )->addColumn(
	                'product_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Product Id'
	            )->addColumn(
	                'variation_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Variation Id'
	            )->addColumn(
	                'vote',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Vote'
	            )->addColumn(
	                'publish',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Publish'
	            )->addColumn(
	                'private',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Private'
	            )->addColumn(
	                'priority',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Priority'
	            )
	            ->addColumn(
                'created_date',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
	                null,
	                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
	                'Created Date'
	            )
	            ->addColumn(
	                'hit',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Hit'
	            )
	            ->addColumn(
	                'sales',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Sales'
	            )->addColumn(
	                'name',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                ['unsigned' => true, 'nullable' => true],
	                'Name'
	            )->addColumn(
                    'image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true, 'nullable' => true],
                    'Images'
                )->addColumn(
                    'colors',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true, 'nullable' => true],
                    'Colors'
                )->addColumn(
	                'tags',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                ['unsigned' => true, 'nullable' => true],
	                'Tags'
	            )->addColumn(
	                'thumbnail',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['unsigned' => true, 'nullable' => true],
	                'Thumbnail'
	            );
            $setup->getConnection()->createTable($table);
		}

		$setup->endSetup();
	}
}