<?php
namespace Esto\HirePurchase\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();

        if(
            version_compare($context->getVersion(), '1.0.7', '<')
            || !$installer->getConnection()->isTableExists('esto_country_banks')
        ) {
            $entityTableName = $installer->getTable('esto_country_banks');
            $table = $installer->getConnection()
                ->newTable($entityTableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true]
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    'country_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    'bank_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    'bank_logo',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                );
            $installer->getConnection()->createTable($table);
        }



        $installer->endSetup();
    }
}